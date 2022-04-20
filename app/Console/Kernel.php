<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //$schedule->command('update:leads')->everyTenMinutes();
        //$schedule->command('account:sending')->dailyAt('09:00');
        $schedule->command('account-sending:aladra')->dailyAt('09:00');
        $schedule->command('account-sending:anuva')->dailyAt('09:00');
        $schedule->command('account-sending:brasilia')->dailyAt('09:00');
        $schedule->command('update:report-leads')->dailyAt('10:22');
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
