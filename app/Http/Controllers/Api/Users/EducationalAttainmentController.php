<?php

namespace App\Http\Controllers\Api\Users;

use App\Models\Users\Onboard;
use App\Models\Users\EducationalAttainment;

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


class EducationalAttainmentController extends Controller
{
	// Store / Update Onboarding Profile	
	public function store(Request $request)
    {
		//set validation
		$validator = Validator::make($request->all(), [
				'degree_level' => 'required',
				'major' => 'required',
				'field' => 'required',
				'institute' => 'required',
				'country_id' => 'required',
				'graddate' => 'required',
				'jobseeker_id' => 'required'
			]

		);


		//if validation fails
		if ($validator->fails()) {
			return response()->json($validator->errors(), 422);
		}

		if($request->type=="add"){
			//create Educational Attainment Data
			$onboard_educational_attainment = EducationalAttainment::
			Create([
					'degree_level' => $request->degree_level,
					'major' => $request->major,
					'field' => $request->field,
					'institute' => $request->institute,
					'country_id' => $request->country_id,
					'graddate' => $request->graddate,
					'gpa' => $request->gpa,
					'licensecert' => $request->licensecert,
					'semtrainings' => $request->semtrainings,
					'link_regid' => $request->jobseeker_id,
				]);
			
			return response()->json([
						'success' => true,
						'message' => 'Successfully added educational attainment.',
						'data' => $onboard_educational_attainment,
					], 200);
		}elseif($request->type=="edit"){

			$onboard_educational_attainment = EducationalAttainment::
			updateOrCreate([
					'id' => $request->id
				],
				[
					'degree_level' => $request->degree_level,
					'major' => $request->major,
					'field' => $request->field,
					'institute' => $request->institute,
					'country_id' => $request->country_id,
					'graddate' => $request->graddate,
					'gpa' => $request->gpa,
					'licensecert' => $request->licensecert,
					'semtrainings' => $request->semtrainings,
					'link_regid' => $request->jobseeker_id,
				]);
			
			return response()->json([
						'success' => true,
						'message' => 'Successfully update educational attainment.',
						'data' => $onboard_educational_attainment,
					], 200);

		}		
	}

	public function get(Request $request)
    {
		//get Educational Attainment
		$onboard_educational_attainment = EducationalAttainment::join('tblm_degree_level','tblm_degree_level.id','=','tblm_i_onboard_educ_attain.degree_level')->join('tblm_field_of_study','tblm_field_of_study.id','=','tblm_i_onboard_educ_attain.field')->join('tblm_country','tblm_country.id','=','tblm_i_onboard_educ_attain.country_id')->select('tblm_i_onboard_educ_attain.*','tblm_degree_level.description as level','tblm_field_of_study.description as study_field','tblm_country.long_desc as country')->where('link_regid', '=', $request->jobseeker_id)->get();
		if($onboard_educational_attainment===null){
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}else{
			return response()->json([
				'success' => true,
				'data' => $onboard_educational_attainment,
			], 200);
		}
		
	}

	public function delete(Request $request)
	{
		//Delete Educational Attainment
		$onboard_educational_attainment = EducationalAttainment::where('link_regid', '=', $request->jobseeker_id)->where('id', '=', $request->id)->first();
		
		if($onboard_educational_attainment===null){
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}else{
			//delete Educational Attainment
			$onboard_educational_attainment->delete();
			return response()->json([
				'success' => true,
				'message' => 'Successfully delete educational attainment.',
			], 200);
		}
	}

	public function getLevel(Request $request)
    {

		$degree_level = DB::table('tblm_degree_level')->get();
	
		return response()->json([
			'data' => $degree_level,
			'success' => true,
		], 200);
	}

	public function getFieldStudy(Request $request)
    {

		$field_of_study = DB::table('tblm_field_of_study')->get();
	
		return response()->json([
			'data' => $field_of_study,
			'success' => true,
		], 200);
	}

	public function completeEduc($id)
	{
		$onboard_educational_attainment = EducationalAttainment::where('link_regid', '=', $id)->get();
		
		$completed = 0;
		$empty = 0;
		$total = 8;
				
		foreach($onboard_educational_attainment as $educ){
			!empty($educ->ea_qualification) ? $completed += 1  :  $empty += 1;
			!empty($educ->ea_major) ? $completed += 1 :  $empty += 1;
			!empty($educ->ea_institute) ? $completed += 1 :  $empty += 1;
			!empty($educ->ea_location) ? $completed += 1 :  $empty += 1;
			!empty($educ->ea_graddate) ? $completed += 1 :  $empty += 1;
			!empty($educ->ea_gpa) ? $completed += 1 :  $empty += 1;
			!empty($educ->ea_licensecert) ? $completed += 1 :  $empty += 1;
			!empty($educ->ea_semtrainings) ? $completed += 1 :  $empty += 1;
			
			//$total++;
		}
		
		$percentage_complete = ($total / 100);
		
		return $percentage_complete;
		
	}
	

}
