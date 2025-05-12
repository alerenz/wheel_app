<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PromocodeController;
use App\Http\Controllers\Api\MaterialThingController;
use App\Http\Controllers\Api\EmptyPrizeController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WheelController;
use App\Http\Controllers\Api\SectorController;
use App\Http\Middleware\CheckRole;
use App\Http\Controllers\Api\UserPrizeController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PromocodesCodeController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth');

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('show', [AuthController::class, 'user']);
    Route::post('register', [AuthController::class, 'register']);
});

Route::group(['middleware'=>['jwt.auth', 'role:admin']], function ($router) {
    Route::get('promocode', [PromocodeController::class,'index']);
    Route::post('promocode', [PromocodeController::class,'store']);
    Route::get('promocode/{id}', [PromocodeController::class,'show']);
    Route::put('promocode/{id}', [PromocodeController::class,'update']);
    Route::delete('promocode/{id}', [PromocodeController::class,'destroy']);

    Route::get('material-thing', [MaterialThingController::class,'index']);
    Route::post('material-thing', [MaterialThingController::class,'store']);
    Route::get('material-thing/{id}', [MaterialThingController::class,'show']);
    Route::put('material-thing/{id}', [MaterialThingController::class,'update']);
    Route::delete('material-thing/{id}', [MaterialThingController::class,'destroy']);

    Route::get('empty-prize', [EmptyPrizeController::class,'index']);
    Route::post('empty-prize', [EmptyPrizeController::class,'store']);
    Route::get('empty-prize/{id}', [EmptyPrizeController::class,'show']);
    Route::put('empty-prize/{id}', [EmptyPrizeController::class,'update']);
    Route::delete('empty-prize/{id}', [EmptyPrizeController::class,'destroy']);

    Route::get('wheel', [WheelController::class,'index']);
    Route::post('wheel', [WheelController::class,'store']);
    Route::get('wheel/{id}', [WheelController::class,'show']);
    Route::put('wheel/{id}', [WheelController::class,'update']);
    Route::delete('wheel/{id}', [WheelController::class,'destroy']);

    Route::get('sector', [SectorController::class,'index']);
    Route::post('sector', [SectorController::class,'store']);
    Route::get('sector/{id}', [SectorController::class,'show']);
    Route::put('sector/{id}', [SectorController::class,'update']);
    Route::delete('sector/{id}', [SectorController::class,'destroy']);

    Route::get('userPrize',[UserPrizeController::class, 'index']);
    Route::get('userPrize/{id}',[UserPrizeController::class, 'show']);

    Route::get('users',[UserController::class, 'index']);
    
    Route::get('promocodesCode',[PromocodesCodeController::class,'index']);
    Route::post('promocodesCode/{id}',[PromocodesCodeController::class,'store']);
    
});

Route::group(['middleware'=>'jwt.auth'], function ($router) {
    Route::get('wheels/activeWheel',[WheelController::class, 'activeWheel']);
    Route::get('sectors/winSector',[SectorController::class, 'getDroppedSector']);
    Route::get('userPrizes/user',[UserPrizeController::class, 'getUserPrizes']);
});
