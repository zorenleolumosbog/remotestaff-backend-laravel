<?php


namespace App\Http\Controllers\Api\Timesheet;
use App\Models\Timesheet\TimesheetDtl;
use App\Models\Timesheet\TimesheetHdr;
use App\Models\Timesheet\TimesheetAdjDtl;
use App\Models\Timesheet\TimesheetAdjHdr;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin\Client;
use App\Models\Users\OnboardProfileBasicInfo;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Mail;
use Carbon\Carbon;
use Auth;
use Carbon\CarbonPeriod;
use DateTime;
use DateInterval;
use DatePeriod;


class TimesheetController extends Controller
{
	public function store(Request $request)
    {

        $adjust_data = json_decode($request->adjust_data);

        $start_date = Carbon::parse($request->date_from);
        $end_date = Carbon::parse($request->date_to);


        $timesheetAdjHdr = TimesheetAdjHdr::Create([
            'tran_date'  => Carbon::now(),
            'client_id'  => $request->client_id,
            'subcon_id'  => $request->subcon_id,
            'isvalid'  => 1,
            'isposted'  => 1,
            'datecreated'  => Carbon::now(),
            'createdby'  => 1,
        ]);

        if(count($adjust_data)>0){
            foreach($adjust_data as $data){
               $timesheet_data = $this->getPerTimesheet($data[0]);

               $getCheckDtl = $this->getCheckDtl($request->subcon_id, $request->client_id, $data[0]);

                if($data[0]!="undefined"){
                    if($getCheckDtl){
                        $timesheetAdjDtl = TimesheetAdjDtl::
                        updateOrCreate([
                        'id' => $getCheckDtl->id
                        ],
                        [
                            'adjusted_hours'  => $data[1],
                        ]);
                    }else{
                        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$data[0])) {
                            if(!empty($data[1])){
                                $timesheetAdjDtl = TimesheetAdjDtl::Create([
                                    'link_adj_hdr_id'  => $timesheetAdjHdr->id,
                                    'date'  => $data[0],
                                    'adjusted_hours'  => $data[1],
                                    'createdby'  => 1,
                                    'datecreated'  => Carbon::now(),
                                ]);
                            }

                        }else{
                            if(!empty($data[1])){

                                $id = $timesheetAdjHdr->id;
                                $timesheetAdjDtl = TimesheetAdjDtl::Create([
                                    'link_adj_hdr_id'  => $id,
                                    'date'  => $data[0],
                                    'adjusted_hours'  => $data[1],
                                    'createdby'  => 1,
                                    'datecreated'  => Carbon::now(),
                                ]);
                            }

                        }

                    }

                }

            }
        }



        return response()->json([
                    'success' => true,
                    'message' => 'Successfully adjusted.'
                ], 200);
	}



	public function getClients(Request $request)
    {
        $select_option = array();
        $clients = DB::connection('mysql2')->table("tblm_client")->whereNotNull('client_poc')->orderBy('client_poc', 'ASC')
            ->get();


        foreach($clients as $client){
            $select_option[] = array('id'=> $client->client_id_legacy,'client_poc'=> $client->client_poc." - ". $client->client_id_legacy);
         }


        return response()->json([
            'success' => true,
            'data' => $select_option,
        ], 200);


    }

    public function getStaff(Request $request)
    {
        $staff_info = array();

        $staff = DB::table("subcontractors")->where('leads_id','=',$request->client)->where('status','=','ACTIVE')->get();

        foreach($staff as $sub){

            $staff_data = DB::table("personal")->where('userid','=',$sub->userid)->first();

            if($staff_data){
                $staff_info[] = array('id' => $sub->userid,'complete_name' => $staff_data->fname.' '.$staff_data->lname);
            }

        }

        return response()->json([
            'success' => true,
            'data' => $staff_info,
        ], 200);
    }

    public function getTimeSheet(Request $request)
    {
        $timesheet_data = array();
        $return_data = array();
        $date_generated = array();


        $start_date = Carbon::parse($request->start_date);
        $end_date = Carbon::parse($request->end_date);
        $period = CarbonPeriod::create($start_date->format('Y-m-d'), $end_date->format('Y-m-d'));

        $get_data = $this->getTimeRecords($request->client_id, $request->subcon_id,$start_date->format('Y-m-d'),$end_date->format('Y-m-d'));

        foreach($get_data as $data){
            $timesheet_data[] = array('date_worked' => $data['date'],'work_time_in' => $data['time_in'],'work_time_out' => $data['time_out'],'lunch_start' => $data['lunch_start'],'lunch_end' => $data['lunch_end'],'lunch_hours' => $data['lunch_hours'],'actual_hours_worked' => $data['actual_hours_worked'],'adjusted_hours' => $data['adjusted_hours'],'reg_ros_hours' => $data['reg_ros_hours']);
        }

         return response()->json([
            'success' => true,
            'data' => $timesheet_data
        ], 200);

    }

    public function getHistory(Request $request)
    {
        $main_db = Config::get('database.connections');


        $timesheet_history = TimesheetAdjHdr::join($main_db['mysql']['database'].'.tblm_b_onboard_actreg_basic',$main_db['mysql']['database'].'.tblm_b_onboard_actreg_basic.reg_link_preregid','=','tblt_timesheet_adj_hdr.subcon_id')->with(['dtl'])
        ->orderBy('tran_date', 'desc')
        ->paginate($request->limit ? $request->limit : TimesheetAdjHdr::count());;


        return response()->json([
            'success' => true,
            'data' => $timesheet_history
        ], 200);

    }


    public function getPerTimesheet($id){
        $timesheet = DB::connection('mysql2')->table("tblt_timesheet_dtl")->where('id', $id)->first();
        return $timesheet;
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


    function getActualHours($start, $end){

        $time1 = strtotime($start);
        $time2 = strtotime($end);

        if(strtotime($start) > strtotime($end)){
            $time1 = strtotime($start);
            $time2 = strtotime($end);
        }


        $difference = round(abs($time2 - $time1) / 3600,2);
        return number_format($difference, 2);
    }

    function getTimeRecords($client_id, $subcon_id,$start_date,$end_date){

            $url = "https://portal.remotestaff.com.au/portal/exporting/rssc_time_records3.php?id=".$subcon_id."&client_id=".$client_id."&start_date=".$start_date."&end_date=".$end_date."";

            // echo $url; die();

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, TRUE);

            $data = curl_exec($curl);
            // $data = json_encode($data);
            $response_result = json_decode($data, false);

            curl_close($curl);

            $response = array();
            $return = array();
            $work_status = 'Full-Time';



            $dates = $this->getDatesFromRange($start_date, $end_date);

            foreach($dates as $date){
                if(!isset($response[$date])){
                    $response[$date] = array(
                        'time_in'=>array(),
                        'time_out'=>array(),
                        'lunch_start'=>array(),
                        'lunch_end'=>array()
                    );
                }
            }

            //if employee timesheet exist
            if(isset($response_result)){

                if(isset($response_result->rssc_time_records)){
                    foreach($response_result->rssc_time_records as $key => $value){
                        $rec_date = date('Y-m-d', strtotime($value->start));
                        $rec_date_start = date('Y-m-d H:i:s', strtotime($value->start));
                        $rec_date_end = date('Y-m-d H:i:s', strtotime($value->end));
                        $rec_type = $value->timerecord;

                        if(!isset($response[$rec_date])){
                            $response[$rec_date] = array(
                                'time_in'=>array(),
                                'time_out'=>array(),
                                'lunch_start'=>array(),
                                'lunch_end'=>array()
                            );
                        }


                        if($rec_type=='time record'){
                            $response[$rec_date]['time_in'][] = $rec_date_start;
                            $response[$rec_date]['time_out'][] = $rec_date_end;
                        }
                        else if($rec_type=='lunch record'){
                            $response[$rec_date]['lunch_start'][] = $rec_date_start;
                            $response[$rec_date]['lunch_end'][] = $rec_date_end;
                        }

                    }
                }
            }


            $user_info = $this->getUserInfo($subcon_id);

            $num=0;

            foreach($response as $date=>$value){
                $time_str = "";
                $time_out_str = "";
                $total_hrs = 0;

                if($user_info){
                    $roasted_hrs = $user_info->mon_number_hrs == 0 ? 8 : $user_info->mon_number_hrs;
                }else{
                    $roasted_hrs = 8;
                }



                $count = count($value['time_in']);

                for($i=0; $i<$count; $i++){

                    $t_in = $value['time_in'][$i];
                    $t_out = $value['time_out'][$i];
                    $time_str .= date('H:i', strtotime($t_in)) . '<br />';
                    $time_out_str .= date('H:i', strtotime($t_out)) . '<br />';

                    $total_hrs += $this->getActualHours($t_in, $t_out);
                }

                $time_out = end($value['time_out']);
                $lunch_start = current($value['lunch_start']);
                $lunch_end = end($value['lunch_end']);

                $login = $time_str;
                $time_out = $time_out_str;

                $actual_hours = number_format($total_hrs, 2);
                $lunch_hours = $this->getActualHours($lunch_start, $lunch_end);

                if($lunch_start)
                    $lunch_start = date('H:i', strtotime($lunch_start));

                if($lunch_end)
                    $lunch_end = date('H:i', strtotime($lunch_end));


                if($work_status == 'Full-Time'){
                    $adj_hours = '8.0';
                }
                else{
                    $adj_hours = '4.0';
                }

                if($actual_hours < $adj_hours){
                    $adj_hours = $actual_hours;
                }

              if($adjusted_hours=$this->getStoredAdjHrs($subcon_id, $client_id, $date)){
                  if($adj_hours){
                      $adj_hours = $adjusted_hours;
                    }

              }

                $total_actual_hrs = $actual_hours - $lunch_hours;

                $return[] = array(
                    'id' => $num++,
                    'date' => $date,
                    'time_in' => $login,
                    'time_out' => $time_out,
                    'lunch_start' => $lunch_start,
                    'lunch_end' => $lunch_end,
                    'actual_hours_worked' => number_format($total_actual_hrs, 2),
                    'adjusted_hours' => $adj_hours,
                    'lunch_hours' => $lunch_hours,
                    'reg_ros_hours' => $adj_hours==0 ? 0 : $roasted_hrs
                );
            }


            return $return;
    }

    function getStoredAdjHrs($staff_id, $client_id, $date){

        $main_db = Config::get('database.connections');
        $timesheet = DB::connection('mysql2')->table("tblt_timesheet_adj_hdr")
        ->join('tblt_timesheet_adj_dtl', 'tblt_timesheet_adj_dtl.link_adj_hdr_id', '=', 'tblt_timesheet_adj_hdr.id')
        ->where('tblt_timesheet_adj_hdr.subcon_id', '=', $staff_id)
        ->where('tblt_timesheet_adj_hdr.client_id', '=', $client_id)
        ->where('tblt_timesheet_adj_dtl.date', '=', $date)
        ->first();


        if($timesheet){
          return $timesheet->adjusted_hours;
        }


    }

    function getCheckDtl($staff_id, $client_id, $date){

        $main_db = Config::get('database.connections');
        $timesheet = DB::connection('mysql2')->table("tblt_timesheet_adj_hdr")
        ->join('tblt_timesheet_adj_dtl', 'tblt_timesheet_adj_dtl.link_adj_hdr_id', '=', 'tblt_timesheet_adj_hdr.id')
        ->where('tblt_timesheet_adj_hdr.subcon_id', '=', $staff_id)
        ->where('tblt_timesheet_adj_hdr.client_id', '=', $client_id)
        ->where('tblt_timesheet_adj_dtl.date', '=', $date)
        ->first();

        if($timesheet){
          return $timesheet;
        }
    }

    function getUserInfo($staff_id){

        $subcontractors = DB::table("tblm_client_sub_contractor")
        ->join('tblm_b_onboard_actreg_basic','tblm_client_sub_contractor.reg_link_preregid','=','tblm_b_onboard_actreg_basic.reg_id')
        ->where('tblm_b_onboard_actreg_basic.legacy_user_id', '=', $staff_id)
        ->first();

        if($subcontractors){
          return $subcontractors;
        }
    }

    public function getTimeSheetReport(Request $request)
    {
        $collection = collect();

        $timesheet_data = array();
        $return_data = array();
        $date_generated = array();


        $start_date = Carbon::parse($request->start_date);
        $end_date = Carbon::parse($request->end_date);
        $period = CarbonPeriod::create($start_date->format('Y-m-d'), $end_date->format('Y-m-d'));

        $client_id_legacy = 0;
        $client = Client::where('id', $request->client_id)->first();
        if($client) {
            $client_id_legacy = $client->client_id_legacy;
        }

        $legacy_user_id = 0;
        $registrant = OnboardProfileBasicInfo::where('reg_link_preregid', $request->subcon_id)->first();
        if($registrant) {
            $legacy_user_id = $registrant->legacy_user_id;
        }

        $get_data = $this->getTimeRecords($client_id_legacy, $legacy_user_id, $start_date->format('Y-m-d'),$end_date->format('Y-m-d'));

        $total_actual_work_hours = 0;
        $total_adjusted_hours = 0;
        $total_regular_work_hours = 0;

        foreach($get_data as $data){
            $timesheet_data = array(
                'date_worked' => $data['date'],
                'work_time_in' => $data['time_in'],
                'work_time_out' => $data['time_out'],
                'lunch_start' => $data['lunch_start'],
                'lunch_end' => $data['lunch_end'],
                'lunch_hours' => $data['lunch_hours'],
                'actual_hours_worked' => number_format($data['actual_hours_worked'], 2),
                'adjusted_hours' => number_format($data['adjusted_hours'], 2),
                'reg_ros_hours' =>number_format( $data['reg_ros_hours'], 2)
            );

            $total_actual_work_hours = number_format($total_actual_work_hours + $data['actual_hours_worked'], 2);
            $total_adjusted_hours = number_format($total_adjusted_hours + $data['adjusted_hours'], 2);
            $total_regular_work_hours = number_format($total_regular_work_hours + $data['reg_ros_hours'], 2);

            $collection->push($timesheet_data);
        }

        if( $collection->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'total_actual_work_hours' => $total_actual_work_hours,
            'total_adjusted_hours' => $total_adjusted_hours,
            'total_regular_work_hours' => $total_regular_work_hours,
            'data' => $collection->paginate($request->limit ? $request->limit : $collection->count()),
        ], 200);

    }


    function getTimeRecordsTotal($client_id, $subcon_id,$start_date,$end_date){

            $dates = $this->getDatesFromRange($start_date, $end_date);
            $total = 0;
            $counter = 0;

           
                foreach($dates as $date){
                    $adj_hrs = $this->getStoredAdjHrs($subcon_id, $client_id, $date);
                    $total += $adj_hrs;
                    $counter++;
                }

            
            return number_format($total, 2);
    }


    function getTimeRecordsTotalTest(Request $request){

            $mytable = DB::table('subcontractors')->where('id', '=', $request->subcon_id)->first();
      

            $dates = $this->getDatesFromRange($request->start_date, $request->end_date);
            $total = 0;
            $counter = 0;

            if($mytable){
                foreach($dates as $date){
                    $adj_hrs = $this->getStoredAdjHrs($mytable->userid, $request->client_id, $request->date);
                    $total += $adj_hrs;
                    $counter++;
                }

            }

            return number_format($total, 2);
    }


    


}

