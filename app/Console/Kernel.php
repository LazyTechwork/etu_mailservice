<?php

namespace App\Console;

use App\Helpers\Messaging;
use App\Models\Recipient;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Config;
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

            $data = Messaging::get();

            $next = $data["next"];
            $interval = $data["interval"];
            if ($interval <= 0 || $next - 15 > Carbon::now()->timestamp) {
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
            Messaging::update(Carbon::now()->timestamp + $interval * 60);
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
