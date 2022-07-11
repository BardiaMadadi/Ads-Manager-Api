<?php

use App\Http\Controllers\UserController;
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

// public :

Route::group(["prefix" => "/v1"], function () {

    Route::group(["prefix" => "/user"], function () {
        Route::post("/register", [UserController::class, "register"]);
        Route::post("/login", [UserController::class, "login"]);
    });

});



// protected :
Route::group(["middleware" => "auth:sanctum"], function () {

    Route::group(["prefix" => "/v1"], function () {

        Route::group(["prefix" => "/user"], function () {

            Route::get("/get", [UserController::class, "get"]);
            Route::put("/update", [UserController::class, "update"]);
            Route::get("/logout", [UserController::class, "logout"]);

        });

    });

});



