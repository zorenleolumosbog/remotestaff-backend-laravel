<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;
use Illuminate\Support\Facades\DB;
use App\Models\Users\Onboard;
use App\Models\Users\OnboardExpiry;
use Carbon\Carbon;
use DateTime;


class UnverifiedExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unverified:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check unverified registrant';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
       
        Mail::raw('Cron Test Executed!', function($msg) {$msg->to('testexecute@yopmail.com')->subject('Test Email'); });

        $onboard_prereg = DB::table('tblm_a_onboard_prereg')->join('tblm_onboard_expiry','tblm_onboard_expiry.id','=','tblm_a_onboard_prereg.maxdays_rule_id')->whereNull('tblm_a_onboard_prereg.is_verified')->select('tblm_onboard_expiry.*','tblm_a_onboard_prereg.id as user_id','tblm_a_onboard_prereg.*')->get();
        $expired_emails = array();

        foreach($onboard_prereg as $prereg){

            if($prereg->is_active){
                $date_registered = Carbon::createFromFormat('Y-m-d H:i:s', $prereg->date_submitted);
                $expiry_date =  $date_registered->addDays($prereg->max_days_expiry);
                $ex_date = explode(" ",$expiry_date);
                
            
                $before_hour =  Carbon::parse($prereg->date_submitted);
                $current_date_time = Carbon::now()->toDateTimeString();
                
                $start_datetime = new DateTime($before_hour); 
                $diff = $start_datetime->diff(new DateTime($current_date_time));
                $diff = now()->diffInDays($prereg->date_submitted);

                $now_date = date('Y-m-d');

                if ($now_date >= $prereg->effective_from && $now_date <= $prereg->effective_to && $diff > 0 && $diff >= $prereg->max_days_expiry && empty($prereg->is_expired)){
                    echo "Expiry is executed";
                    Onboard::where('tblm_a_onboard_prereg.id', '=', $prereg->user_id)->update(array('is_expired' => 1,'date_expired' => $expiry_date));
                    Mail::send('email.emailExpiredRegistration', ['email' => $prereg->email], function($message) use($prereg){
                        $message->to($prereg->email);
                        $message->subject('Unverified registration - Remote Staff');
                   });
                   array_push($expired_emails,array('id'=> $prereg->id,'email'=> $prereg->email,'date_submitted'=> $prereg->date_submitted,'date_submitted'=> $prereg->date_submitted,'date_expired' => $expiry_date));
                }    
            }

        }

        if(count($expired_emails)>0){
            Mail::send('email.adminUnverified', ['email' => 'rodz.painagan@remotestaff.com','date' => Carbon::now()->toDateTimeString(),'expired_emails' => $expired_emails], function($message){
               $message->to(['corelogic1.jim@gmail.com','agility.marjims@gmail.com','rodz.painagan@remotestaff.com','tina.papango@remotestaff.com']);
               //$message->to('rp.interactive@gmail.com','rodz.painagan@remotestaff.com');
               $message->subject('Unverified registration - Remote Staff');
           });
        }

        return 0;
    }
}
