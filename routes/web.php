<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ChatController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::post('/upload-pdf', [DocumentController::class, 'upload']);
Route::post('/chat/{document}', [ChatController::class, 'ask']);
Route::get('/chatbot', function () {
    return view('chatbot');
});


Route::get('/test-log', function () {
    logger()->info('🔥 Test log from route!');
    return 'Done';
});

Route::get('/widget', function () {
    $botId = request('bot_id'); // نجيب الـ bot_id من الكويري
    return view('widget', compact('botId'));
});
