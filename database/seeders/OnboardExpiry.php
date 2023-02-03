<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Users\Onboard;

class OnboardExpiry extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      
        for($i=1; $i<=4;$i++){
            DB::table('tblm_onboard_expiry')->insert([
                'effective_from' => '2022-12-31',
                'effective_to' => '2023-01-01',
                'max_days_expiry'  => $i,
            ]);
        }
    }
}
