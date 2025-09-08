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

        // Schedule database backup
        $schedule->command('backup:database')
            ->daily()
            ->at('00:00')
            ->appendOutputTo(storage_path('logs/backup.log'));

        // Schedule files backup
        $schedule->command('backup:files')
            ->weekly()
            ->sundays()
            ->at('00:00')
            ->appendOutputTo(storage_path('logs/backup.log'));
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

    protected $commands = [
        // ... existing commands ...
        \App\Console\Commands\ClearCache::class,
    ];
}
