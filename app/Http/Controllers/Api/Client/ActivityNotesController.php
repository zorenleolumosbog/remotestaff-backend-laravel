<?php


namespace App\Http\Controllers\Api\Client;
use App\Models\Client\ClientReport;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Auth;
use Carbon\CarbonPeriod;
use DateTime;
use DateInterval;
use DatePeriod;


class ActivityNotesController extends Controller
{
	
    public function getActivityNotes(Request $request){
        $tblm_client = DB::connection('mysql2')->table("tblm_client")->where('id','=',$request->client_id)->first();

        $tblm_subcon = DB::connection('mysql')->table("tblm_b_onboard_actreg_basic")->where('reg_link_preregid','=',$request->subcon_id)->first();
        
        $leads_id = $tblm_client->client_id_legacy;

        $subcon_id = $tblm_subcon->legacy_user_id;

        $client_report = DB::connection('mysql')->table("legacy_activity_notes")
        ->where('userid','=',$subcon_id)->where('leads_id','=', $leads_id)->whereNotNull('note')
        ->select('id','note','requested',
        'status','responded',
        DB::raw('DATE_FORMAT(requested, "%Y-%m-%d") as formatted_date,
        DATE_FORMAT(requested, "%Y-%m-%d %H:%i:00") as formatted_date_time
        '),
        )
        ->having('formatted_date', '>=', $request->start_date)
       ->having('formatted_date', '<=' ,$request->end_date)
       ->groupBy('formatted_date_time')
        ->orderBy('formatted_date_time','asc')
       ->paginate($request->limit ? $request->limit : table("legacy_activity_notes")::count());

        /*$client_report =  ClientReport::where('userid','=',$subcon_id)->where('leads_id','=', $leads_id)->whereNotNull('activity_note')
        ->select('id','activity_note','activity_note_requested',
        'activity_note_status','activity_note_responded',
        DB::raw('DATE_FORMAT(datetime, "%Y-%m-%d") as formatted_date,
        DATE_FORMAT(activity_note_requested, "%Y-%m-%d %H:%i:00") as formatted_date_time
        '),
        )
        ->having('formatted_date', '>=', $request->start_date)
       ->having('formatted_date', '<=' ,$request->end_date)
       ->groupBy('formatted_date_time')
        ->orderBy('formatted_date_time','asc')
       ->paginate($request->limit ? $request->limit : ClientReport::count());*/

       /*$client_report =  ClientReport::where('userid','=',$request->subcon_id)->where('leads_id','=',$request->client_id)->
        select('id','activity_note','activity_note_requested',
        'activity_note_responded','activity_note_status',
        DB::raw('DATE_FORMAT(datetime, "%Y-%m-%d") as formatted_date'))->having('formatted_date', '>=', '2022-11-01')
       ->having('formatted_date', '<=' ,'2022-11-02')->get();*/

       if( $client_report->count() == 0 ) {
            return response()->json([
                'success' => false,
                'leagcy_id' => $leads_id,
                'message' => 'No data found.',
            ], 200);
        }

       return response()->json([
        'success' => true,
        'data'  => $client_report
        ], 200);
    }
    
}

