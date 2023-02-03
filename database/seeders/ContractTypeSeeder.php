<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContractTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            ['description' => 'Standard', 'datecreated' => now()],
            ['description' => 'Trial', 'datecreated' => now()],
            ['description' => 'Project Based', 'datecreated' => now()],
            ['description' => 'Direct Hire', 'datecreated' => now()],
        ];

        DB::table('tblm_contract_type')->insert($types);
    }
}
