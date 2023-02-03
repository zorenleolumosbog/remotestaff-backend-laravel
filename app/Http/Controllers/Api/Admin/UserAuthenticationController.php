<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AdminUserVerify;
use App\Models\Admin\UserManagement;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class UserAuthenticationController extends Controller
{
    public function register(Request $request)
    {
        //Set validation
		$validator = Validator::make($request->all(), [
            'email' => 'required|unique:tblm_admin_user,email',
            'password' => 'required|confirmed'
        ]);

        //If validation fails
		if ($validator->fails()) {
			return [
                'errors' => $validator->errors()
            ];
		}

        //Create user management
        $user_managements = UserManagement::create([
            'email'        => $request->email,
            'password'     => bcrypt($request->password),
            'datecreated'  => Carbon::now()
        ]);

        //Generate token for email
		$token = Str::random(64);
		
		//Create user verification token
		AdminUserVerify::create([
            'link_admin_user_id' => $user_managements->id, 
            'token' => $token,
            'datecreated'  => Carbon::now()
        ]);

        //Send email verification to the admin user
        Mail::send('email.adminEmailVerificationEmail', ['token' => $token], function($message) use($request){
            $message->to($request->email);
            $message->subject('Account activation - Remote Staff');
        });

        return response()->json([
            'success' => true,
            'message' => 'Please check your email to verify.',
            'data' => $user_managements,
        ], 200);
	}

    public function login(Request $request)
    {
        //Set validation
        $validator = Validator::make($request->all(), [
            'email'     => 'required|exists:tblm_admin_user,email',
            'password'  => 'required'
        ],
        [
            'email.exists' => 'Email does not exist.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        //Get credentials from request
        $credentials = $request->only('email', 'password');

        //If auth failed
        if(!$token = auth()->guard('admin')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Password is not correct.'
            ], 200);
        }

        $user = auth()->guard('admin')->user(); 
        
        if(!$user->is_verified){
            return response()->json([
                'success' => false,
                'message' => 'Your account is not yet verified. Please check your email.'
            ], 200);    
        }else{
            return response()->json([
				'success' => true,
				'user'    => $user,
				'token'   => $token
			], 200);  
        }   
       
    }
	
	public function logout(Request $request)
    {
        try {
            auth()->guard('admin')->logout(true);
            
            // Pass true to force the token to be blacklisted "forever"
            JWTAuth::invalidate(JWTAuth::getToken(), true);
            
            return response()->json( [
                'error'   => false,
                'message' => 'Successfully logged out.'
            ] );
        } catch ( TokenExpiredException $exception ) {
            return response()->json( [
                'error'   => true,
                'message' => 'Token is expired.'

            ], 401 );
        } catch ( TokenInvalidException $exception ) {
            return response()->json( [
                'error'   => true,
                'message' => 'Invalid token.'
            ], 401 );

        } catch ( JWTException $exception ) {
            return response()->json( [
                'error'   => true,
                'message' => 'Token is missing.'
            ], 500 );
        }	
    }

    //Verify admin user account by email
	public function verify($token)
    {
        $verifyUser = AdminUserVerify::where('token', $token)->first();
          
        if($verifyUser){
            $verifyUser->adminUser->is_verified = 1;
            $verifyUser->adminUser->dateverified = Carbon::now();
            $verifyUser->adminUser->save();

            // Delete admin user verified token
            $verifyUser->where('link_admin_user_id', $verifyUser->adminUser->id)
                    ->where('token', $token)
                    ->delete();
            
            return redirect()->away(Config::get('app.url').'/verify-account');
        }

        return redirect()->away(Config::get('app.url').'/verify-account?error=alreadyverify');
	}

    public function resetPass(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'     => 'required|exists:tblm_admin_user,email'
        ],
        [
            'email.exists' => 'Email does not exist.'
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $token = $this->generateToken($request->email);
        Mail::send('email.adminResetPassword', ['token' => $token,'email' => $request->email], function($message) use($request) {
            $message->to($request->email);
            $message->subject('Forgot Password');
        });

        return response()->json([
            'success' => true,
            'message' => 'Check your inbox, we have sent a link to reset email.'
        ], 200);
    }
	
    private function generateToken($email){
        $isOtherToken = DB::table('tblm_recover_password')->where('email', $email)->first();
        
        if($isOtherToken) {
            return $isOtherToken->token;
        }

        $token = Str::random(80);
        $this->storeToken($token, $email);

        return $token;
    }
	
    private function storeToken($token, $email){
        DB::table('tblm_recover_password')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()            
        ]);
    }

    public function changePass(Request $request) {
        return $this->updatePassword($request)->count() > 0 ? $this->resetPassword($request) : $this->tokenNotFoundError();
    }
  
    //Verify if token is valid
    private function updatePassword($request){
        $email = $this->getEmailByToken($request->token);
      
        return DB::table('tblm_recover_password')->where([
            'email' => $email,
            'token' => $request->token
        ]);
    }
  
    //Token not found response
    private function tokenNotFoundError() {
       return response()->json([
          'error' => 'Token is invalid.'
        ], 201);
    }
  
    //Reset password
    private function resetPassword($request) {
        //Set validation
		$validator = Validator::make($request->all(), [
            'password' => 'required'
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        //Find email
        $email = $this->getEmailByToken($request->token);
        $user_data = UserManagement::whereEmail($email)->first();
          
        //Update password
        $user_data->update([
            'password' => bcrypt($request->password)
        ]);

        //Remove verification data from db
        $this->updatePassword($request)->delete();
        
        //Reset password response
        return response()->json([
            'success'=> true,
            'message'=>'Password has been updated.'
        ], 200);
    }
      
    private function getEmailByToken($token) { 
        if(empty($token)){

            return response()->json([
              'success' => false,
              'data' => 'This reset password token already updated.'
            ], 200);
        }else{
            $att = DB::table('tblm_recover_password')
            ->where('token', '=', $token)
            ->first();
        }
  
        if(!empty($att->email)){
            return $att->email;
        }else{

            return response()->json([
                'success'=> true,
                'data'=>'Password has been updated.'
            ], 200);
        }
    }
}
