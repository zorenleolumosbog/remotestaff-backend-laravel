<?php

namespace App\Http\Controllers\Api\Users;

use App\Models\Users\Onboard;
use App\Models\Users\OnboardProfileBasicInfo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Mail\ResetPassword;


class PasswordResetController extends Controller
{
   
	
	public function onBoardingReset(Request $request)
    {
		
        $registrant_type = DB::table('tblm_a_onboard_prereg')->join('tblm_b_onboard_actreg_basic','tblm_a_onboard_prereg.id','=','tblm_b_onboard_actreg_basic.reg_link_preregid')->select('registrant_type')->where('email', $request->email)->first();

        if($registrant_type){
            if($registrant_type->registrant_type != $request->registrant_type) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to change password on this pillar.'
                ], 200);
            }
        }
            
        //set validation
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email'
        ]
		
		);

        //if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }


         if(!$this->validEmail($request->email,'jobseeker')) {
            return response()->json([
				'success' => false,
                'message' => 'Email does not exist.'
            ], 200);
			
        } else {

            // If email exists
            $this->sendMail($request->email);
            return response()->json([
				'success' => true,
                'message' => 'Check your inbox, we have sent a link to reset email.'
            ], 200);            
       
		}
	}
	
	public function sendMail($email){
        $token = $this->generateToken($email);
        Mail::send('email.resetPassword', ['token' => $token,'email' => $email,'name' => $this->get_name_by_email($email)], function($message) use($email){
            $message->to($email);
            $message->subject('Forgot Password');
       });
    }
	
    public function validEmail($email,$user_type) {
		if($user_type=="jobseeker"){
			return !!Onboard::where('email', $email)->first();
		}
       
    }
	
    public function generateToken($email){
        $isOtherToken = DB::table('tblm_recover_password')->where('email', $email)->first();

        if($isOtherToken) {
            return $isOtherToken->token;
        }

        $token = Str::random(80);

        $this->storeToken($token, $email);
        return $token;
    }
	
    public function storeToken($token, $email){
        DB::table('tblm_recover_password')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()            
        ]);
    }

    public function get_name_by_email($email){
        $user_info = DB::table('tblm_a_onboard_prereg')->join('tblm_b_onboard_actreg_basic','tblm_a_onboard_prereg.id','=','tblm_b_onboard_actreg_basic.reg_link_preregid')->where('email', $email)->first();
        
        if($user_info){
            return $user_info->reg_firstname;
        }else{
            return 'User';
        }
        
    }
	
	
	

}