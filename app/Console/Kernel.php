<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */

    protected $commands = [
        Commands\UnverifiedExpiry::class,
        Commands\JobseekerToRemoteWorker::class,
    ];


    protected function schedule(Schedule $schedule)
    {
        $schedule->command('unverified:daily')->dailyAt('07:00')->timezone('Asia/Manila');
        $schedule->command('jobseeker:remoteworker')->dailyAt('07:00')->timezone('Asia/Manila');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
