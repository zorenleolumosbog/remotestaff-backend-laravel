<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Client\AttendanceDetail;
use App\Models\Client\AttendanceHeader;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getRemoteContractorAttendance(Request $request)
    {
        //Set validation
		$validator = Validator::make($request->all(), [
            'client_id' => 'required',
            'remote_contractor_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required'
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $attendance_header = AttendanceHeader::
        where('link_client_id', $request->client_id)
        ->where('link_subcon_id', $request->remote_contractor_id)
        ->first();

        if(!$attendance_header) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        $total_work_hours = AttendanceDetail::
        select('id', 'link_tms_hdr_id', 'date_worked', 'work_total_hours')
        ->where('link_tms_hdr_id', $attendance_header->id)
        ->whereDate(DB::raw("Date(CONVERT_TZ(CONCAT_WS(' ', date_worked, work_time_in), '+00:00', '+08:00'))"), '>=', $request->start_date)
        ->whereDate(DB::raw("Date(CONVERT_TZ(CONCAT_WS(' ', date_worked, work_time_in), '+00:00', '+08:00'))"), '<=', $request->end_date)
        ->where('dtl_type', 'work')
        ->get()
        ->sum('work_total_hours');

        $total_lunch_hours = AttendanceDetail::
        select('id', 'link_tms_hdr_id', 'date_worked', 'work_total_hours')
        ->where('link_tms_hdr_id', $attendance_header->id)
        ->whereDate(DB::raw("Date(CONVERT_TZ(CONCAT_WS(' ', date_worked, work_time_in), '+00:00', '+08:00'))"), '>=', $request->start_date)
        ->whereDate(DB::raw("Date(CONVERT_TZ(CONCAT_WS(' ', date_worked, work_time_in), '+00:00', '+08:00'))"), '<=', $request->end_date)
        ->where('dtl_type', 'lunch')
        ->get()
        ->sum('work_total_hours');

        $attendance_details = AttendanceDetail::
        select('id', 'link_tms_hdr_id', 'date_worked', 'dtl_type', 'work_time_in',
        DB::raw("SUM(work_total_hours) as work_total_hours,
        Date(CONVERT_TZ(CONCAT_WS(' ', date_worked, work_time_in), '+00:00', '+08:00')) as datecreated"))
        ->where('link_tms_hdr_id', $attendance_header->id)
        ->whereDate(DB::raw("Date(CONVERT_TZ(CONCAT_WS(' ', date_worked, work_time_in), '+00:00', '+08:00'))"), '>=', $request->start_date)
        ->whereDate(DB::raw("Date(CONVERT_TZ(CONCAT_WS(' ', date_worked, work_time_in), '+00:00', '+08:00'))"), '<=', $request->end_date)
        ->where('dtl_type', 'work')
        ->groupBy(DB::raw("Date(CONVERT_TZ(CONCAT_WS(' ', date_worked, work_time_in), '+00:00', '+08:00'))"))
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : AttendanceDetail::count());

        $mapping = $attendance_details->map(function ($attendance_detail) use ($attendance_header, $request){
            $attendance_detail->works = AttendanceDetail::
                    where('link_tms_hdr_id', $attendance_header->id)
                    ->where('dtl_type', 'work')
                    ->where(DB::raw("Date(CONVERT_TZ(CONCAT_WS(' ', date_worked, work_time_in), '+00:00', '+08:00'))"), $attendance_detail->datecreated)
                    ->get();

            $attendance_detail->lunch = AttendanceDetail::
                    where('link_tms_hdr_id', $attendance_header->id)
                    ->where('dtl_type', 'lunch')
                    ->where(DB::raw("Date(CONVERT_TZ(CONCAT_WS(' ', date_worked, work_time_in), '+00:00', '+08:00'))"), $attendance_detail->datecreated)
                    ->first();

            return $attendance_detail;
        });

        return response()->json([
            'success' => true,
            'total_actual_work_hours' => number_format($total_work_hours - $total_lunch_hours, 2),
            'total_adjusted_hours' => number_format(0, 2),
            'total_regular_work_hours' => number_format(0, 2),
            'data' => $attendance_details->setCollection(collect($mapping)),
        ], 200);
    }
}
