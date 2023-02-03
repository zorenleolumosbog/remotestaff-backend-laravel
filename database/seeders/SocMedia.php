<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SocMedia extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tblm_social_media')->insert([
            'description' => 'Facebook',
            'datecreated' => Carbon::now()
        ]);
        
        DB::table('tblm_social_media')->insert([
            'description' => 'Linkedin',
            'datecreated' => Carbon::now()
        ]);

        DB::table('tblm_social_media')->insert([
            'description' => 'Twitter',
            'datecreated' => Carbon::now()
        ]);

        DB::table('tblm_social_media')->insert([
            'description' => 'Instagram',
            'datecreated' => Carbon::now()
        ]);
    }
}
