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
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

    /**
     *
     * Command for getting files and convert to json
     * 
     */

        // $schedule->command('command:TenantSync')
        //     ->withoutOverlapping()
        //     ->runInBackground();

    /**
     *
     * Command for 7 day old file delete
     * 
     */
        // $schedule->command('command:delete_files')
        //     ->withoutOverlapping()
        //     ->runInBackground()
        //     ->timezone('Asia/Manila')
        //     ->dailyAt('13:00');
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
