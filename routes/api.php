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

Route::middleware('app.check')->namespace('Api\V1')->prefix('/v1')->group(function () {

    //Site Before login
    Route::namespace('Supplier')->prefix('/supplier')->group(function () {

        //Hotel
        Route::post('/hotel/update/{hotel_id}', 'HotelController@update');
        Route::resource('/hotel', 'HotelController');

        //Hotel Gallery
        Route::resource('/hotel/{hotel_id}/gallery', 'HotelGalleryController');

        //Hotel Room
        Route::resource('/hotel/{hotel_id}/room', 'HotelController');


    });

    //Room
//    Route::resource('{hotel_id}/room', 'RoomController');

//    //Zarinpall Callback
//    Route::any('storeProjectPaymentCallback', 'ProjectsController@storeProjectPaymentCallback')->name('project.payment.callback');
//



//    //After Login
//    Route::middleware('users.authenticate')->group(function () {
//
//        //Users
//        Route::get('/profile/init', 'UsersController@index');
//        Route::post('/profile/update', 'UsersController@update');
//
//        //Category
//        Route::get('/category', 'CategoryController@index');
//
//        //Category Plan
//        Route::get('category/{category_id}/plan', 'CategoryPlanController@index');
//
//        //Category Timing
//        Route::get('category/{category_id}/timing', 'CategoryTimingController@index');
//
//        //Invoice
//        Route::resource('/invoice', 'InvoiceController', ['only' => ['index']]);
//
//        //Only Customer
//        Route::middleware('users.customer.authenticate')->group(function () {
//
//            //project
//            Route::post('/projects/invoice', 'ProjectsController@projectInvoice');
//            Route::resource('/projects', 'ProjectsController', ['only' => ['index', 'store', 'show']]);
//
//        });
//    });
});

