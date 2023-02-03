<?php

namespace App\Http\Controllers\Api\Users;

use App\Models\Users\Onboard;
use App\Models\Users\ContAltEmail;
use App\Models\Users\ContMobile;
use App\Models\Users\ContLandline;
use App\Models\Users\ContSocialMedia;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Mail;
use Carbon\Carbon;
use Auth;


class ContactInfoController extends Controller
{
	// Add EDIT Contact

	public function store(Request $request)
    {
		
		// $request->type = Type of Actions Edit or Add
		// $request->contact_type = email,landline,mobile,socialmedia

		
		if($request->contact_info=="alt_email"){
			if($request->type=="edit"){
				$onboard_email = Onboard::where('id', '=', $request->jobseeker_id)->first();
				$contAltEmail = ContAltEmail::where('id', '=', $request->id)->first();

				if($onboard_email->email==$request->alt_email){
					return response()->json([
						'success' => false,
						'message' => 'Primary and alternative email is the same. Please input unique email.',
					], 422);
				}

				if($contAltEmail->alt_email==$request->alt_email){
					$validator = Validator::make($request->all(), ['alt_email' => 'required|email']);
				}else{
					$validator = Validator::make($request->all(), ['alt_email' => 'required|email|unique:tblm_d_onboard_contact_alt_email'],['alt_email.unique' => 'The email has already been added.'] );
				}
			}else{
					$validator = Validator::make($request->all(), ['alt_email' => 'required|email|unique:tblm_d_onboard_contact_alt_email'],['alt_email.unique' => 'The email has already been added.'] );
			}

			$model = 'ContAltEmail';
			
		}elseif($request->contact_info=="mobile"){
			
			if($request->type=="edit"){
				$contMobile = ContMobile::where('id', '=', $request->id)->first();

				if($contMobile->mobile_number==$request->mobile_number){
					$validator = Validator::make($request->all(), ['mobile_number' => 'required']);
				}else{
					$validator = Validator::make($request->all(), ['mobile_number' => 'required|unique:tblm_d_onboard_contact_mobile'],['mobile_number.unique' => 'This record has already been added.']);
				}
			}else{
				$validator = Validator::make($request->all(), ['mobile_number' => 'required|unique:tblm_d_onboard_contact_mobile'],['mobile_number.unique' => 'This record has already been added.']);
			}

			$model = 'ContMobile';
			
		}elseif($request->contact_info=="landline"){

			if($request->type=="edit"){
				$contMobile = ContLandline::where('id', '=', $request->id)->first();

				if($contMobile->landline_number==$request->landline_number){
					$validator = Validator::make($request->all(), ['landline_number' => 'required']);
				}else{
					$validator = Validator::make($request->all(), ['landline_number' => 'required|unique:tblm_d_onboard_contact_landline'],['landline_number.unique' => 'This record has already been added.']);
				}
			}else{
				$validator = Validator::make($request->all(), ['landline_number' => 'required|unique:tblm_d_onboard_contact_landline'],['landline_number.unique' => 'This record has already been added.']);
			}

			$model = 'ContLandline';

		}elseif($request->contact_info=="socmed"){

			
			
			$data_array = array('social_media_url' => 'required|unique:tblm_d_onboard_contact_social_media');
			if($request->type=="edit"){
				$contSocialMedia = ContSocialMedia::where('id', '=', $request->id)->first();
				
				if($contSocialMedia->social_media_url!=$request->social_media_url){
					$validator = Validator::make($request->all(), ['social_media_url' => 'required|unique:tblm_d_onboard_contact_social_media'],['social_media_url.unique' => 'This record has already been added.']);
				}else{
					$validator = Validator::make($request->all(), ['social_media_url' => 'required|unique:tblm_d_onboard_contact_social_media']);
				}
			}else{
				$validator = Validator::make($request->all(), ['social_media_url' => 'required|unique:tblm_d_onboard_contact_social_media'],['social_media_url.unique' => 'This record has already been added.']);
			}
			$model = 'ContSocialMedia';
			
		}else{
			return response()->json([
				'success' => false,
				'message' => 'Please use contact info type.',
			], 200);
		}


		

			//if validation fails
			if ($validator->fails()) {
				return response()->json($validator->errors(), 422);
			}

			
			if($request->contact_info=="alt_email" && $request->type=="add"){
				$db_data = array(
					'alt_email' => $request->alt_email,
					'createdby' => $request->jobseeker_id,
					'datecreated' => Carbon::now(),
					'is_primary' => $request->is_primary,
					'link_regid' => $request->jobseeker_id
				);
			}elseif($request->contact_info=="alt_email" && $request->type=="edit"){
				$db_data = array(
					'id' => $request->id,
					'alt_email' => $request->alt_email,
					'is_primary' => $request->is_primary,
					'link_regid' => $request->jobseeker_id
				);
			}elseif($request->contact_info=="mobile" && $request->type=="add"){
				$db_data = array(
					'mobile_number' => $request->mobile_number,
					'createdby' => $request->jobseeker_id,
					'datecreated' => Carbon::now(),
					'is_primary' => $request->is_primary,
					'link_regid' => $request->jobseeker_id,
				);
			}elseif($request->contact_info=="mobile" && $request->type=="edit"){
				$db_data = array(
					'id' => $request->id,
					'mobile_number' => $request->mobile_number,
					'createdby' => $request->jobseeker_id,
					'datecreated' => Carbon::now(),
					'is_primary' => $request->is_primary,
					'link_regid' => $request->jobseeker_id
				);
			}elseif($request->contact_info=="landline" && $request->type=="add"){
				$db_data = array(
					'landline_number' => $request->landline_number,
					'createdby' => $request->jobseeker_id,
					'datecreated' => Carbon::now(),
					'is_primary' => $request->is_primary,
					'link_regid' => $request->jobseeker_id
				);
			}elseif($request->contact_info=="landline" && $request->type=="edit"){
				$db_data = array(
					'id' => $request->id,
					'landline_number' => $request->landline_number,
					'createdby' => $request->jobseeker_id,
					'datecreated' => Carbon::now(),
					'is_primary' => $request->is_primary,
					'link_regid' => $request->jobseeker_id
				);
			}elseif($request->contact_info=="socmed" && $request->type=="add"){
				$db_data = array(
					'link_social_media' => $request->socialmed_id,
					'social_media_url' => $request->social_media_url,
					'createdby' => $request->jobseeker_id,
					'datecreated' => Carbon::now(),
					'link_regid' => $request->jobseeker_id
				);
			}elseif($request->contact_info=="socmed" && $request->type=="edit"){
				$db_data = array(
					'id' => $request->id,
					'link_social_media' => $request->socialmed_id,
					'social_media_url' => $request->social_media_url,
					'createdby' => $request->jobseeker_id,
					'datecreated' => Carbon::now(),
					'link_regid' => $request->jobseeker_id
				);
			}

			$alt_email = $this->contactInfo($model,$db_data,$request);
			$requested = $request->type =="add" ? 'added':'edited';
			return response()->json([
				'success' => true,
				'message' => 'Successfully '.$requested.' contact information.',
				'data' => $alt_email
			], 200);
		
		
		
	}

	public function get(Request $request)
    {			
				
				$records_email = DB::table('tblm_a_onboard_prereg')->where('tblm_a_onboard_prereg.id', '=', $request->jobseeker_id)->first();
				$records_alt_email = DB::table('tblm_a_onboard_prereg')->join('tblm_d_onboard_contact_alt_email','tblm_d_onboard_contact_alt_email.link_regid','=','tblm_a_onboard_prereg.id')->where('tblm_a_onboard_prereg.id', '=', $request->jobseeker_id)->select('tblm_d_onboard_contact_alt_email.*')->get();
				$records_mobile = DB::table('tblm_a_onboard_prereg')->join('tblm_d_onboard_contact_mobile','tblm_d_onboard_contact_mobile.link_regid','=','tblm_a_onboard_prereg.id')->where('tblm_a_onboard_prereg.id', '=', $request->jobseeker_id)->select('tblm_d_onboard_contact_mobile.*')->get();
				$records_landline = DB::table('tblm_a_onboard_prereg')->join('tblm_d_onboard_contact_landline','tblm_d_onboard_contact_landline.link_regid','=','tblm_a_onboard_prereg.id')->where('tblm_a_onboard_prereg.id', '=', $request->jobseeker_id)->select('tblm_d_onboard_contact_landline.*')->get();
				$records_socmed = DB::table('tblm_a_onboard_prereg')->join('tblm_d_onboard_contact_social_media','tblm_d_onboard_contact_social_media.link_regid','=','tblm_a_onboard_prereg.id')->join('tblm_social_media','tblm_d_onboard_contact_social_media.link_social_media','=','tblm_social_media.id')->where('tblm_a_onboard_prereg.id', '=', $request->jobseeker_id)->select('tblm_social_media.description','tblm_d_onboard_contact_social_media.*')->get();
				
				return response()->json([
						'success' => true,
						'jobseeker_id' => $records_email->id,
						'email' => $records_email->email,
						'alt_emails' => $records_alt_email,
						'mobiles' => $records_mobile,
						'phones' => $records_landline,
						'soc_med' => $records_socmed	
					], 200);
				
	}

	public function delete(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'jobseeker_id' => 'required',
			'id' => 'required',
			]

		);

			//if validation fails
		if ($validator->fails()) {
			return response()->json($validator->errors(), 422);
		}
		
		if($request->contact_info=="alt_email"){
			$modelName = 'ContAltEmail';
		}elseif($request->contact_info=="mobile"){
			$modelName = 'ContMobile';
		}elseif($request->contact_info=="landline"){
			$modelName = 'ContLandline';
		}elseif($request->contact_info=="socmed"){
			$modelName = 'ContSocialMedia';
		}else{
			return response()->json([
				'success' => false,
				'message' => 'Please use contact info type.',
			], 200);
		}

		$model = "App\\Models\\Users\\".$modelName;
		
		
		$contact_info = $model::where('link_regid', '=', $request->jobseeker_id)->where('id', '=', $request->id)->first();
	
		
		if($contact_info===null){
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}else{
			//delete file Attachment
			$contact_info->delete();
			return response()->json([
				'success' => true,
				'message' => 'Successfully deleted.',
			], 200);
		}
	}

	public function set(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'jobseeker_id' => 'required',
			'id' => 'required',
			]

		);

			//if validation fails
		if ($validator->fails()) {
			return response()->json($validator->errors(), 422);
		}

		if($request->contact_info=="alt_email"){
			$modelName = 'ContAltEmail';
		}elseif($request->contact_info=="mobile"){
			$modelName = 'ContMobile';
		}elseif($request->contact_info=="landline"){
			$modelName = 'ContLandline';
		}else{
			return response()->json([
				'success' => false,
				'message' => 'Please use contact info type.',
			], 200);
		}


		$model = "App\\Models\\Users\\".$modelName;

		$contact_info = $model::where('link_regid', '=', $request->jobseeker_id)->where('id', '=', $request->id);

		if($contact_info===null){
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}
			
		
	}

	public function contactInfo($modelName,$db_data,$req){
		
		$model = "App\\Models\\Users\\".$modelName;

		if($req->contact_info!="socmed" && $req->type=="edit" && $db_data['is_primary']){
			$is_primary = $model::where('link_regid', '=', $req->jobseeker_id)->where('is_primary', '=', 1)->update(array('is_primary' => 0));
		}elseif($req->contact_info!="socmed" && $req->type=="add" && $db_data['is_primary'] == 1){
			$is_primary = $model::where('link_regid', '=', $req->jobseeker_id)->where('is_primary', '=', 1)->update(array('is_primary' => 0));
		}

		if($req->type=="add"){
			$onboard_contact_info = $model::create($db_data);
			return $onboard_contact_info;
		}else{
			$onboard_contact_info = $model::updateOrCreate(['id'=> $db_data['id']],$db_data);
			return $onboard_contact_info;
		}
		
		
	}

	
	public function completeContact($id)
	{

		$records_email = DB::table('tblm_a_onboard_prereg')->where('tblm_a_onboard_prereg.id', '=', $id)->first();
		$records_alt_email = DB::table('tblm_a_onboard_prereg')->join('tblm_d_onboard_contact_alt_email','tblm_d_onboard_contact_alt_email.link_regid','=','tblm_a_onboard_prereg.id')->where('tblm_a_onboard_prereg.id', '=', $id)->select('tblm_d_onboard_contact_alt_email.*')->get();
		$records_mobile = DB::table('tblm_a_onboard_prereg')->join('tblm_d_onboard_contact_mobile','tblm_d_onboard_contact_mobile.link_regid','=','tblm_a_onboard_prereg.id')->where('tblm_a_onboard_prereg.id', '=', $id)->select('tblm_d_onboard_contact_mobile.*')->get();
		$records_landline = DB::table('tblm_a_onboard_prereg')->join('tblm_d_onboard_contact_landline','tblm_d_onboard_contact_landline.link_regid','=','tblm_a_onboard_prereg.id')->where('tblm_a_onboard_prereg.id', '=', $id)->select('tblm_d_onboard_contact_landline.*')->get();
		$records_socmed = DB::table('tblm_a_onboard_prereg')->join('tblm_d_onboard_contact_social_media','tblm_d_onboard_contact_social_media.link_regid','=','tblm_a_onboard_prereg.id')->join('tblm_social_media','tblm_d_onboard_contact_social_media.link_social_media','=','tblm_social_media.id')->where('tblm_a_onboard_prereg.id', '=', $id)->select('tblm_social_media.description','tblm_d_onboard_contact_social_media.*')->get();
				
		$completed = 0;
		$empty = 0;

		$records_alt_email->count() > 0 ? $completed += 1 :  $empty += 1;
		$records_mobile->count() > 0 ? $completed += 1 :  $empty += 1;
		$records_landline->count() > 0 ? $completed += 1 :  $empty += 1;
		$records_socmed->count() > 0 ? $completed += 1 :  $empty += 1;

		$total = 4;

		$percentage_complete = ($completed/$total);
		
		return $percentage_complete;
	}

}
