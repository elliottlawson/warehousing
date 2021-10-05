<?php

namespace App\Console;

use App\Console\Commands\DefaultInventoryTypesCommand;
use App\Console\Commands\DefaultLocationTypesCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     * @var array
     */
    protected $commands = [
        //
        DefaultLocationTypesCommand::class,
        DefaultInventoryTypesCommand::class,
    ];

    /**
     * Define the application's command schedule.
     * @param  Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
