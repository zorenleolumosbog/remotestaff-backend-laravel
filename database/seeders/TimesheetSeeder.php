<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimesheetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create timesheet header
        $headers = [
            ['client_id' => '1', 'subcon_id' => '1'],
            ['client_id' => '1', 'subcon_id' => '2'],
            ['client_id' => '2', 'subcon_id' => '4'],
            ['client_id' => '3', 'subcon_id' => '7'],
            ['client_id' => '4', 'subcon_id' => '12'],
            ['client_id' => '5', 'subcon_id' => '15'],
        ];

        foreach ($headers as $header) { 
            DB::connection('mysql2')->table('tblt_timesheet_hdr')->insert([
               'link_subcon_id'  => $header['subcon_id'],
                'link_client_id'  => $header['client_id'],
                'status_id' => '1',
                'work_total_hours' => 80
           ]);
        }

        
        #### Create timesheet detail ####

        $timesheet_hdr = 1;
        foreach ($headers as $header) {
            $date = date_create("2022-11-1");

            for($i=1; $i<=30;$i++){ 
                DB::connection('mysql2')->table('tblt_timesheet_dtl')->insert([
                    'link_tms_hdr'  => $timesheet_hdr,
                    'date_worked'  => $date,
                    'work_time_in' => '8:00:00',
                    'work_time_out' => '16:00:00',
                    'work_total_hours' => 8
                ]);

                date_add($date, date_interval_create_from_date_string("1 day"));
            }

            $timesheet_hdr += 1;
        }
    }
}
