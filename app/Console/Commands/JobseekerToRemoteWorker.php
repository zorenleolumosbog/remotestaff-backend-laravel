<?php

namespace App\Console\Commands;

use App\Models\Users\OnboardProfileBasicInfo;
use Illuminate\Console\Command;

class JobseekerToRemoteWorker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobseeker:remoteworker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert Jobseeker to Remote Worker';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // OnboardProfileBasicInfo::
        // where('registrant_type', 1)
        // ->update([
        //     'registrant_type' => 2
        // ]);
    }
}
