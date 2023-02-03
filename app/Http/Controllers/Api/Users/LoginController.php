<?php

namespace App\Http\Controllers\Api\Users;

use App\Models\Users\Onboard;
use App\Models\Users\OnboardProfileBasicInfo;
use App\Models\Users\AuditLogin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class LoginController extends Controller
{


	public function onBoardingLogin(Request $request)
    {

        //set validation
        $validator = Validator::make($request->all(), [
            'email'     => 'required',
            'password'  => 'required',
            'registrant_type'  => 'required'
        ]);

        //if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //if exist
        $user_exist = Onboard::where('email', '=', $request->email)->first();
        if ($user_exist === null) {
            return response()->json([
                'success' => false,
                'message' => 'The email you entered is not yet registered'
            ], 200);
        }

        //get credentials from request
        $credentials = $request->only('email', 'password');

        //if auth failed
        if(!$token = auth()->guard('jobseeker')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'The password you entered is incorrect. Please try again.'
            ], 200);
        }

        $user = auth()->guard('jobseeker')->user();

        if(!$user->is_verified){
            return response()->json([
                'success' => false,
                'unverified' => true,
                'message' => 'Your account is not yet verified. Please check your email.'
            ], 200);
        }

        if(!$user->basicInfo()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is uknown registrant type. Please contact the administrator.'
            ], 200);
        }

        $basicinfo = OnboardProfileBasicInfo::
                        where('reg_link_preregid', $user->id)
                        ->whereNotNull('reg_firstname')
                        ->whereNotNull('reg_lastname')
                        ->first();

        switch (true) {
            case $request->registrant_type === 'jobseeker' && $user->basicInfo()->first()->registrant_type === 1:
            case $request->registrant_type === 'remote-contractor' && $user->basicInfo()->first()->registrant_type === 2:
            case $request->registrant_type === 'client' && $user->basicInfo()->first()->registrant_type === 4:
            case $request->registrant_type === 'admin' && $user->basicInfo()->first()->registrant_type === 3:

                break;

            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized Access.',
                    'basic_info' => null,
                    'user_id' => $user->id,
                    'registrant_type_id' => $user->basicInfo()->first()->registrant_type
                ], 200);
        }

        if(!$basicinfo){
            return response()->json([
                'success' => false,
                'message' => 'Empty basic info.',
                'basic_info' => null,
                'user_id' => $user->id,
                'registrant_type_id' => $user->basicInfo()->first()->registrant_type
            ], 200);
        }else{

            $audit = AuditLogin::Create([
				'link_prereg_id'  => $user->id,
				'logged_in'  => Carbon::now(),
				'ip_addr'  => $request->ip()
			]);

            $registrant_type = $user->basicInfo()->first()->registrant_type;

            $client_data = $this->getClient($user->id);


            if(!empty($client_data)){
                return response()->json([
                    'success' => true,
                    'user'    => auth()->guard('jobseeker')->user(),
                    'basic_info' => $basicinfo,
                    'token'   => $token,
                    'client_id' => $client_data['client_id'],
                    'client_id_legacy' => $client_data['client_id_legacy'],
                    'client_name' => $client_data['client_name'],
                    'client_list' => json_encode($this->getClientList($user->id)),
                    'login_id' =>  $audit->id
                ], 200);
            }else{
                return response()->json([
                    'success' => true,
                    'user'    => auth()->guard('jobseeker')->user(),
                    'basic_info' => $basicinfo,
                    'token'   => $token,
                    'login_id' =>  $audit->id
                ], 200);
                    
            }
        }

    }

	public function logout(Request $request)
    {

        $audit = AuditLogin::updateOrCreate(['id' => $request->login_id],[
            'logged_out'  => Carbon::now()
        ]);

        Auth::logout();

        $removeToken = JWTAuth::invalidate(JWTAuth::getToken());

        if($removeToken) {
            return response()->json([
				'success' => true,
				'message' => 'Successfully logged out',
			]);
        }

    }

    function getClient($reg_id){

        $main_db = Config::get('database.connections');

        $staff = DB::table("tblm_b_onboard_actreg_basic")->join('tblm_client_sub_contractor','tblm_client_sub_contractor.actreg_contractor_id','=','tblm_b_onboard_actreg_basic.reg_id')->select('tblm_client_sub_contractor.id as sub_id')->select('*','tblm_client_sub_contractor.id as sub_id')->where('tblm_b_onboard_actreg_basic.reg_link_preregid','=',$reg_id)->first();

        if($staff){
            $client = DB::connection('mysql2')->table("tblm_client_subcon_pers")
            ->join('tblm_client','tblm_client.id','=','tblm_client_subcon_pers.link_client_id')
            ->select('tblm_client.id as client_id','tblm_client.client_id_legacy','tblm_client_subcon_pers.link_client_id','tblm_client.client_poc')
            ->where('link_subcon_id','=',$staff->sub_id)->first();
            
            if($client){
                return array('client_id' => $client->link_client_id,'client_id_legacy' => $client->client_id_legacy,'client_name' => $client->client_poc);
            }
        }
       
    }

    function getClientList($reg_id){

        $main_db = Config::get('database.connections');
        $main = $main_db['mysql']['database'];

        $staffs_collect = array();

        $staff = DB::connection('mysql2')->table("tblm_client_subcon_pers")->join($main.'.tblm_client_sub_contractor',$main.'.tblm_client_sub_contractor.id','=','tblm_client_subcon_pers.link_subcon_id')->join($main.'.tblm_b_onboard_actreg_basic','tblm_client_sub_contractor.actreg_contractor_id','=',$main.'.tblm_b_onboard_actreg_basic.reg_id')->select('tblm_client.id as client_id','tblm_client.client_id_legacy','tblm_client.client_poc as client_name')->join('tblm_client','tblm_client.id','=','tblm_client_subcon_pers.link_client_id')->join($main_db['mysql']['database'].'.tblm_a_onboard_prereg',$main.'.tblm_b_onboard_actreg_basic.reg_link_preregid','=',$main.'.tblm_a_onboard_prereg.id')
        
        ->where([
            [$main.'.tblm_b_onboard_actreg_basic.reg_link_preregid','=',$reg_id],
            [$main.'.tblm_a_onboard_prereg.password','!=',""]
            ])  
        ->groupBy('tblm_b_onboard_actreg_basic.reg_link_preregid')  
        
        ->get();

        foreach($staff as $staff_data){
            $staffs_collect[] = array('client_id' => $staff_data->client_id,'client_id_legacy'=> $staff_data->client_id_legacy,'client_name'=> $staff_data->client_name);
        }

        return $staffs_collect;
       
    }

}
