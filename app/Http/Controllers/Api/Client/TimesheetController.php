<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Admin\ClientRemoteContractor;
use App\Models\Client\TimesheetDetail;
use App\Models\Client\TimesheetHeader;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimesheetController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getRemoteContractorTimesheet(Request $request)
    {
        //Set validation
		$validator = Validator::make($request->all(), [
            'client_id' => 'required',
            'remote_contractor_id' => 'required|exists:tblm_client_sub_contractor,reg_link_preregid',
            'start_date' => 'required',
            'end_date' => 'required'
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $client_remote_contractor = ClientRemoteContractor::
        where('reg_link_preregid', $request->remote_contractor_id)
        ->first();

        $timesheet_header = TimesheetHeader::
        where('link_client_id', $request->client_id)
        ->where('link_subcon_id', $client_remote_contractor->id)
        ->first();

        if(!$timesheet_header) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        $timesheet_details = TimesheetDetail::
        where('link_tms_hdr', $timesheet_header->id)
        ->paginate($request->limit ? $request->limit : TimesheetDetail::count());

        $total_hours = TimesheetDetail::
        select('id', 'link_tms_hdr', 'date_worked')
        ->selectRaw('SUM(work_total_hours) as work_total_hours,
        SUM(reg_ros_hours) as reg_ros_hours,
        SUM(lunch_total_hours) as lunch_total_hours')
        ->where('link_tms_hdr', $timesheet_header->id)
        ->whereDate('date_worked', '>=', $request->start_date)
        ->whereDate('date_worked', '<=', $request->end_date)
        ->groupBy('link_tms_hdr')
        ->first();

        return response()->json([
            'success' => true,
            'total_actual_work_hours' => number_format($total_hours->work_total_hours, 2),
            'total_adjusted_hours' => number_format(0, 2),
            'total_regular_work_hours' => number_format($total_hours->reg_ros_hours, 2),
            'data' => $timesheet_details,
        ], 200);
    }
}
