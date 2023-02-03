<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContractStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses = [
            ['description' => 'Active', 'datecreated' => now()],
            ['description' => 'Suspended', 'datecreated' => now()],
            ['description' => 'Resigned', 'datecreated' => now()],
            ['description' => 'Terminated', 'datecreated' => now()],
            ['description' => 'Invalid', 'datecreated' => now()],
        ];

        DB::table('tblm_contract_status')->insert($statuses);
    }
}
