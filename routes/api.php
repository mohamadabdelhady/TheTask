<?php

use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/getAllCategories',[\App\Http\Controllers\GetData::class,'getCategoriesData']);
Route::post('/getCategoryOption',[\App\Http\Controllers\GetData::class,'getCategoryOption']);
Route::get('/getOptionChildren/{optionId}',[\App\Http\Controllers\GetData::class,'getOptionsChildren']);
