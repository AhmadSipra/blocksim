<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
	
 		////...........User Routes.........////

    Route::post('signup', array('uses' => 'UserController@store'));
    Route::post('doLogin', array('uses' => 'UserController@doLogin'));
    Route::post('External_Login', array('uses' => 'UserController@External_Login'));
    Route::post('auth/login', ['uses' => 'AuthController@authenticate']);
    // Route::post('uploadProfileImage/{id}', array('uses' => 'UserController@uploadProfileImage'));
    // Route::post('uploadCNICImage/{id}', array('uses' => 'UserController@uploadCNICImage'));
    Route::post('showAllUsers', array('uses' => 'UserController@showAllUsers'));


    		////...........Order Routes.........////

    Route::post('orders', array('uses' => 'ordersController@store'));
    Route::post('edit/{id}', array('uses' => 'ordersController@update'));
    Route::delete('delOrder/{id}', array('uses' => 'ordersController@destroy'));
    // Route::post('uploadCNICImage/{id}', array('uses' => 'ordersController@uploadCNICImage'));
    // Route::post('uploadProfileImage/{id}', array('uses' => 'ordersController@uploadProfileImage'));
    Route::post('showOrderAgainstUserID', array('uses' => 'ordersController@showOrderAgainstUserID'));



    Route::post('profile', array('uses' => 'profilesController@store'));
    Route::post('updateprofle', array('uses' => 'profilesController@update'));
    Route::delete('delOrder/{id}', array('uses' => 'profilesController@destroy'));
    Route::post('showOrderAgainstUserID', array('uses' => 'profilesController@showOrderAgainstUserID'));

    Route::post('uploadCNICImage', array('uses' => 'profilesController@uploadCNICImage'));
    Route::post('uploadProfileImage', array('uses' => 'profilesController@uploadProfileImage'));


