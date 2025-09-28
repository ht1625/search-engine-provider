<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Defined artisan commands.
     *
     * @var array
     */
    protected $commands = [];

    /**
     * The application's command scheduler definition.
     */
    protected function schedule(Schedule $schedule)
    {
        // This is the place to define your command schedule. 
        // But no work is done here, just the schedule is defined.
        // $schedule->command('app:fetch-provider-data')->cron('*/3 * * * *');
    }

    /**
     * Where scripts are saved.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}