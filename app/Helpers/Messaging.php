<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class Messaging
{

    /**
     * @throws \JsonException
     */
    public static function update($next = null, $interval = null, $disabled = null): void
    {
        $file = "messaging.json";
        $data = ["next" => 0, "interval" => 1, "disabled" => true];
        if (Storage::exists($file)) {
            $data = json_decode(Storage::get($file), true, 512, JSON_THROW_ON_ERROR);
        }
        if ($next !== null) {
            $data["next"] = $next;
        }
        if ($interval !== null) {
            $data["interval"] = $interval;
        }
        if ($disabled !== null) {
            $data["disabled"] = $disabled;
        }

        Storage::put($file, json_encode($data, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws \JsonException
     */
    public static function get()
    {
        $file = "messaging.json";
        $data = ["next" => 0, "interval" => 1, "disabled" => true];
        if (Storage::exists($file)) {
            $data = json_decode(Storage::get($file), true, 512, JSON_THROW_ON_ERROR);
        } else {
            Storage::put($file, json_encode($data, JSON_THROW_ON_ERROR));
        }
        return $data;
    }
}
