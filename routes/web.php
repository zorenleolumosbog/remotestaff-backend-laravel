<?php

use App\Http\Controllers\Api\SocialAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Client\ScreenCaptureController;
use App\Http\Controllers\Api\Client\ActivityNotesController;
use App\Http\Controllers\Api\Client\SubcontractorController as ClientSubcontractorController;
use App\Http\Controllers\Api\Users\LoginController;
use App\Http\Controllers\Api\Timesheet\TimesheetController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/social-auth/{registranttype}/{provider}', [SocialAuthController::class, 'redirect']);
Route::get('/social-auth/{registranttype}/{provider}/callback', [SocialAuthController::class, 'handleCallback']);


Route::get('/get-client-lists', [LoginController::class, 'getClientList']);
Route::get('/get-total-records', [TimesheetController::class, 'getTimeRecordsTotalTest']);




