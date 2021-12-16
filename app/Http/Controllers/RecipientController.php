<?php

namespace App\Http\Controllers;

use App\Models\Recipient;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use VK\Client\VKApiClient;

class RecipientController extends Controller
{
    private $vk;

    public function __construct()
    {
        $this->vk = new VKApiClient("5.131");
    }

    public function github(Request $request)
    {
        $event = $request->get("event");
        $payload = $request->get("payload");
        $pushed = $event === "push" && $payload["ref"] === "refs/heads/main";

        try {
            $this->vk->messages()->send(env("VK_TOKEN"), [
                "peer_id" => 242521347,
                "message" => "Got GH event! ${event}. Pushed? ${pushed}"
            ]);
        } catch (Exception $e) {
            dd($e);
        }

        if ($pushed) {
            exec("./vendor/bin/envoy run deploy >> /dev/null");
        }

        return response('Got it!', 200);
    }

    public function changeMessagingTime(Request $request)
    {
        $validator = validator($request->all(), ['time' => ['required', 'integer', 'min:1', 'max:12000']]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'time is required to be integer of range 1-12000'], 400);
        }
        if (Storage::exists('messaging_time.txt.disabled')) {
            Storage::put('messaging_time.txt.disabled', $request->get("time"));
        } else {
            Storage::put('messaging_time.txt', $request->get("time"));
        }

        return response()->json(['status' => 'ok']);
    }

    public function disableMessaging()
    {
        if (!Storage::exists('messaging_time.txt.disabled') && Storage::exists('messaging_time.txt')) {
            Storage::move('messaging_time.txt', 'messaging_time.txt.disabled');
        }

        return response()->json(['status' => 'ok']);
    }

    public function enableMessaging()
    {
        if (Storage::exists('messaging_time.txt.disabled')) {
            Storage::move('messaging_time.txt.disabled', 'messaging_time.txt');
        } else if (Storage::exists('messaging_time.txt')) {
            Storage::put('messaging_time.txt', '5');
        }

        return response()->json(['status' => 'ok']);
    }

    public function messagingList()
    {
        $list = Recipient::all();
        return response()->json(['status' => 'ok', 'count' => count($list), 'list' => $list]);
    }

    public function addToMessagingList(Request $request)
    {
        $validator = validator($request->all(), ['vk' => ['required', 'integer', 'min:1000']]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'vk should contain eligible vk id'], 400);
        }
        $created = Recipient::create(['vk' => $request->get('vk')]);

        return response()->json(['status' => 'ok', 'recipient' => $created]);
    }

    public function removeFromMessagingList(Request $request)
    {
        $validator = validator($request->all(), ['id' => ['required', 'integer', 'exists:recipients']]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'id is not exists in database or not entered'], 400);
        }
        $recipient = Recipient::whereId($request->get('id'))->delete();

        return response()->json(['status' => 'ok', 'recipient' => $recipient]);
    }
}
