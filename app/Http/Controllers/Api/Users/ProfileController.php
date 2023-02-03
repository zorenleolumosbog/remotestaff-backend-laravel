<?php

namespace App\Http\Controllers\Api\Users;


use App\Models\Users\OnboardProfileBasicInfo;
use App\Models\Users\OnboardProfileContactInfo;
use App\Models\Users\Onboard;

use App\Http\Controllers\Api\Users\WorkRefController;
use App\Http\Controllers\Api\Users\ContactInfoController;
use App\Http\Controllers\Api\Users\CharRefController;
use App\Http\Controllers\Api\Users\EducationalAttainmentController;
use App\Http\Controllers\Api\Users\EmploymentHistoryController;
use App\Http\Controllers\Api\Users\SkillsController;
use App\Http\Controllers\Api\Users\fileAttachmentController;

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
use Illuminate\Support\Facades\Hash;


class ProfileController extends Controller
{

	// Store / Update Onboarding Profile
	public function onBoardingStoreProfile(Request $request){

			$validator = Validator::make($request->all(), [
				'jobseeker_id' => 'required',
				]

			);

				//if validation fails
			if ($validator->fails()) {
				return response()->json($validator->errors(), 422);
			}

			$onboard_basic_info = "";
			$onboard_contact_info = "";

			if($request->info_type=="basic"){

				//create Onboarding Basic Info



				$onboard_basic_info = OnboardProfileBasicInfo::updateOrCreate(['reg_link_preregid' => $request->jobseeker_id],[
					'reg_link_preregid'  => $request->jobseeker_id,
					'reg_prefix'  => $request->prefix,
					'reg_nickname'  => $request->nickname,
					'reg_firstname'  => $request->first_name,
					'reg_middlename'  => $request->middle_name,
					'reg_lastname'  => $request->last_name,
					'reg_birthdate'  => $request->birthdate,
					'reg_civilstatus'  => $request->civil_status,
					'reg_religion'  => $request->religion,
					'reg_sss_id'  => $request->sss_id,
					'reg_philhealthid'  => $request->philhealth_id,
					'reg_pagibigid'  => $request->pagibig_id,
					'reg_tin'  => $request->tin,
					'reg_gender'  => $request->gender,
					'reg_nationality'  => $request->nationality,
					'reg_source'  => $request->source,
					'reg_home_addr_line1'  => $request->address_1,
					'reg_home_addr_line2'  => $request->address_2,
					'reg_home_addr_towncity'  => $request->town_city,
					'reg_prov_addr_line1'  => $request->province_address_1,
					'reg_prov_addr_line2'  => $request->province_address_2,
					'reg_prov_addr_towncity'  => $request->province_towncity,
					'reg_datemodified' => Carbon::now()
				]);
				return response()->json([
					'success' => true,
					'message' => 'Successfully update profile information.',
					'basic_info' => $onboard_basic_info,

				], 200);
				/*$onboard_contact_info = OnboardProfileContactInfo::updateOrCreate(['ci_link_regid' => $request->jobseeker_id],[
					'ci_addr_country'  => $request->country,
					'ci_region'  => $request->region,
					'ci_addr_province_state'  => $request->state,
					'ci_prov_addr_state_province'  => $request->province_state,
					'ci_prov_addr_region'  => $request->province_region,
					'ci_prov_addr_country'  => $request->province_country,
					'ci_link_regid'  => $request->jobseeker_id,
				]);*/

			}elseif($request->info_type=="contact"){

				//create Onboarding Basic Contact Info
				$onboard_contact_info = OnboardProfileContactInfo::updateOrCreate(['ci_link_regid' => $request->jobseeker_id],[
					'ci_addr_line1'  => $request->address_1,
					'ci_addr_line2'  => $request->address_2,
					'ci_addr_towncity'  => $request->town_city,
					'ci_addr_country'  => $request->country,
					'ci_addr_province_state'  => $request->state,
					'ci_prov_addr_line1'  => $request->province_address_1,
					'ci_prov_addr_line2'  => $request->province_address_2,
					'ci_prov_addr_towncity'  => $request->province_towncity,
					'ci_alt_email2'  => $request->alternative_email_1,
					'ci_alt_email3'  => $request->alternative_email_2,
					'ci_primarymobile'  => $request->primary_mobile,
					'ci_secondarymobile'  => $request->secondary_mobile,
					'ci_landline'  => $request->landline,
					'ci_region'  => $request->region,
					'ci_zipcode'  => $request->zipcode,
					'ci_prov_addr_state_province'  => $request->province_state,
					'ci_prov_addr_country'  => $request->province_country,
					'ci_prov_addr_region'  => $request->province_region,
					'ci_skypeid'  => $request->skype_id,
					'ci_fbid'  => $request->fb_id,
					'ci_linkedinid'  => $request->linkedin_id,
					'ci_referredby'  => $request->referred_by,
					'ci_referredmobile'  => $request->referred_mobile,
					'ci_referredemail'  => $request->referred_email,
					'ci_link_regid'  => $request->jobseeker_id,
				]);

				if(!empty($request->email)){
					$onboard_contact_info = Onboard::updateOrCreate(['id' => $request->jobseeker_id],[
						'email'  => $request->email,
					]);
				}

			}

			return response()->json([
						'success' => true,
						'message' => 'Successfully update profile information.',
						'basic_info' => $onboard_basic_info,
						'contact_info' => $onboard_contact_info,
					], 200);


	}

	// Add EDIT Contact

	public function onBoardingStoreUpdateContacts(Request $request)
    {

		// $request->type = Type of Actions Edit or Add
		// $request->contact_type = email,landline,mobile,socialmedia

		if($request->contact_info=="alt_email"){
			$model = 'ContAltEmail';
			$data_array = array('alt_email' => 'required|email');
		}elseif($request->contact_info=="mobile"){
			$model = 'ContMobile';
			$data_array = array('mobile_number' => 'required');
		}elseif($request->contact_info=="landline"){
			$model = 'ContLandline';
			$data_array = array('landline_number' => 'required');
		}elseif($request->contact_info=="socmed"){
			$model = 'ContSocialMedia';
			$data_array = array('social_media_url' => 'required');
		}else{
			return response()->json([
				'success' => false,
				'message' => 'Please use contact info type.',
			], 200);
		}


			//set validation
			$validator = Validator::make($request->all(), $data_array );

			//if validation fails
			if ($validator->fails()) {
				return response()->json($validator->errors(), 422);
			}

			if($request->contact_info=="alt_email" && $request->type=="add"){
				$db_data = array(
					'alt_email' => $request->alt_email,
					'createdby' => $request->jobseeker_id,
					'datecreated' => Carbon::now(),
					'link_regid' => $request->jobseeker_id
				);
			}elseif($request->contact_info=="alt_email" && $request->type=="edit"){
				$db_data = array(
					'id' => $request->id,
					'alt_email' => $request->alt_email,
					'link_regid' => $request->jobseeker_id
				);
			}elseif($request->contact_info=="mobile" && $request->type=="add"){
				$db_data = array(
					'mobile_number' => $request->mobile_number,
					'createdby' => $request->jobseeker_id,
					'datecreated' => Carbon::now(),
					'link_regid' => $request->jobseeker_id
				);
			}elseif($request->contact_info=="mobile" && $request->type=="edit"){
				$db_data = array(
					'id' => $request->id,
					'mobile_number' => $request->mobile_number,
					'createdby' => $request->jobseeker_id,
					'datecreated' => Carbon::now(),
					'link_regid' => $request->jobseeker_id
				);
			}elseif($request->contact_info=="landline" && $request->type=="add"){
				$db_data = array(
					'landline_number' => $request->landline_number,
					'createdby' => $request->jobseeker_id,
					'datecreated' => Carbon::now(),
					'link_regid' => $request->jobseeker_id
				);
			}elseif($request->contact_info=="landline" && $request->type=="edit"){
				$db_data = array(
					'id' => $request->id,
					'landline_number' => $request->landline_number,
					'createdby' => $request->jobseeker_id,
					'datecreated' => Carbon::now(),
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

	public function onBoardingGetContacts(Request $request)
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
						'soc_med' => $records_socmed,
					], 200);

	}

	public function onBoardingDeleteContacts(Request $request)
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

	public function onBoardingPrimaryContacts(Request $request)
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
		}else{
			$contact_info->update(['is_primary'=>$request->primary]);
			return response()->json([
				'success' => true,
				'message' => 'Successfully updated.',
			], 200);
		}


	}

	public function contactInfo($modelName,$db_data,$req){

		$model = "App\\Models\\Users\\".$modelName;

		if($req->type=="add"){
			$onboard_contact_info = $model::create($db_data);
			return $onboard_contact_info;
		}else{
			$onboard_contact_info = $model::updateOrCreate(['id'=> $db_data['id']],$db_data);
			return $onboard_contact_info;
		}


	}


	// Get Onboarding Profile

	public function onBoardingGetProfile(Request $request){

			//Get Info

			//set validation
			$validator = Validator::make($request->all(), [
				'jobseeker_id' => 'required',
				]

			);

				//if validation fails
			if ($validator->fails()) {
				return response()->json($validator->errors(), 422);
			}

			if($request->info_type=="basic"){

				$records = OnboardProfileBasicInfo::join('tblm_a_onboard_prereg', 'tblm_b_onboard_actreg_basic.reg_link_preregid', '=', 'tblm_a_onboard_prereg.id')->where('reg_link_preregid', '=', $request->jobseeker_id)->select('tblm_a_onboard_prereg.id as jobseeker_id', 'tblm_a_onboard_prereg.date_submitted' ,'tblm_b_onboard_actreg_basic.*')->first();

				$percentage_complete = $this->totalCompleteness($request->jobseeker_id);

				return response()->json([
					'success' => true,
					'data' => $records,
					'completeness' => $percentage_complete
				], 200);
			}elseif($request->info_type=="contact"){

				$records = DB::table('tblm_a_onboard_prereg')->join('tblm_d_onboard_contact_info','tblm_d_onboard_contact_info.ci_link_regid','=','tblm_a_onboard_prereg.id')->where('tblm_a_onboard_prereg.id', '=', $request->jobseeker_id)->select('tblm_a_onboard_prereg.id as jobseeker_id','tblm_a_onboard_prereg.email','tblm_d_onboard_contact_info.*')->first();
				if($records!==null){
					return response()->json([
						'success' => true,
						'data' => $records
					], 200);
				}else{
					$user = Onboard::where('id', '=', $request->jobseeker_id)->first();
					return response()->json([
						'success' => true,
						'data' => $user
					], 200);
				}


			}elseif($request->info_type=="all"){
				$records = DB::table('tblm_a_onboard_prereg')->join('tblm_d_onboard_contact_info','tblm_d_onboard_contact_info.ci_link_regid','=','tblm_a_onboard_prereg.id')->join('tblm_b_onboard_actreg_basic','tblm_b_onboard_actreg_basic.reg_link_preregid','=','tblm_a_onboard_prereg.id')->where('tblm_a_onboard_prereg.id', '=', $request->jobseeker_id)->first();
				return response()->json([
					'success' => true,
					'data' => $records
				], 200);
			}else{
				return response()->json([
					'success' => false,
					'message' => 'Information type not found'
				], 200);
			}



	}


	// Get Onboarding Profile

	public function onBoardingEmailCheck(Request $request){

		if (Onboard::where('email', '=', $request->email)->count() > 0) {
			return response()->json([
				'success' => false,
				'message' => 'Email already exist.',
			], 200);
		 }else{
			return response()->json([
				'success' => true,
				'message' => 'Email not exist.',
			], 200);
		 }

	}

	public function onBoardingDeleteProfile(Request $request)
	{
		$profile_basic_info = OnboardProfileBasicInfo::where('reg_id', '=', $request->jobseeker_id)->first();
		if($profile_basic_info===null){
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}else{
			//delete Onboard Profile Basic Info
			OnboardProfileContactInfo::where('ci_link_regid', '=', $profile_basic_info->reg_id)->delete();
			fileAttachment::where('fa_link_regid', '=', $profile_basic_info->reg_id)->delete();
			EmploymentHistory::where('we_link_regid', '=', $profile_basic_info->reg_id)->delete();
			CharRef::where('cr_link_regid', '=', $profile_basic_info->reg_id)->delete();
			WfhRef::where('wfr_link_regid', '=', $profile_basic_info->reg_id)->delete();
			EducationalAttainment::where('ea_link_preregid', '=', $profile_basic_info->reg_id)->delete();
			JobApplicationStatus::where('ja_link_preregid', '=', $profile_basic_info->reg_id)->delete();
			$profile_basic_info->delete();

			return response()->json([
				'success' => true,
				'message' => 'Successfully deleted basic profile info.',
			], 200);
		}
	}

	public function updatePass(Request $request){

		$validator = Validator::make($request->all(), [
			'jobseeker_id' => 'required',
			'old_password' => 'required',
			'new_password' => 'required',
			]

		);

			//if validation fails
		if ($validator->fails()) {
			return response()->json($validator->errors(), 422);
		}

		$user = Onboard::where('id', '=', $request->jobseeker_id)->first();

		if (!Hash::check($request->old_password, $user->password)) {
			return response()->json([
				'success' => false,
				'message' => 'Old Password is not exist.',
			], 200);
		 }elseif (Hash::check($request->new_password, $user->password)) {
			return response()->json([
				'success' => false,
				'message' => 'New Password is same with old password.',
			], 200);
		 }else{

			$onboard = Onboard::updateOrCreate(['id' => $request->jobseeker_id],[
				'password'  => Hash::make($request->new_password),
			]);

			return response()->json([
				'success' => true,
				'message' => 'Password successfully updated.',
			], 200);
		 }
	}

	public function onBoardingValidateProfile(Request $request){

		$validator = Validator::make($request->all(), [
			'jobseeker_id' => 'required'
			]

		);

		//if validation fails
		if ($validator->fails()) {
			return response()->json($validator->errors(), 422);
		}

		if($request->step =="prereg"){
			$records = OnboardProfileBasicInfo::join('tblm_a_onboard_prereg', 'tblm_b_onboard_actreg_basic.reg_link_preregid', '=', 'tblm_a_onboard_prereg.id')->where('reg_link_preregid', '=', $request->jobseeker_id)->select('tblm_a_onboard_prereg.id as jobseeker_id', 'tblm_a_onboard_prereg.date_submitted' ,'tblm_b_onboard_actreg_basic.*')->first();

			if($records){
				return response()->json([
					'success' => true,
					'status' => 1,
				], 200);
			}else{
				return response()->json([
					'success' => true,
					'status' => 0,
				], 200);
			}
		}elseif($request->step=="verify"){
			$user = Onboard::where('id', '=', $request->jobseeker_id)->first();

			if($user->is_verified==1){
				return response()->json([
					'success' => true,
					'status' => 1,
				], 200);
			}else{
				return response()->json([
					'success' => true,
					'status' => 0,
				], 200);
			}
		}

	}

	public function completeBasic($id)
	{

		$records = OnboardProfileBasicInfo::join('tblm_a_onboard_prereg', 'tblm_b_onboard_actreg_basic.reg_link_preregid', '=', 'tblm_a_onboard_prereg.id')->where('reg_link_preregid', '=', $id)->select('tblm_a_onboard_prereg.id as jobseeker_id','tblm_b_onboard_actreg_basic.*')->first();

		$total = 3;
		$completed = 0;
		$empty = 0;

		!empty($records->reg_firstname) ? $completed += 1  :  $empty += 1;
		!empty($records->reg_lastname) ? $completed += 1 :  $empty += 1;
		!empty($records->reg_birthdate) ? $completed += 1 :  $empty += 1;


		$percentage_complete = ($completed/$total);

		return $percentage_complete;
	}


	public function totalCompleteness($id)
	{

		$WorkRefController = new WorkRefController;
		$ContactInfoController = new ContactInfoController;
		$CharRefController = new CharRefController;
		$EducationalAttainmentController = new EducationalAttainmentController;
		$EmploymentHistoryController = new EmploymentHistoryController;
		$SkillsController = new SkillsController;
		$fileAttacmentController = new fileAttachmentController;

		$basic_info = $this->completeBasic($id);
		$contact_info = $ContactInfoController->completeContact($id);
		$char_ref = $CharRefController->completeCharRef($id);
		$educational_attainment = $EducationalAttainmentController->completeEduc($id);
		$employment_hist = $EmploymentHistoryController->completeHistory($id);
		$work_ref = $WorkRefController->completeWorkRef($id);
		$skills = $SkillsController->completeSkill($id);
		$fileAttacment = $fileAttacmentController->completeFileAttacment($id);
		$total_completeness = ($basic_info + $contact_info + $employment_hist + $work_ref + $skills +  $fileAttacment)/6*60;

		return $total_completeness;

	}

}
