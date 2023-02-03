<?php


namespace App\Http\Controllers\Api\Client;
use App\Models\Client\ClientReport;
use App\Models\Client\ClientScreenCaptureDtl;
use App\Models\Client\ClientScreenCaptureHdr;
use App\Models\Users\Onboard;

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


class ScreenCaptureController extends Controller
{
	
    public function getScreenshots(Request $request){

        if($request->source=="archived"){

                $img_url = 'https://rssc-images.s3.ap-southeast-1.amazonaws.com/';
        
                $screenshots_data = array();
                $tblm_client = DB::connection('mysql2')->table("tblm_client")->where('id','=',$request->client_id)->first();
                $tblm_legacy =  DB::table("tblm_client_sub_contractor")
                ->join('tblm_b_onboard_actreg_basic','tblm_client_sub_contractor.actreg_contractor_id','=','tblm_b_onboard_actreg_basic.reg_id')
                ->where('tblm_b_onboard_actreg_basic.reg_link_preregid','=',$request->subcon_id)
                ->first();
                
                if($tblm_client){
                    $client_report =  ClientReport::where('userid','=',$tblm_legacy->legacy_user_id)->where('leads_id','=',$tblm_client->client_id_legacy)->select('id','_id', DB::raw('DATE_FORMAT(datetime, "%Y-%m-%d") as formatted_date'),DB::raw('DATE_FORMAT(datetime, "%Y-%m-%d %H:%i:00") as formatted_date_time'),DB::raw('WEEK(DATE_FORMAT(datetime, "%Y-%m-%d")) as formatted_date_timeweek'),DB::raw('DATE_FORMAT(datetime, "%Y") as formatted_date_year'))->having('formatted_date', '>=', $request->start_date)
                ->having('formatted_date', '<=' ,$request->end_date)
                ->orderBy('id', 'asc')
                ->paginate($request->limit ? $request->limit : ClientReport::count());

                $client_report->transform(function ($item){
                        $img_url = 'https://rssc-images.s3.ap-southeast-1.amazonaws.com/';
                        $week = sprintf("%02d", $item->formatted_date_timeweek);

                        $monitor = empty($item->screen_count) ? 1 : $item->screen_count;
                        $full_url = $img_url.$item->formatted_date_year.'/'.$week.'/screen/'.$item->_id.'-'.$monitor.'.jpg';
                        $item->date_time = $item->formatted_date_time;
                        $item->src = $full_url;
                        $item->title = $item->formatted_date_time;
                        return $item;
                    });  
                    
                if(!$client_report){
                        return response()->json([
                            'success' => false,
                            'message' => 'No screenshots found.',
                        ], 200);
                }else{
                        return response()->json([
                            'success' => true,
                            'data' => $client_report,
                        ], 200);
                }
            }

        }elseif($request->source=="current"){

            $screencapturedtl = ClientScreenCaptureDtl::join('tblt_screencap_hdr','tblt_screencap_hdr.id','=','tblt_screencap_dtl.link_screencap_hdr_id')->select('tblt_screencap_dtl.*',DB::raw('DATE_FORMAT(tblt_screencap_dtl.datecreated, "%Y-%m-%d") as formatted_date'),DB::raw('DATE_FORMAT(tblt_screencap_dtl.datecreated, "%Y-%m-%d %H:%i:00") as formatted_date_time'))->where('link_subcon_id','=',$request->subcon_id)->where('link_client_id',$request->client_id)
            //$request->end_date)
            ->having('formatted_date', '>=', $request->start_date)
            ->having('formatted_date', '<=' ,$request->end_date)
            ->orderBy('id', 'desc')
            ->paginate($request->limit ? $request->limit : ClientScreenCaptureDtl::count());
    
            $screencapturedtl->transform(function ($item){
                $img_url = 'https://s3b-ges-prod-01-pyt.s3.amazonaws.com';
                $full_url = $img_url.'/'.$item->photo;
                $item->date_time = $item->formatted_date_time;
                $item->src = $full_url;
                $item->title = $item->formatted_date_time;
                return $item;
            });  

            if(!$screencapturedtl){
                    return response()->json([
                        'success' => false,
                        'message' => 'No screenshots found.',
                    ], 200);
            }else{
                    return response()->json([
                        'success' => true,
                        'data' => $screencapturedtl,
                    ], 200);
            }

        }

    }

    function getDatesFromRange($start, $end, $format = 'Y-m-d') {
        $array = array();
        $interval = new DateInterval('P1D');
        
        $_end = date('Y-m-d', strtotime($end . ' +1 day'));
    
        $realEnd = new DateTime($end);
        $realEnd->add($interval);
    
        $period = new DatePeriod(new DateTime($start), $interval, $realEnd);
    
        foreach($period as $date) { 
            $array[] = $date->format($format); 
        }
    
        return $array;
    }

    
}

