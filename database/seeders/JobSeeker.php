<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Users\Onboard;
use Carbon\Carbon;

class JobSeeker extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        $email = "remotestaff_test_";
        for($i=0; $i<=29;$i++){
             //create Onboarding user
            $onboard = Onboard::create([
                'email'     => $email.$i.'@yopmail.com',
                'password'  => bcrypt('remotestaff123'),
                'email_passwd_conf'  => bcrypt('remotestaff123'),
                'date_submitted'  => Carbon::now(),
                'ip_addr'  => '127.0.1.1',
                'maxdays_rule_id' => 2 
            ]);
        }

        $email2 = "remotestaff_test2_";
        for($i=0; $i<=9;$i++){
             //create Onboarding user
            $onboard = Onboard::create([
                'email'     => $email2.$i,
                'password'  => bcrypt('remotestaff123'),
                'email_passwd_conf'  => bcrypt('remotestaff123'),
                'date_submitted'  => Carbon::now(),
                'ip_addr'  => '127.0.1.1',
                'maxdays_rule_id' => 1 
            ]);
        }


        DB::table('tblm_onboard_expiry')->insert([
            'effective_from' => '2022-08-31',
            'effective_to' => '2022-12-31',
            'max_days_expiry' => 1,
            'datecreated' => Carbon::now(),
            'createdby' => 1,
            'is_active' => 1
            
        ]);

        DB::table('tblm_onboard_expiry')->insert([
            'effective_from' => '2022-08-31',
            'effective_to' => '2022-12-31',
            'max_days_expiry' => 2,
            'datecreated' => Carbon::now(),
            'createdby' => 1,
            'is_active' => 1
            
        ]);
       

    }
}
