<?php

namespace App\Http\Controllers\Api\Users;

use App\Models\Users\User;
use App\Models\Users\OnboardProfileBasicInfo;
use App\Models\Users\OnboardProfileContactInfo;
use App\Models\Users\Onboard;
use App\Models\Users\UserVerify;
use App\Models\Users\ContMobile;
use App\Models\Users\ContLandline;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use DateTime;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PhpParser\Node\Expr\FuncCall;

class RegisterController extends Controller
{


	public function onBoarding(Request $request){
		//set validation
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email|unique:tblm_a_onboard_prereg',
            'password'  => 'required|min:8',
			'password_confirm' => 'required|same:password'
        ],
		[
            'email.unique' => 'Email is already registered. Please login instead',
        ]

		);

        //if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

		//create Onboarding user
        $onboard = Onboard::create([
            'email'     => $request->email,
            'password'  => bcrypt($request->password),
            'email_passwd_conf'  => bcrypt($request->password),
            'date_submitted'  => Carbon::now(),
            'ip_addr'  => $request->ip(),
            'maxdays_rule_id'  => 1,
        ]);

        switch ($request->registrant_type) {
            case 'jobseeker':
                $registrant_type_id = 1;
                break;
            case 'admin':
                $registrant_type_id = 3;
                break;
            case 'client':
                $registrant_type_id = 4;
                break;

            default:
                return redirect()->away(config('app')['url'] . '/register/' . $request->registrant_type);
        }

		//create Onboard Profile BasicInfo
        $onboard->basicInfo()->create([
            'registrant_type' => $registrant_type_id
        ]);

		//Generate token for email
		$token = Str::random(64);

		//Verify Email of registered
		UserVerify::create([
              'user_id' => $onboard->id,
              'token' => $token
            ]);

        Mail::send('email.emailVerificationEmail', ['token' => $token,'registrant_type' => $request->registrant_type], function($message) use($request, $onboard){
             $message->to($request->email);
             $message->subject('Account activation - Remote Staff - Reference #:'.$onboard->id);
        });

		//return response JSON user is created
        if($onboard) {
            return response()->json([
                'success' => true,
				'message' => 'Please check your email to verify.',
                'email'    => $onboard->email,
            ], 200);
        }
	}

	//Verify account by email
	public function verifyAccount($token,$registrant_type)
    {
        $verifyUser = UserVerify::where('token', $token)->first();

        $message = 'Sorry your email cannot be identified.';

        if(!is_null($verifyUser) ){
            $user = $verifyUser->onboard;

            if(!$user->is_verified) {

                $verifyUser->onboard->is_verified = 1;
                $verifyUser->onboard->email_verified_at = Carbon::now();
                $verifyUser->onboard->date_verified = Carbon::now();
                $verifyUser->onboard->save();

                return redirect()->away(Config::get('app.url').'/verify-account/?id='.$verifyUser->user_id.'&token='.$token.'&type='.$registrant_type);

            } else {
                return redirect()->away(Config::get('app.url').'/verify-account?error=alreadyverify&id='.$verifyUser->user_id.'&type='.$registrant_type);
            }
        }
	}

    public function validatePreRegistration($preregid) {
        $basicinfo = OnboardProfileBasicInfo::
                        select('registrant_type')
                        ->where('reg_link_preregid', $preregid)
                        ->whereNull('reg_firstname')
                        ->whereNull('reg_lastname')
                        ->first();

        if($basicinfo){
            return response()->json([
                'success' => true,
                'data' => $basicinfo
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message'    => 'Enter a valid email.'
        ], 200);
    }


    //Pre registration

	public function preRegistration(Request $request)
    {

        //set validation
        $validator = Validator::make($request->all(), [
            'first_name'     => 'required',
            'last_name'  => 'required',
            'birthdate'  => 'required',
            'town_city'  => 'required',
            'address_1'  => 'required',
            'jobseeker_id'  => 'required'
        ]

		);

        //if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }


        $onboard_basic_info = OnboardProfileBasicInfo::updateOrCreate(['reg_link_preregid' => $request->jobseeker_id],[
            'reg_firstname'  => $request->first_name,
            'reg_middlename'  => $request->middle_name,
            'reg_lastname'  => $request->last_name,
            'reg_birthdate'  => $request->birthdate,
            'reg_home_addr_line1'  => $request->address_1,
            'reg_home_addr_line2'  => $request->address_2,
            'reg_home_addr_towncity'  => $request->town_city,
            'reg_datecreated' => Carbon::now(),
            'reg_modifiedby' => $request->jobseeker_id,
            'reg_datemodified' => Carbon::now()
        ]);


        $onboard_contact_mobile = ContMobile::updateOrCreate(['link_regid' => $request->jobseeker_id],[
            'mobile_number'  => $request->mobile_number,
            'link_regid'  => $request->jobseeker_id,
            'is_primary' => 1
        ]);

        $onboard_contact_landline = ContLandline::updateOrCreate(['link_regid' => $request->jobseeker_id],[
            'landline_number'  => $request->landline_number,
            'link_regid'  => $request->jobseeker_id,
            'is_primary' => 1
        ]);

        //Via social media login
        $onboard = Onboard::where('is_verified', 1)->where('id', $request->jobseeker_id)->first();

        if (!$onboard) {
            $verifyUser = UserVerify::where('token', $request->token)->first();

            $verifyUser->onboard->is_verified = 1;
            $verifyUser->onboard->email_verified_at = Carbon::now();
            $verifyUser->onboard->date_verified = Carbon::now();
            $verifyUser->onboard->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated.',
        ], 200);

	}
}
