<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($client=1; $client<=20; $client++) { 
            DB::connection('mysql2')->table('tblm_client_basic_rate')->insert([
               'link_client_id'  => $client,
               'basic_hourly_rate'  => 200,
           ]);
        }
    }
}
