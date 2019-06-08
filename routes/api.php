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
    Route::middleware('app.api.check')->namespace('Api')->group(function () {

        //Hotel
        Route::get('/hotel', 'HotelController@index');
        Route::get('/hotel/{hotel_id}', 'HotelController@show');

        //Reservation
        Route::get('/reservation', 'ReservationController@index');

        //Payment
        Route::post('/payment', 'PaymentController@store');

        //Get Ticket
        Route::get('/ticket', 'TicketController@index');

    });


    //Supplier
    Route::middleware('app.supplier.check')->namespace('Supplier')->prefix('/supplier')->group(function () {

        //Hotel
        Route::post('/hotel/update/{hotel_id}', 'HotelController@update');
        Route::resource('/hotel', 'HotelController');

        //Hotel Comment
        Route::post('/hotel/{hotel_id}/comment/update/{hotel_comment_id}', 'HotelCommentController@update');
        Route::resource('/hotel/{hotel_id}/comment', 'HotelCommentController');

        //Hotel Gallery
        Route::resource('/hotel/{hotel_id}/gallery', 'HotelGalleryController');


        //Hotel Room Episode
        Route::post('/hotel/{hotel_id}/room/episode/update/{room_episode_id}', 'RoomEpisodeController@update');
        Route::resource('/hotel/{hotel_id}/room/episode', 'RoomEpisodeController');

        //Hotel Room
        Route::post('/hotel/{hotel_id}/room/update/{room_id}', 'RoomController@update');
        Route::resource('/hotel/{hotel_id}/room', 'RoomController');

        //Hotel Rack
        Route::get('/hotel/{hotel_id}/rack', 'RackController@index');

        //Setting
        Route::get('/setting', 'SettingController@index');

        //Agency
        Route::namespace('WebService')->prefix('/webservice')->group(function () {

            //Hotel Room
            Route::get('/hotel/{hotel_id}/room', 'RoomController@index');

            //Hotel Gallery
            Route::get('/hotel/{hotel_id}/gallery', 'HotelGalleryController@index');

            //Reservation
            Route::get('/reservation', 'ReservationController@index');
            Route::get('/reservation/{room_id}', 'ReservationController@show');

            //Payment
            Route::Post('/payment', 'ShoppingController@store');

            //Agency Request
            Route::Post('/agency/request', 'AgencyRequestController@store');

            //Hotel Comment
            Route::get('/hotel/{hotel_id}/comment', 'HotelCommentController@index');
        });

    });

    //Agency
    Route::middleware('app.agency.check')->namespace('Agency')->prefix('/agency')->group(function () {

        //Reservation
        Route::get('/reservation', 'ReservationController@index');


    });

});

