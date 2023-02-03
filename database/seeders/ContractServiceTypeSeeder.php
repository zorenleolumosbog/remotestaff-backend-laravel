<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContractServiceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $service_types = [
            ['description' => 'Backorder', 'datecreated' => now()],
            ['description' => 'ASL', 'datecreated' => now()],
            ['description' => 'Custom', 'datecreated' => now()],
        ];

        DB::table('tblm_contract_service_types')->insert($service_types);
    }
}
