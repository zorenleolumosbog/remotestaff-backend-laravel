<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin\SocialMedia;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Abraham\TwitterOAuth\TwitterOAuth;
use App\Models\Admin\Registrant;
use App\Models\Users\Onboard;

class SocialAuthController extends Controller
{
    public function redirect($registranttype, $provider)
    {
        try {
            $config = config('services')[$provider];
            $client_id = $config['client_id'];
            $client_secret = $config['client_secret'];
            $redirect = url('') . '/social-auth/' . $registranttype . '/' . $provider . '/callback';
            $provider_config = new \SocialiteProviders\Manager\Config($client_id, $client_secret, $redirect);

            switch ($provider) {
                case 'twitter':
                    $connection = new TwitterOAuth($client_id, $client_secret);
                    $token = $connection->oauth('oauth/request_token', array('oauth_callback' => $redirect));

                    session(['oauth_token' => $token['oauth_token']]);
                    session(['oauth_token_secret' => $token['oauth_token_secret']]);

                    $redirect = $connection->url('oauth/authorize', array('oauth_token' => $token['oauth_token']));

                    return redirect()->away($redirect);
                case 'instagram':
                    return \Socialite::driver($provider)->setConfig($provider_config)->scopes(['user_profile'])->redirect();

                default:
                    return \Socialite::driver($provider)->setConfig($provider_config)->redirect();
            }
        } catch (\Throwable $e) {
            return redirect()->away(config('app')['url'] . '/login/' . $registranttype);
        }
    }

    public function handleCallback(Request $request, $registranttype, $provider)
    {
        try {
            $config = config('services')[$provider];
            $client_id = $config['client_id'];
            $client_secret = $config['client_secret'];
            $redirect = '/social-auth/' . $registranttype . '/' . $provider . '/callback';
            $provider_config = new \SocialiteProviders\Manager\Config($client_id, $client_secret, $redirect);

            switch ($provider) {
                case 'twitter':
                    $config = config('services')['twitter'];
                    $connection = new TwitterOAuth($config['client_id'], $config['client_secret'], $request->session()->get('oauth_token'), $request->session()->get('oauth_token_secret'));
                    $access_token = $connection->oauth("oauth/access_token", ["oauth_verifier" => $_REQUEST['oauth_verifier']]);

                    $connection = new TwitterOAuth($config['client_id'], $config['client_secret'], $access_token['oauth_token'], $access_token['oauth_token_secret']);
                    $user = $connection->get('account/verify_credentials', ['include_email' => 'true']);
                    break;

                case 'instagram':
                    $curl = curl_init();
                    curl_setopt_array($curl, [
                        CURLOPT_URL => "https://api.instagram.com/oauth/access_token",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => [
                            'client_id' => config('services')['instagram']['client_id'],
                            'client_secret' => config('services')['instagram']['client_secret'],
                            'grant_type' => 'authorization_code',
                            'redirect_uri' => config('services')['instagram']['redirect'],
                            'code' => $request->code
                        ],
                    ]);
                    $response = curl_exec($curl);
                    curl_close($curl);

                    $user_id = json_decode($response, true)['user_id'];
                    $access_token = json_decode($response, true)['access_token'];

                    $curl = curl_init();
                    curl_setopt_array($curl, [
                        CURLOPT_URL => "https://graph.instagram.com/v15.0/$user_id?fields=id,username&access_token=$access_token",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                    ]);

                    $response = curl_exec($curl);
                    curl_close($curl);

                    $username = json_decode($response, true)['username'];
                    break;

                default:
                    $user = \Socialite::driver($provider)->setConfig($provider_config)->user();
                    break;
            }
        } catch (\Throwable $e) {
            return redirect()->away(config('app')['url'] . '/login/' . $registranttype);
        }

        if(!$user->email) {
            return redirect()->away(config('app')['url'] . '/login/' . $registranttype . '?invalid_email=true&provider=' . $provider);
        }

        $social_media = SocialMedia::where('description', 'LIKE', "%{$provider}%")->first();

        switch ($registranttype) {
            case 'jobseeker':
            case 'client':
            case 'admin':
                $registrant = Onboard::updateOrCreate([
                    'email' => $provider == 'instagram' ? $username : $user->email,
                ],
                [
                    'link_social_media_id' => $social_media ? $social_media->id : null,
                    'email_verified_at'  => Carbon::now(),
                    'ip_addr'  => $request->ip(),
                    'date_submitted'  => Carbon::now(),
                    'is_verified'  => true,
                    'is_social_media' => true,
                    'date_verified'  => Carbon::now(),
                    'maxdays_rule_id' => null,
                    'maxdays_unverifed' => null,
                    'is_expired' => null,
                    'date_expired' => null
                ]);

                switch ($registranttype) {
                    case 'jobseeker':
                        $registrant_type_id = 1;
                        break;
                    case 'client':
                        $registrant_type_id = 4;
                        break;
                    case 'admin':
                        $registrant_type_id = 3;
                        break;
                    default:
                        return redirect()->away(config('app')['url'] . '/login/' . $registranttype);

                        break;
                }

                if(!Registrant::where('id', $registrant->id)->first()->basicInfo()->exists()) {
                    $registrant->basicInfo()->updateOrCreate([
                        'reg_link_preregid' => $registrant->id,
                    ],
                    [
                        'registrant_type' => $registrant_type_id
                    ]);
                }

                break;
            case 'remote-contractor':
                $registrant = Onboard::where('email', $user->email)->first();
                if(!$registrant) {
                    return redirect()->away(config('app')['url'] . '/login/remote-contractor?registrant_type_id=0&invalid_access=true');
                }

                if(Registrant::where('id', $registrant->id)->first()->basicInfo()->where('registrant_type', 2)->exists()) {
                    Onboard::where('email', $user->email)
                    ->update([
                        'link_social_media_id' => $social_media ? $social_media->id : null
                    ]);
                } else {
                    $registrant_type = Registrant::where('id', $registrant->id)->first()->basicInfo()->first();

                    return redirect()->away(config('app')['url'] . '/login/remote-contractor?registrant_type_id=' . $registrant_type->registrant_type . '&invalid_access=true');
                }

                break;
            default:
                return redirect()->away(config('app')['url']);
        }

        $registrant_type = Registrant::where('id', $registrant->id)->first()->basicInfo()->first();
        switch (true) {
            case $registranttype === 'remote-contractor':
            case $registranttype === 'jobseeker' && $registrant_type_id === $registrant_type->registrant_type:
            case $registranttype === 'admin' && $registrant_type_id === $registrant_type->registrant_type:
            case $registranttype === 'client' && $registrant_type_id === $registrant_type->registrant_type:
                break;

            default:
                return redirect()->away(config('app')['url'] . '/login/' . $registranttype . '?registrant_type_id=' . $registrant_type->registrant_type . '&invalid_access=true');
        }

        $token = auth()->guard('jobseeker')->login($registrant);

        putenv('TMPDIR=/');

        $cookieset = [
            'expires'   => time() + (86400 * 30),  // 86400 = 1 day
            'path'      => '/',
            'domain'    => 'remotestaff.com', // prod
            'secure'    => true // prod
        ];

        setcookie('userid', $registrant->id, $cookieset);
        setcookie('token', $token, $cookieset);

        $registrant = Registrant::where('id', $registrant->id)->first();

        if ($registrant->hasExpiry()->exists()) {
            $registrant->hasExpiry()->delete();
        }

        switch ($registranttype) {
            case 'jobseeker':
            case 'client':
                if($registrant->basicInfo()->whereNotNull('reg_firstname')->whereNotNull('reg_lastname')->exists()) {
                    return redirect()->away(config('app')['url'] . '/' . $registranttype . '/staff-overview');
                }

                return redirect()->away(config('app')['url'] . '/register/basic-info/' . $registranttype);

            case 'remote-contractor':
                return redirect()->away(config('app')['url'] . '/remote-contractor/staff-overview');

            case 'admin':
                if($registrant->basicInfo()->exists()) {
                    return redirect()->away(config('app')['url'] . '/admin/corporate-apps');
                }
                return redirect()->away(config('app')['url'] . '/register/basic-info/' . $registranttype);

            default:
                return redirect()->away(config('app')['url']);
        }

    }
}
