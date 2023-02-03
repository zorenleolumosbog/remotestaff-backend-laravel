<?php

namespace App\Http\Controllers\Api\Users;

use App\Models\Users\CharRef;
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


class CharRefController extends Controller
{
	public function store(Request $request)
    {
		//set validation
		$validator = Validator::make($request->all(), [
				'name'     => 'required',
				'prof_relation'  => 'required',
				'years_known' => 'required',
				'company' => 'required',
				'contact' => 'required',
				'email' => 'required',
				'jobseeker_id' => 'required'
			]

		);

			//if validation fails
		if ($validator->fails()) {
			return response()->json($validator->errors(), 422);
		}
		

		if($request->type=="add"){
		
			$onboard_character_ref = CharRef::create([
				'name'  => $request->name,
				'prof_relation'  => $request->prof_relation,
				'yearsknown'  => $request->years_known,
				'company'  => $request->company,
				'contact_mobile'  => $request->contact,
				'contact_email'  => $request->email,
				'link_regid'  => $request->jobseeker_id,
				'createdby'  => $request->jobseeker_id,
				'datecreated'  => Carbon::now(),
			]);
			
			return response()->json([
						'success' => true,
						'message' => 'Successfully added charater reference.',
						'data' => $onboard_character_ref,
					], 200);
		}elseif($request->type=="edit"){

			$onboard_character_ref = CharRef::updateOrCreate(['id' => $request->id],[
				'name'  => $request->name,
				'prof_relation'  => $request->prof_relation,
				'yearsknown'  => $request->years_known,
				'company'  => $request->company,
				'contact_mobile'  => $request->contact,
				'contact_email'  => $request->email,
				'link_regid'  => $request->jobseeker_id,
				'modifiedby'  => $request->jobseeker_id,
				'datemodified'  => Carbon::now(),
			]);
			
			return response()->json([
						'success' => true,
						'message' => 'Successfully edited employment history',
						'data' => $onboard_character_ref,
					], 200);
		}else{
			return response()->json([
						'success' => false,
						'message' => 'Wrong type!'
					], 200);
		}
		
		return response()->json([
					'success' => true,
					'message' => 'Successfully update basic information.',
					'data' => $onboard_character_ref,
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

		$onboard_character_ref = CharRef::where('link_regid', '=', $request->jobseeker_id)
		->get()->paginate($request->limit ? $request->limit : CharRef::count());

		if($onboard_character_ref===null){
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}else{
			return response()->json([
				'success' => true,
				'data' => $onboard_character_ref,
			], 200);
		}
		
	}
	
	public function delete(Request $request)
	{
		$onboard_character_ref = CharRef::where('link_regid', '=', $request->jobseeker_id)->where('id', '=', $request->id)->first();
		if($onboard_character_ref===null){
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}else{
			//delete Character Reference
			$onboard_character_ref->delete();
			return response()->json([
				'success' => true,
				'message' => 'Successfully deleted character reference.',
			], 200);
		}
	}
	

	public function completeCharRef($id)
	{
		$onboard_character_ref = CharRef::where('link_regid', '=', $id)->get();
		
		$completed = 0;
		$empty = 0;
		$total = 6;
				
		foreach($onboard_character_ref as $char){
			!empty($char->cr_name) ? $completed += 1  :  $empty += 1;
			!empty($char->cr_prof_relation) ? $completed += 1 :  $empty += 1;
			!empty($char->cr_yearsknown) ? $completed += 1 :  $empty += 1;
			!empty($char->cr_company) ? $completed += 1 :  $empty += 1;
			!empty($char->cr_contact) ? $completed += 1 :  $empty += 1;
			!empty($char->cr_email) ? $completed += 1 :  $empty += 1;
			
			//$total++;
		}
		
		$percentage_complete = ($completed / $total) * 100;
		
		return $percentage_complete;
		
	}

}
