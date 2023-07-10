<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BucketManage;

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

Route::get('/', [BucketManage::class, 'index']);
Route::post('/store', [BucketManage::class, 'store']);
Route::get('/result', [BucketManage::class, 'result']);


// Route::get('/bucket', function () {
//     return view('bucket_form');
// });
