<?php

namespace Database\Seeders;

use App\Models\Admin\CivilStatus;
use App\Models\Admin\Country;
use App\Models\Admin\Industry;
use App\Models\Admin\InvoiceItemType;
use App\Models\Admin\JobSource;
use App\Models\Admin\RegistrantType;
use App\Models\Admin\Religion;
use App\Models\Admin\Skill;
use App\Models\Admin\SkillLevel;
use App\Models\Admin\SocialMedia;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterfileSeeder extends Seeder
{
    private function seedRegistrantType() {
        RegistrantType::truncate();

        $data = [
            'Jobseeker',
            'Remote Worker',
            'Corporate Apps User',
            'Client / Manager'
        ];

        foreach ($data as $datum) {
            RegistrantType::create([
                'description' => $datum,
                'createdby' => 1,
                'datecreated' => Carbon::now()
            ]);
        }

        return true;
    }

    private function seedSocialMedia() {
        SocialMedia::truncate();

        $data = [
            'Twitter',
            'Facebook',
            'Yahoo',
            'Google',
            'Instagram',
            'Linkedin',
            'Apple'
        ];

        foreach ($data as $datum) {
            SocialMedia::create([
                'description' => $datum,
                'createdby' => 1,
                'datecreated' => Carbon::now()
            ]);
        }

        return true;
    }

    private function seedJobsource() {
        JobSource::truncate();

        $data = [
            'Google.com',
            'Bing.com',
            'Facebook.com',
            'Yahoo.com',
        ];

        foreach ($data as $datum) {
            JobSource::create([
                'description' => $datum,
                'createdby' => 1,
                'datecreated' => Carbon::now()
            ]);
        }

        return true;
    }

    private function seedCivilStatus() {
        CivilStatus::truncate();

        $data = [
            'Single',
            'Married',
            'Divorced',
            'Separated',
            'Widowed'
        ];

        foreach ($data as $datum) {
            CivilStatus::create([
                'description' => $datum,
                'createdby' => 1,
                'datecreated' => Carbon::now()
            ]);
        }

        return true;
    }

    private function seedReligion() {
        Religion::truncate();

        $data = [
            'Judaism',
            'Christianity',
            'Islam',
            'Hinduism',
            'Buddhism'
        ];

        foreach ($data as $datum) {
            Religion::create([
                'description' => $datum,
                'createdby' => 1,
                'datecreated' => Carbon::now()
            ]);
        }

        return true;
    }

    private function seedIndustry() {
        Industry::truncate();

        $data = [
            'Agriculture',
            'Computer and technology',
            'Construction',
            'Education',
            'Energy',
            'Entertainment',
            'Fashion',
            'Finance and Economic',
            'Food and Beverage',
            'Health Care',
            'Hospitality',
            'Manufacturing',
            'Media and News',
            'Mining',
            'Pharmaceutical',
            'Telecommunication',
            'Transportation'
        ];

        foreach ($data as $datum) {
            Industry::create([
                'description' => $datum,
                'createdby' => 1,
                'datecreated' => Carbon::now()
            ]);
        }

        return true;
    }

    private function seedSkill() {
        Skill::truncate();

        $data = [
            'Asking questions',
            'Note-taking',
            'Organization',
            'Punctuality',
            'Verbal/nonverbal communication',
            'Active listening',
            'Constructive criticism',
            'Interpersonal communication',
            'Public speaking',
            'Verbal/nonverbal communication',
            'Written communication',
            'Typing/word processing',
            'Fluency in coding languages',
            'Data science and analysis',
            'Systems administration',
            'Database management',
            'Graphics and design',
            'Spreadsheets',
            'Email management',
            'Active listening',
            'Empathy',
            'Interpersonal skills',
            'Problem-solving',
            'Reliability',
            'Communication',
            'Empathy',
            'Flexibility',
            'Leadership',
            'Patience',
            'Ability to teach and mentor',
            'Flexibility',
            'Risk-taking',
            'Team building',
            'Time management',
            'Decision-making',
            'Project planning',
            'Task delegation',
            'Team communication',
            'Team leadership',
            'Attention to detail',
            'Collaboration',
            'Communication',
            'Research',
            'Delegating tasks',
            'Focus',
            'Goal setting',
            'Organization',
            'Prioritization',
            'Ambition',
            'Creativity',
            'Empathy',
            'Leadership',
            'Teamwork'
        ];

        foreach ($data as $datum) {
            Skill::create([
                'desc' => $datum,
                'createdby' => 1,
                'datecreated' => Carbon::now()
            ]);
        }

        return true;
    }

    private function seedSkillLevel() {
        SkillLevel::truncate();

        $data = [
            'Basic',
            'Intermediate',
            'Advance'
        ];

        foreach ($data as $datum) {
            SkillLevel::create([
                'desc' => $datum,
                'createdby' => 1,
                'datecreated' => Carbon::now()
            ]);
        }

        return true;
    }

    private function seedInvoiceItemType() {
        InvoiceItemType::truncate();

        $data = [
            'Regular Work Hours',
            'Commission',
            'Service Fee',
            'Currency Adjustment',
            'Paid Leave'
        ];

        foreach ($data as $datum) {
            InvoiceItemType::create([
                'description' => $datum,
                'createdby' => 1,
                'datecreated' => Carbon::now()
            ]);
        }

        return true;
    }

    private function seedCountry() {
        $curl_session = curl_init();
        curl_setopt($curl_session, CURLOPT_URL, 'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/countries%2Bstates%2Bcities.json');
        curl_setopt($curl_session, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);

        $data = json_decode(curl_exec($curl_session));
        curl_close($curl_session);

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('tblm_country')->truncate();
        DB::table('tblm_region')->truncate();
        DB::table('tblm_state')->truncate();
        DB::table('tblm_towncity')->truncate();
        DB::table('tblm_barangay')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // $countries = ['Australia', 'United Kingdom', 'United States']; //Include only
        $countries = ['Philippines'];//Exclude only
        foreach ($data as $datum) {
            // if (in_array($datum->name, $countries)) { //Include only
            if (!in_array($datum->name, $countries)) { //Exclude only
                $country_code = $datum->iso2;
                $country = Country::create([
                    'short_desc' => $datum->iso2,
                    'long_desc' => $datum->name,
                    'createdby' => 1,
                    'datecreated' => Carbon::now()
                ]);

                foreach ($datum->states as $datum) {
                    $state = $country->states()->updateOrCreate(
                        [
                            'description' => $datum->name
                        ],
                        [
                            'createdby' => 1,
                            'datecreated' => Carbon::now()
                        ]
                    );
                    foreach ($datum->cities as $datum) {
                        $zipcode = DB::table('zipcodes')
                        ->where('country', $country_code)
                        ->where('city', 'LIKE', "{$datum->name}")
                        ->first();

                        $state->towncities()->updateOrCreate(
                            [
                                'description' => $datum->name,
                            ],
                            [
                                'zip_code' => $zipcode ? $zipcode->postal_code : 0,
                                'createdby' => 1,
                                'datecreated' => Carbon::now()
                            ]
                        );
                    }
                }
            }
        }
    }

    private function seedPhilippine() {
        $curl_session = curl_init();
        curl_setopt($curl_session, CURLOPT_URL, 'https://raw.githubusercontent.com/flores-jacob/philippine-regions-provinces-cities-municipalities-barangays/master/philippine_provinces_cities_municipalities_and_barangays_2019v2.json');
        curl_setopt($curl_session, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);

        $data = json_decode(curl_exec($curl_session));
        curl_close($curl_session);

        $country = Country::create([
            'short_desc' => 'PH',
            'long_desc' => 'Philippines',
            'with_region' => 1,
            'createdby' => 1,
            'datecreated' => Carbon::now()
        ]);

        foreach ($data as $datum) {
            $region = $country->regions()->create([
                'description' => str_replace("REGION", '', $datum->region_name),
                'createdby' => 1,
                'datecreated' => Carbon::now()
            ]);

            foreach ($datum->province_list as $key => $datum) {
                $state = $region->states()->create([
                    'description' => ucwords(strtolower($key)),
                    'createdby' => 1,
                    'datecreated' => Carbon::now()
                ]);

                foreach ($datum->municipality_list as $key => $datum) {
                    $municipality = ucwords(strtolower($key));
                    $zipcode = DB::table('zipcodes')
                        ->where('country', 'PH')
                        ->where('city', 'LIKE', "{$municipality}")
                        ->first();

                    $towncity = $state->towncities()->create([
                        'description' => $municipality,
                        'zip_code' => $zipcode ? $zipcode->postal_code : 0,
                        'createdby' => 1,
                        'datecreated' => Carbon::now()
                    ]);

                    foreach ($datum->barangay_list as $datum) {
                        $towncity->barangays()->create([
                            'description' => ucwords(strtolower($datum)),
                            'createdby' => 1,
                            'datecreated' => Carbon::now()
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->seedRegistrantType();
        // $this->seedSocialMedia();
        // $this->seedJobsource();
        // $this->seedCivilStatus();
        // $this->seedReligion();
        // $this->seedIndustry();
        // $this->seedSkill();
        // $this->seedSkillLevel();
        // $this->seedInvoiceItemType();
        // $this->seedCountry();
        // $this->seedPhilippine();
    }
}
