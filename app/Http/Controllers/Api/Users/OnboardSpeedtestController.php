<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\Users\OnboardSpeedtest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class OnboardSpeedtestController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $reg_id)
    {
        $speedtest = OnboardSpeedtest::
        where('link_reg_id', $reg_id)
        ->orderBy('datecreated', 'desc')
        ->paginate($request->limit ? $request->limit : OnboardSpeedtest::count());

        if( $speedtest->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $speedtest,
        ], 200);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($reg_id)
    {
        $speedtest = OnboardSpeedtest::
        where('link_reg_id', $reg_id)
        ->orderBy('datecreated', 'desc')
        ->take(1)
        ->first();

        if( !$speedtest) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $speedtest,
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Set validation
		$validator = Validator::make($request->all(), [
            'reg_basic_id' => 'required|exists:tblm_b_onboard_actreg_basic,reg_id',
            'latency' => 'required|numeric',
            'download_speed' => 'required|numeric',
            'upload_speed' => 'required|numeric'
        ],
        [
            'reg_basic_id.required' => 'The Jobseeker Basic Info is required.',
            'reg_basic_id.exists' => 'The Jobseeker Basic Info does not exists.',
            'latency.required' => 'The Latency is required.',
            'latency.numeric' => 'The Latency must be a number.',
            'download_speed.required' => 'The Download Speed is required.',
            'download_speed.numeric' => 'The Download Speed must be a number.',
            'upload_speed.required' => 'The Upload Speed is required.',
            'upload_speed.numeric' => 'The Upload Speed must be a number.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $registrant = OnboardSpeedtest::create(
        [
            'link_reg_id' => $request->reg_basic_id,
            'latency' => $request->latency,
            'download_speed' => $request->download_speed,
            'upload_speed' => $request->upload_speed,
            'createdby' => auth()->user()->id,
            'datecreated' => Carbon::now()
        ]);
		
		return response()->json([
					'success' => true,
					'message' => 'Successfully Added.',
					'data' => $registrant,
				], 200);
    }

    public function upload(Request $request)
    {
        $path = $request->file('file')->store('public');
        Storage::delete($path);
    }
}
