<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientSubconSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $client_ids = [1,1,1,2,2,2,3,3,3,3,3,4,4,4,5,5,5,5,6,6,6,7,7,7,7,7,8,8,8,8,9,9,9,9,10,10,10,
                        11,11,11,12,12,12,13,13,13,14,14,14,15,15,15,16,16,16,17,17,17,18,18,18,19,19,19,20,20,20,20];
        
        $subcon_id = 1;
        foreach ($client_ids as $client_id) { 
            DB::connection('mysql2')->table('tblm_client_subcon_pers')->insert([
                'link_subcon_id'  => $subcon_id,
                'link_client_id' => $client_id
            ]);

            $subcon_id += 1;
        }
    }
}
