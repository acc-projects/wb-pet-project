<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;

class Handler
{
    public function __invoke(Schedule $schedule): void
    {
        $scheduleTimes = explode(',', env('WB_SCHEDULE_TIME', '9,17'));
        $schedule->command('wb:fetch-all --limit=500'
            . ' --dateFrom=' .env('WB_DATE_FROM', '2025-11-01')
            . ' --dateTo=' .env('WB_DATE_TO', '2025-11-03')
            . ' -v'
            )
            ->twiceDaily($scheduleTimes[0], $scheduleTimes[1])
            ->description('Daily WB data sync')
            ->withoutOverlapping(30) // Защита от параллельного выполнения
            ->appendOutputTo(storage_path('logs/wb-scheduler.log'));

    }
}
