<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $clients = [
            ['legacy_id' => '15906', 'name' => 'ActivTec Solutions'],
            ['legacy_id' => '27694', 'name' => 'Douglas Reith'],
            ['legacy_id' => '11058', 'name' => 'Amanda Cunington'],
            ['legacy_id' => '26787', 'name' => 'Andrew Gagner'],
            ['legacy_id' => '7890', 'name' => 'AppsWiz .com'],
            ['legacy_id' => '26612', 'name' => 'Baxta Pets'],
            ['legacy_id' => '24787', 'name' => 'Ben Wymer'],
            ['legacy_id' => '24005', 'name' => 'Brendan Culver'],
            ['legacy_id' => '26367', 'name' => 'Brooke Mottram'],
            ['legacy_id' => '14697', 'name' => "Claire O'Connor"],
            ['legacy_id' => '17529', 'name' => 'Colin Williams'],
            ['legacy_id' => '25832', 'name' => 'Cormac Gray'],
            ['legacy_id' => '27506', 'name' => 'Danny Jang - Ecommerce and Sales Department'],
            ['legacy_id' => '11953', 'name' => 'Doug Voss'],
            ['legacy_id' => '21241', 'name' => 'Emil Pangilinan'],
            ['legacy_id' => '9383', 'name' => 'Focus (NSW) Pty Ltd'],
            ['legacy_id' => '25335', 'name' => 'Gab Kendrick'],
            ['legacy_id' => '9587', 'name' => 'Ian Hill'],
            ['legacy_id' => '27555', 'name' => 'James Hill'],
            ['legacy_id' => '26228', 'name' => "Jim's Group Finance"]
        ];

        foreach ($clients as $client) { 
            DB::connection('mysql2')->table('tblm_client')->insert([
               'client_id_legacy'  => $client['legacy_id'],
               'client_name'  => $client['name'],
           ]);
        }
    }
}
