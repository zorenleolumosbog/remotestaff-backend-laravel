<?php

namespace App\Http\Controllers\Api\Users;

use App\Models\Users\WfhRef;
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


class jobApplicationController extends Controller
{
	public function store(Request $request)
    {
		//set validation
		$validator = Validator::make($request->all(), [
				'application_date' => 'required',
				'job_title' => 'required',
				'application_status' => 'required',
				'job_status' => 'required',
				'jobseeker_id' => 'required'
			]

		);

		//if validation fails
		if ($validator->fails()) {
			return response()->json($validator->errors(), 422);
		}

		//create Job Application Status
		$onboard_job_application_status = JobApplicationStatus::
		updateOrCreate([
				'ja_link_preregid' => $request->jobseeker_id, 
				'ja_id' => $request->ja_id
			],
			[
				'ja_application_date' => $request->application_date,
				'ja_job_title' => $request->job_title,
				'ja_application_status' => $request->application_status,
				'ja_job_status' => $request->job_status,
				'ja_link_preregid' => $request->jobseeker_id
			]);
		
		return response()->json([
					'success' => true,
					'message' => 'Successfully update job application status.',
					'data' => $onboard_job_application_status,
				], 200);
	}

	public function get(Request $request)
    {
		//get Job Application Status
		$onboard_job_application_status = JobApplicationStatus::where('ja_link_preregid', '=', $request->jobseeker_id)->get();
		if($onboard_job_application_status===null){
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}else{
			return response()->json([
				'success' => true,
				'data' => $onboard_job_application_status,
			], 200);
		}
		
	}

	public function delete(Request $request)
	{
		$onboard_job_application_status = JobApplicationStatus::where('ja_link_preregid', '=', $request->jobseeker_id)->where('ja_id', '=', $request->id)->first();
		if($onboard_job_application_status===null){
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}else{
			//delete Job Application Status
			$onboard_job_application_status->delete();
			return response()->json([
				'success' => true,
				'message' => 'Successfully delete job application status.',
			], 200);
		}
	}

	
	

}
