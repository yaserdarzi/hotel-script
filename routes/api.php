<?php

use Illuminate\Http\Request;

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
if (App::environment() == "production")
    \URL::forceScheme('https');

Route::namespace('Api\V1')->prefix('/v1')->group(function () {

    //Site After login
    Route::middleware('app.supplier.check')->namespace('Supplier')->prefix('/supplier')->group(function () {

        //Hotel
        Route::post('/hotel/update/{hotel_id}', 'HotelController@update');
        Route::resource('/hotel', 'HotelController');

        //Hotel Gallery
        Route::resource('/hotel/{hotel_id}/gallery', 'HotelGalleryController');


        //Hotel Room Episode
        Route::post('/hotel/{hotel_id}/room/episode/update/{room_episode_id}', 'RoomEpisodeController@update');
        Route::resource('/hotel/{hotel_id}/room/episode', 'RoomEpisodeController');

        //Hotel Room
        Route::post('/hotel/{hotel_id}/room/update/{room_id}', 'RoomController@update');
        Route::resource('/hotel/{hotel_id}/room', 'RoomController');

    });

    //Site After login
    Route::middleware('app.agency.check')->namespace('Agency')->prefix('/agency')->group(function () {

        //Reservation
        Route::get('/reservation', 'ReservationController@index');


    });

});

