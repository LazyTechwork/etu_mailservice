<?php

namespace App\Http\Controllers;

use App\Helpers\Messaging;
use App\Models\Recipient;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Process\Process;
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
        $event = $request->header("X-GitHub-Event");
        $ref = $request->get("ref");
        $pushed = $event === "push" && $ref === "refs/heads/master";
        $githubHash = $request->header('X-Hub-Signature');
        $localToken = config('app.deploy_secret');
        $githubPayload = $request->getContent();
        $localHash = 'sha1=' . hash_hmac('sha1', $githubPayload, $localToken, false);
        if (!hash_equals($githubHash, $localHash)) {
            return response()->json(['status' => 'error', 'exception' => 'Cannot validate secret']);
        }
        try {
            $this->vk->messages()->send(Config::get("app.vk_token"), [
                "peer_id"   => 242521347,
                "message"   => "Got GH event! $event. Pushed? " . ($pushed ? "yes" : "no"),
                "random_id" => random_int(0, PHP_INT_MAX)
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'exception' => $e->getMessage()]);
        }

        $output = [];
        if ($pushed) {
            $process = new Process(["./deploy.sh"], base_path());
            $process->run(function ($type, $buffer) use (&$output) {
                $output[] = $buffer;
            });
        }

        return response()->json(['status' => 'ok', 'output' => $output]);
    }

    public function changeMessagingTime(Request $request)
    {
        $validator = validator($request->all(), ['time' => ['required', 'integer', 'min:1', 'max:12000']]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'time is required to be integer of range 1-12000'], 400);
        }
        try {
            Messaging::update(null, (int)$request->get("time"));
        } catch (\JsonException $_) {
            return response()->json(['status' => 'error', 'message' => 'Cannot write to JSON'], 500);
        }
        return response()->json(['status' => 'ok']);
    }

    public function disableMessaging()
    {
        try {
            Messaging::update(null, null, true);
        } catch (\JsonException $_) {
            return response()->json(['status' => 'error', 'message' => 'Cannot write to JSON'], 500);
        }
        return response()->json(['status' => 'ok']);
    }

    public function enableMessaging()
    {
        try {
            Messaging::update(Carbon::now()->timestamp + 60 * Messaging::get()['interval'], null, false);
        } catch (\JsonException $_) {
            return response()->json(['status' => 'error', 'message' => 'Cannot write to JSON'], 500);
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

    public function status()
    {
        return response()->json(Messaging::get());
    }
}
