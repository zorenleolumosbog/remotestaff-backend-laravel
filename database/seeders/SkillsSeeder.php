<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SkillsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $skills = array('PHP','Python','NodeJs','Vue','Linux');

        foreach($skills as $skill){
            DB::table('tblm_skills')->insert([
                'desc' => $skill,
                'datecreated' => Carbon::now()
            ]);
        }


        $skill_level = array('Basic','Intermediate','Advance');

        foreach($skill_level as $level){
            DB::table('tblm_skill_level')->insert([
                'desc' => $level,
                'datecreated' => Carbon::now()
            ]);
        }

        

    }
}
