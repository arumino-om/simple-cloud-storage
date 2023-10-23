<?php

use App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get("/storage/{storage_id}/list", [Controllers\StorageAPIController::class, "list"]);
Route::get("/storage/{storage_id}/thumbnail", [Controllers\StorageAPIController::class, "thumbnail"]);
Route::get("/storage/{storage_id}/get", [Controllers\StorageAPIController::class, "get"]);
Route::get("/storage/list", [Controllers\StorageAPIController::class, "storages"]);
Route::post("/storage/add", [Controllers\StorageAPIController::class, "storages_add"]);

