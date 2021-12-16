<?php

use App\Http\Controllers\RecipientController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post("/github", [RecipientController::class, 'github']);
Route::get("/change", [RecipientController::class, 'changeMessagingTime']);
Route::get("/disable", [RecipientController::class, 'disableMessaging']);
Route::get("/enable", [RecipientController::class, 'enableMessaging']);
Route::get("/list", [RecipientController::class, 'messagingList']);
Route::get("/add", [RecipientController::class, 'addToMessagingList']);
Route::get("/remove", [RecipientController::class, 'removeFromMessagingList']);
