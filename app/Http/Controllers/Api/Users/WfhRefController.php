<?php

namespace App\Http\Controllers\Api\Users;


use App\Models\Users\WFHRef;

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


class WfhRefController extends Controller
{
	public function store(Request $request)
    {
		//set validation
		$validator = Validator::make($request->all(), [
				'work_env'     => 'required',
				'net_type'  => 'required',
				'net_bandwidth' => 'required',
				'speed_download' => 'required',
				'speed_upload' => 'required',
				'hardware_type' => 'required',
				'brand_name' => 'required',
				'os' => 'required',
				'ram' => 'required',
				'headseat_brand' => 'required',
				'webcam_brand' => 'required',
				'jobseeker_id' => 'required',
			]

		);

			//if validation fails
		if ($validator->fails()) {
			return response()->json($validator->errors(), 422);
		}

		//create Onboarding Basic Info
		$onboard_wfh_ref = WFHRef::updateOrCreate(['wfr_link_regid' => $request->jobseeker_id],[
			'wfr_workenv'  => $request->work_env,
			'wfr_nettype'  => $request->net_type,
			'wfr_netbandwidth'  => $request->net_bandwidth,
			'wfr_speeddownload'  => $request->speed_download,
			'wfr_speedupload'  => $request->speed_upload,
			'wfr_comp_hardwaretype'  => $request->hardware_type,
			'wfr_comp_brandname'  => $request->brand_name,
			'wfr_comp_processor'  => $request->processor,
			'wfr_comp_os'  => $request->os,
			'wfr_comp_ram'  => $request->ram,
			'wfr_comp_headseatbrand'  => $request->headseat_brand,
			'wfr_comp_webcambrand'  => $request->webcam_brand,
			'wfr_link_regid'  => $request->jobseeker_id,
		]);
		
		return response()->json([
					'success' => true,
					'message' => 'Successfully update.',
					'data' => $onboard_wfh_ref,
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

		$onboard_wfh_ref = WFHRef::where('wfr_link_regid', '=', $request->jobseeker_id)->first();
		if($onboard_wfh_ref===null){
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}else{
			return response()->json([
				'success' => true,
				'data' => $onboard_wfh_ref,
			], 200);
		}
		
	}


	public function delete(Request $request)
	{
		$onboard_wfh_resources = WFHRef::where('wfr_link_regid', '=', $request->jobseeker_id)->where('wfr_id', '=', $request->id)->first();
		if($onboard_wfh_resources===null){
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}else{
			//delete Work from Home Resources
			$onboard_wfh_resources->delete();
			return response()->json([
				'success' => true,
				'message' => 'Successfully deleted.',
			], 200);
		}
	}

	public function getNetType(Request $request)
    {

		$internet_type = DB::table('tblm_internet_type')->get();

		return response()->json([
			'data' => $internet_type,
			'success' => true,
		], 200);
	}

	public function getISP(Request $request)
    {

		$isp = DB::table('tblm_isp')->get();

		return response()->json([
			'data' => $isp,
			'success' => true,
		], 200);
	}

	public function getHardware(Request $request)
    {

		$hardwaretype = DB::table('tblm_comp_hardwaretype')->get();

		return response()->json([
			'data' => $hardwaretype,
			'success' => true,
		], 200);
	}



	

	
	


	
	

}
