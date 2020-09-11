<?php

namespace App\Console;

use App\Console\Commands\UpdateOnlyShopifyFileCommand;
use App\Console\Commands\UpdateOnlyStockFileCommand;
use App\Console\Commands\UpdateStockAndShopifyFIlesCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [

        UpdateStockAndShopifyFIlesCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('updateStockShopifyFiles:run')
            ->cron('0 */2 * * *');

//        $schedule->command('updateShopifyProductsFile:run')
//            ->dailyAt('21:00');
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
