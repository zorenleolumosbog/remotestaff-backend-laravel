<?php

namespace App\Http\Controllers\Api\Users;

use App\Models\Users\WorkRef;
use App\Models\Users\Onboard;

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


class WorkRefController extends Controller
{
	public function store(Request $request)
    {
		//set validation
		$validator = Validator::make($request->all(), [
				'availability'     => 'required',
				'emp_preference'  => 'required',
				'timezone' => 'required',
				'latest_job_title' => 'required',
				'workingmodel' => 'required',
				'fulltime_agreedsalary' => 'required',
				'parttime_agreedsalary' => 'required',
				'years_of_exp' => 'required'
			]

		);

			//if validation fails
		if ($validator->fails()) {
			return response()->json($validator->errors(), 422);
		}

		//create Onboarding Basic Info
		$onboard_wrk_ref = WorkRef::updateOrCreate(['wp_link_regid' => $request->jobseeker_id],[
			'wp_availability'  => $request->availability,
			'wp_emp_preference'  => $request->emp_preference,
			'wp_timezone'  => $request->timezone,
			'wp_latest_job_title'  => $request->latest_job_title,
			'wp_workingmodel'  => $request->workingmodel,
			'wp_fulltime_agreedsalary'  => $request->fulltime_agreedsalary,
			'wp_parttime_agreedsalary'  => $request->parttime_agreedsalary,
			'wp_years_of_exp'  => $request->years_of_exp,
			'wp_datecreated'  => Carbon::now(),
			'wp_createdby'  => $request->jobseeker_id,
			'wp_link_regid'  => $request->jobseeker_id,
			'wp_datemodified' => Carbon::now(),
			'wp_modifiedby' => $request->jobseeker_id
		]);
		
		return response()->json([
					'success' => true,
					'message' => 'Successfully update work reference.',
					'data' => $onboard_wrk_ref,
				], 200);
	}

	public function get(Request $request)
    {
		//set validation
		$validator = Validator::make($request->all(), [
			'jobseeker_id' => 'required',
			]

		);

			//if validation fails
		if ($validator->fails()) {
			return response()->json($validator->errors(), 422);
		}

		$onboard_wrk_ref = WorkRef::where('wp_link_regid', '=', $request->jobseeker_id)->first();
		
		//$onboard_wrk_ref = WorkRef::join('tblm_notice_period','tblm_f_onboard_work_preference.wp_availability','=','tblm_notice_period.id')->join('tblm_timezone','tblm_f_onboard_work_preference.wp_timezone','=','tblm_timezone.id')->where('tblm_f_onboard_work_preference.wp_link_regid', $request->jobseeker_id)->select('tblm_f_onboard_work_preference.*','tblm_notice_period.description as notice','tblm_timezone.description as timezone')->get();

		if($onboard_wrk_ref===null){
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}else{
			return response()->json([
				'success' => true,
				'data' => $onboard_wrk_ref,
			], 200);
		}
		
	}


	public function delete(Request $request)
	{
		//set validation
		$validator = Validator::make($request->all(), [
			'jobseeker_id' => 'required',
			'id' => 'required',
			]

		);
		
		$onboard_wrk_ref = WorkRef::where('wp_link_regid', '=', $request->jobseeker_id)->where('wp_id', '=', $request->id)->first();
		if($onboard_wrk_ref===null){
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}else{
			//delete Work from Home Resources
			$onboard_wrk_ref->delete();
			return response()->json([
				'success' => true,
				'message' => 'Successfully deleted work reference.',
			], 200);
		}
	}


	public function completeWorkRef($id)
	{

		$onboard_wrk_ref = WorkRef::where('wp_link_regid', '=', $id)->first();
				
		$completed = 0;
		$empty = 0;
				
		!empty($onboard_wrk_ref->wp_availability) ? $completed += 1  :  $empty += 1;
		!empty($onboard_wrk_ref->wp_emp_preference) ? $completed += 1 :  $empty += 1;
		!empty($onboard_wrk_ref->wp_latest_job_title) ? $completed += 1 :  $empty += 1;
		!empty($onboard_wrk_ref->wp_workingmodel) ? $completed += 1 :  $empty += 1;
		!empty($onboard_wrk_ref->wp_fulltime_agreedsalary) ? $completed += 1 :  $empty += 1;
		!empty($onboard_wrk_ref->wp_parttime_agreedsalary) ? $completed += 1 :  $empty += 1;
		!empty($onboard_wrk_ref->wp_years_of_exp) ? $completed += 1 :  $empty += 1;

		$total = 7;
		$percentage_complete = ($completed / $total);
		
		return $percentage_complete;
	}


	public function getNotice(Request $request)
    {
		$notice_period = DB::table('tblm_notice_period')->get();
	
		return response()->json([
			'data' => $notice_period,
			'success' => true,
		], 200);
		
	}

	public function getTimezone(Request $request)
    {
		$timezone = DB::table('tblm_timezone')->get();
	
		return response()->json([
			'data' => $timezone,
			'success' => true,
		], 200);
		
	}
	

}
