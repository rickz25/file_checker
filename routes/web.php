<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckerController;



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
Route::get('/', [CheckerController::class, 'index'])->name('index');
Route::post('check-file', [CheckerController::class, 'checkFile']);
Route::post('/threshold', [CheckerController::class, 'thresholdSettings'])->name('threshold');
Route::post('/format', [CheckerController::class, 'formatSettings'])->name('format');
