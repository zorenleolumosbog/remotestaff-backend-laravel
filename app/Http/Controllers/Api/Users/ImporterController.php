<?php

namespace App\Http\Controllers\Api\Users;

use App\Models\Users\User;
use App\Models\Users\Onboard;
use App\Models\Users\UserVerify;
use App\Models\Timesheet\TimesheetHdr;
use App\Models\Timesheet\TimesheetDtl;
use App\Models\Timesheet\TimesheetAdjDtl;
use App\Models\Timesheet\TimesheetAdjHdr;
use App\Models\Users\OnboardProfileBasicInfo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use DateTime;
use DateInterval;
use DatePeriod;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class ImporterController extends Controller
{
    
	public function get(){

         
    $mytable = DB::connection('mysql2')->table('ges_adjusted_one_three')
    ->where('subcon_id','>=',13152)
    ->where('subcon_id','<=',13905)
    // ->where('subcon_id','=',8423)
    //->orderBy('subcon_id','DESC')
    ->get();
    
    $counter = 0;
    $tot=0;
    $total_adj_hrs = array();


    foreach($mytable as $adjust){
        echo $adjust->subcon_id."<br/>";

        $mytable = DB::table('subcontractors')->where('id', '=', $adjust->subcon_id)->first();
        
        if($mytable){
            // $adjusted_hours = DB::connection('mysql2')->table('tblt_timesheet_adj_dtl')->select('tblt_timesheet_adj_hdr.client_id','tblt_timesheet_adj_hdr.subcon_id',DB::raw('SUM(tblt_timesheet_adj_dtl.adjusted_hours) as adj_hours'))->join('tblt_timesheet_adj_hdr','tblt_timesheet_adj_hdr.id','=','tblt_timesheet_adj_dtl.link_adj_hdr_id')
            // ->where([
            //     'tblt_timesheet_adj_hdr.client_id' => $adjust->leads_id,
            //     'tblt_timesheet_adj_hdr.subcon_id' => $mytable->userid
            //     ])

            // ->groupBy('client_id')->first(); 
            
              // echo $mytable->userid;
               //$timesheet = app('App\Http\Controllers\Api\Timesheet\TimesheetController')->getTimeRecordsTotal($adjust->leads_id, $mytable->userid,'2023-01-01','2023-01-31');
               $timesheet = app('App\Http\Controllers\Api\Timesheet\TimesheetController')->getTimeRecords($adjust->leads_id, $mytable->userid,'2023-01-16','2023-01-31');

               //if(!empty($timesheet)){


                if($timesheet){

                //echo $timesheet." -- Naa <br/>";

                        $tot_adj = 0;
                        $actual_hours_worked = 0;
                        foreach($timesheet as $index => $t_total){
                                $tot_adj += $t_total['adjusted_hours'];
                                $actual_hours_worked += $t_total['actual_hours_worked'];
                        }

                        // echo "Userid:".$mytable->userid." -- Client:".$adjust->leads_id." - ".$tot_adj."---".$actual_hours_worked."<br/><br/>";
                          $update = DB::connection('mysql2')->table('ges_adjusted_one_three')->where('subcon_id', $adjust->subcon_id)->where('leads_id', $adjust->leads_id)->update( [ 'total_adjusted_hours' =>  $tot_adj] );

                          echo $tot_adj;

                        $tot_adj=0;
                        $actual_hours_worked=0;

                    $counter++;

                }
            
        }
        
    }
        
        
    
     //$subcontractors = DB::table('subcontractors')->get();


      // $url = 'https://portal.remotestaff.com.au/portal/exporting/rssc_time_records3.php?id=74&client_id=11&start_date=2022-11-01&end_date=2022-12-02';
      // $ch = curl_init();
      // // Will return the response, if false it print the response
      // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      // // Set the url
      // curl_setopt($ch, CURLOPT_URL,$url);
      // // Execute
      // $result=curl_exec($ch);
      // // Closing
      // curl_close($ch);

      // // Will dump a beauty json :3
      // $json_result = json_decode($result, true);
      // echo "<pre>";
      // print_r($json_result); 
      // echo "</pre>"; 

      // if($json_result['status']=='ACTIVE'){
      //   $rssc_time_records = $json_result['rssc_time_records'];
      //       foreach($rssc_time_records as $record){
              
      //       }
      //  }

      // $timesheet_details = DB::connection('mysql2')->table("timesheet_details")->join('timesheet','timesheet.id','=','timesheet_details.timesheet_id')->select('timesheet.*','timesheet_details.id as time_id','timesheet_details.timesheet_id','timesheet_details.adj_hrs','timesheet_details.regular_rostered','timesheet_details.reference_date')->get();
      // $main_db = Config::get('database.connections');  

      // $counter = 0;
      // foreach($timesheet_details as $details){
      //   // echo "<pre>";
      //   // print_r($timesheet_details);
      //   // echo "</pre>";
      //   //echo $details->time_id.'\n';

        
      //   $timesheethdr = TimesheetHdr::where('legacy_timesheet_id','=',$details->timesheet_id)->first();
      //   $client = DB::connection('mysql2')->table("tblm_client")->where('client_id_legacy','=',$details->leads_id)->first();
      //   // $subcon = OnboardProfileBasicInfo::where('legacy_subcon_id','=',$details->userid)->first();

       
      //   if($client){
      //     $clientid = $client->id;
      //   }else{
      //     $clientid = 0;
      //   }

      //   // if($subcon){
      //   //   $subid = $subcon->legacy_subcon_id;
      //   // }else{
      //   //   $subid = 0;
      //   // }
        
      

      //   $counter++;
        
      //   $timesheetadjhdr = TimesheetAdjHdr::Create([
      //     'tran_date'  => $details->reference_date,
      //     'client_id'  => $details->leads_id,
      //     'subcon_id'  => $details->userid,
          
      //   ]);

      //   $timesheetadjdtl = TimesheetAdjDtl::Create([
      //     'link_adj_hdr_id'  => $timesheetadjhdr->id,
      //     'link_timesheet_dtl_id'  => $details->userid,
      //     'date'  => $details->reference_date,
      //     'adjusted_hours'  => $details->adj_hrs,
      //   ]);


      // $main_db = Config::get('database.connections'); 
        
    //   $get_data = $this->getTimeRecords(11, 74,'2022-12-01','2022-12-31');

    //     foreach($get_data as $data){
    //         $timesheet_data[] = array('date_worked' => $data['date'],'work_time_in' => $data['time_in'],'work_time_out' => $data['time_out'],'adjusted_hours' => $data['adjusted_hours']);
    //     }

   
        
      // $counter=0;
      // foreach($subcontractors as $sub){


      //      $get_data = $this->getTimeRecord($sub->leads_id, $sub->userid);
            
      //      foreach($get_data as $time){

      //            print_r($time);

      //      } 
          

            if($counter>10){
                die();
            }
            $counter++;

      // }
       

      

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

function utf8ize( $mixed ) {
    if (is_array($mixed)) {
        foreach ($mixed as $key => $value) {
            $mixed[$key] = utf8ize($value);
        }
    } elseif (is_string($mixed)) {
        return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
    }
    return $mixed;
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

function getTimeRecords($client_id, $subcon_id){

        $url = "https://portal.remotestaff.com.au/portal/exporting/rssc_time_records3.php?id=".$subcon_id."&client_id=".$client_id."&start_date=2022-11-01&end_date=2022-12-02";

        // echo $url; die();

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, TRUE);

        $data = curl_exec($curl);  
        // $data = json_encode($data);
        $obj = json_decode($data, false);

        curl_close($curl);

        $response = array();
        $return = array();
        $work_status = 'Full-Time';

        // if(isset($obj->work_status)){
            // $work_status = $obj->work_status;
        // }
        // else{
            // return json_encode(array('success'=>false));
            // die();
        // }

        $dates = $this->getDatesFromRange('2022-12-01', '2022-12-31');

        //once response is parsed, now finalize, get first timein and last timeout
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
        if($obj){
        
            foreach($obj->rssc_time_records as $key => $value){
                $__date = date('Y-m-d', strtotime($value->start));
                $__start = date('Y-m-d H:i:s', strtotime($value->start));
                $__end = date('Y-m-d H:i:s', strtotime($value->end));
                $__type = $value->timerecord;

                if(!isset($response[$__date])){
                    $response[$__date] = array(
                        'time_in'=>array(),
                        'time_out'=>array(),
                        'lunch_start'=>array(),
                        'lunch_end'=>array()
                    );
                }

                //array time in since its possible to have multiple timein so get the first time in and last login
                if($__type=='time record'){
                    $response[$__date]['time_in'][] = $__start;
                    $response[$__date]['time_out'][] = $__end;
                }
                else if($__type=='lunch record'){
                    $response[$__date]['lunch_start'][] = $__start;
                    $response[$__date]['lunch_end'][] = $__end;
                }
                
            }

        }



        //$user_info = getUserInfo($staff_id);
        $__i=0;


        // var_dump($dates);
        // die();

        foreach($response as $date=>$value){
            $time_str = "";
            $time_out_str = "";
            $total_hrs = 0;
            $__rrh = '';

            $_c = count($value['time_in']);

            for($i=0; $i<$_c; $i++){
                
                $t_in = $value['time_in'][$i];
                $t_out = $value['time_out'][$i];
                $time_str .= date('H:i', strtotime($t_in)) . '<br />';
                $time_out_str .= date('H:i', strtotime($t_out)) . '<br />';

                $total_hrs += $this->getActualHours($t_in, $t_out);
            }

            // $total_hrs = $obj->total_log_hours;

            // $__first_login = current($value['time_in']);
            $__last_logout = end($value['time_out']);
            $__first_lunch = current($value['lunch_start']);
            $__last_lunch = end($value['lunch_end']);

            $__first_login = $time_str;
            $__last_logout = $time_out_str;

            // $__actual_hours = getActualHours($__first_login, $__last_logout);
            // $__lunch_hours = getActualHours($__first_lunch, $__last_lunch);

            $__actual_hours = number_format($total_hrs, 2);
            $__lunch_hours = $this->getActualHours($__first_lunch, $__last_lunch);

            if($__first_lunch)
                $__first_lunch = date('H:i', strtotime($__first_lunch)); 
            
            if($__last_lunch)
                $__last_lunch = date('H:i', strtotime($__last_lunch));

            //get leave request
            $__leave_notes = "";
            // if($leave=getLeaveRequest($staff_id, $date)){
            //     $__leave_notes = $leave['reason_for_leave'];
            // }
            
            if($work_status == 'Full-Time'){
                $__adj_hours = '8.0';
            }
            else{
                $__adj_hours = '4.0';
            }

            if($__actual_hours < $__adj_hours){
                $__adj_hours = $__actual_hours;
            }

          if($adj_hours=$this->getStoredAdjHrs($subcon_id, $client_id, $date)){
              if($adj_hours){
                  $__adj_hours = $adj_hours;
                }
              
          }

            $return[] = array(
                'id' => $__i++,
                'date' => $date,
                'time_in' => $__first_login,
                'time_out' => $__last_logout,
                'lunch_start' => $__first_lunch,
                'lunch_end' => $__last_lunch,
                'actual_hours_worked' => $__actual_hours,
                'adjusted_hours' => $__adj_hours,
                'lunch_hours' => $__lunch_hours,
                'rrh' => '',
                'leave_notes' => $__leave_notes,
            );
        }


        return $return;
}

function getStoredAdjHrs($staff_id, $client_id, $date){

  $main_db = Config::get('database.connections');
  $timesheet = DB::connection('mysql2')->table("tblt_timesheet_adj_hdr")
  ->join('tblt_timesheet_adj_dtl','tblt_timesheet_adj_dtl.link_adj_hdr_id','=','tblt_timesheet_adj_hdr.id')
  ->where('tblt_timesheet_adj_dtl.date', '=', $date)
  ->first();


  if($timesheet){
    return $timesheet->adjusted_hours;
  }
  
  
}    

		

}
