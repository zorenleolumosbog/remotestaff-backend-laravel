<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalaryType extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $salaryType = [
            ['id' => '1', 'desc' => 'hourly'],
            ['id' => '2', 'desc' => 'daily'],
            ['id' => '3', 'desc' => 'weekly'],
            ['id' => '4', 'desc' => 'monthly'],
        ];

        foreach ($salaryType as $salary) { 
            DB::connection('mysql2')->table('tblm_salary_type')->insert([
                'id'  => $salary['id'],
                'desc'  => $salary['desc'],
                'datecreated'  => Carbon::now()
            ]);
        }
    }
}
