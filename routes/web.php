<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UploadGudangController;
use App\Http\Controllers\UploadPenjualanController;
use App\Http\Controllers\UploadReplenishController;
use App\Http\Controllers\UploadReplenishEditController;
use Illuminate\Support\Facades\Route;

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

// Route::get('/', function () {
//     return view('welcome');
// });


Route::get('/', [HomeController::class, 'index']);


//report
Route::get('/reports/reports_replenish', [ReportController::class, 'reportReplenish']);
Route::get('/reports/exportExcelReportReplenish', [ReportController::class, 'exportExcelReportReplenish']);


//upload stock gudang
Route::get('/uploadgudang', [UploadGudangController::class, 'index']);
Route::post('/uploadgudang/validateUpload', [UploadGudangController::class, 'validationUpload']);
Route::post('/uploadgudang/upload', [UploadGudangController::class, 'uploadAction']);


//upload stock replenish
Route::get('/uploadreplenish', [UploadReplenishController::class, 'index']);
Route::post('/uploadreplenish/validateUpload', [UploadReplenishController::class, 'validationUpload']);
Route::post('/uploadreplenish/upload', [UploadReplenishController::class, 'uploadAction']);



//upload stock penjualan
Route::get('/uploadpenjualan', [UploadPenjualanController::class, 'index']);
Route::post('/uploadpenjualan/validateUpload', [UploadPenjualanController::class, 'validationUpload']);
Route::post('/uploadpenjualan/upload', [UploadPenjualanController::class, 'uploadAction']);


//upload stock replenish edit
Route::get('/uploadreplenishedit', [UploadReplenishEditController::class, 'index']);
Route::post('/uploadreplenishedit/updateReplenish', [UploadReplenishEditController::class, 'updateReplenish']);
Route::post('/uploadreplenishedit/validateUpload', [UploadReplenishEditController::class, 'validationUpload']);
Route::post('/uploadreplenishedit/upload', [UploadReplenishEditController::class, 'uploadAction']);
