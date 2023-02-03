<?php

namespace App\Http\Controllers\Api\Users;
use App\Models\Users\User;
use App\Models\Users\Onboard;
use Illuminate\Http\Request;
use App\Http\Requests\UpdatePasswordRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Users\PasswordResetController;

class ChangePasswordController extends Controller {
	
	public function passwordResetOnboarding(Request $request){
      return $this->updatePasswordRow($request)->count() > 0 ? $this->resetPassword($request,'Onboard') : $this->tokenNotFoundError();
  }

    // Verify if token is valid
  private function updatePasswordRow($request){
    $email = $this->getEmailByToken($request->token);
    return DB::table('tblm_recover_password')->where([
           'email' => $email,
           'token' => $request->token
     ]);
  }

  // Token not found response
  private function tokenNotFoundError() {
     return response()->json([
        'error' => 'Either your email or token is wrong...'
      ],201);
  }

    // Reset password
  private function resetPassword($request,$model) {
        // find email
      if($model=="Onboard"){
          $email = $this->getEmailByToken($request->token);
          $userData = Onboard::whereEmail($email)->first();
        }
        // update password
        $userData->update([
          'password'=>bcrypt($request->password)
        ]);

        // remove verification data from db
        $this->updatePasswordRow($request)->delete();
        // reset password response
		
        return response()->json([
          'success'=> true,
          'message'=>'Password has been updated.'
        ],200);
    }  
    
    private function getEmailByToken($token) {
     
      if(empty($token)){
          return response()->json([
            'success'=> false,
            'data'=>'This reset password token already updated.'
          ],200);
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
        ],200);
      }
      
    } 
}