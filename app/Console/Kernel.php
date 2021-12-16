<?php

namespace App\Console;

use App\Models\Recipient;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use VK\Client\VKApiClient;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $vk = new VKApiClient("5.131");
            $message = "Проверка отправки сообщений";
            if (Storage::exists("last_send.txt") && Storage::exists("messaging_time.txt")) {
                $last_send = (int)Storage::get("last_send.txt");
                $messaging = (int)Storage::get("messaging_time.txt");
                if ($messaging <= 0 || $last_send + $messaging * 60 - 15 < Carbon::now()->timestamp) {
                    return;
                }
            } else {
                return;
            }
            $list = Recipient::all();
            $list = $list->chunk(100);
            foreach ($list as $item) {
                $vk->messages()->send(Config::get("app.vk_token"), [
                    'ids'       => array_map(static function ($i) { return $i['vk']; }, $item),
                    'message'   => $message,
                    'random_id' => random_int(0, PHP_INT_MAX)
                ]);
                sleep(2);
            }
        })->everyMinute()->name('messaging')->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
