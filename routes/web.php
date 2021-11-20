<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Calculator;
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
Route::get('/', [Calculator::class,'index']);

Route::get('/calculator/getBanks', [Calculator::class,'getBanks']);

Route::post('/calculator/getMortgage', [Calculator::class,'getMortgages']);

Route::post('/calculator/getPercent', [Calculator::class,'getPercent']);

Route::post('/calculator/validate', [Calculator::class,'validate']);

Route::post('/calculator/results', [Calculator::class,'createFileAndResults']);

Route::post('/calculator/getPdf', [Calculator::class,'getPdf']);

Route::post('/calculator/getXlsx', [Calculator::class,'getXlsx']);
