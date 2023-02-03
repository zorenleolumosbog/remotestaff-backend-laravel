<?php

namespace App\Http\Controllers\Api\Users;

use App\Models\Users\EmploymentHistory;
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


class EmploymentHistoryController extends Controller
{
	public function store(Request $request)
    {

		
		
		if($request->type=="add"){

			$validator = Validator::make($request->all(), [
				'jobseeker_id' => 'required',
				'position' => 'required',
				'employer_name' => 'required',
				'start_date' => 'required',
				'end_date' => 'required',
				'country' => 'required',
				]
	
			);
	
				//if validation fails
				if ($validator->fails()) {
					return response()->json($validator->errors(), 422);
				}

				//create Onboarding Basic Info
			$onboard_employment_history = EmploymentHistory::Create([
				'we_link_reg_id'  => $request->jobseeker_id,
				'we_position_held'  => $request->position,
				'we_er_name'  => $request->employer_name,
				'we_start_date'  => $request->start_date,
				'we_end_date'  => $request->end_date,
				'we_country_id'  => $request->country,
			]);
			
			return response()->json([
						'success' => true,
						'message' => 'Successfully added employment history.',
						'data' => $onboard_employment_history,
					], 200);

		}elseif($request->type=="edit"){
			$onboard_employment_history = EmploymentHistory::updateOrCreate(['we_id' => $request->id],[
				'we_link_reg_id'  => $request->jobseeker_id,
				'we_position_held'  => $request->position,
				'we_er_name'  => $request->employer_name,
				'we_start_date'  => $request->start_date,
				'we_end_date'  => $request->end_date,
				'we_country_id'  => $request->country
			]);
			
			return response()->json([
						'success' => true,
						'message' => 'Successfully edited employment history',
						'data' => $onboard_employment_history,
					], 200);
		}else{
			return response()->json([
						'success' => false,
						'message' => 'Wrong type!'
					], 200);
		}
		
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

		$onboard_employment_history = DB::table('tblm_e_onboard_work_history')->join('tblm_country','tblm_e_onboard_work_history.we_country_id','=','tblm_country.id')->where('tblm_e_onboard_work_history.we_link_reg_id', $request->jobseeker_id)->select('tblm_e_onboard_work_history.*','tblm_country.short_desc as country_short_desc','tblm_country.long_desc as country_long_desc')->get();
		
		
		return response()->json([
					'success' => true,
					'data' => $onboard_employment_history,
				], 200);
	}
	
	public function delete(Request $request)
	{
		$onboard_employment_history = EmploymentHistory::where('we_link_reg_id', '=', $request->jobseeker_id)->where('we_id', '=', $request->id)->first();
		
		if($onboard_employment_history===null){
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}else{
			//delete Employment History
			$onboard_employment_history->delete();
			return response()->json([
				'success' => true,
				'message' => 'Successfully deleted employment history.',
			], 200);
		}
	}
	

	public function completeHistory($id)
	{
		$onboard_employment_history = DB::table('tblm_e_onboard_work_history')->join('tblm_country','tblm_e_onboard_work_history.we_country_id','=','tblm_country.id')->where('tblm_e_onboard_work_history.we_link_reg_id', $id)->select('tblm_e_onboard_work_history.*','tblm_country.short_desc as country_short_desc','tblm_country.long_desc as country_long_desc')->get();
		
		$completed = 0;
		$empty = 0;
		$total = 5;
		$count = 0;
				
		foreach($onboard_employment_history as $history){
			if($count==1){
				!empty($history->we_position_held) ? $completed += 1  :  $empty += 1;
				!empty($history->we_er_name) ? $completed += 1 :  $empty += 1;
				!empty($history->we_start_date) ? $completed += 1 :  $empty += 1;
				!empty($history->we_end_date) ? $completed += 1 :  $empty += 1;
				!empty($history->we_country_id) ? $completed += 1 :  $empty += 1;
			}
			
			$count++;
		}
		

		$percentage_complete = ($completed/$total);
		
		return $percentage_complete;
		
	}

}
