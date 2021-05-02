<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1', 'middleware' => 'api_token'], function () {

    Route::post('post', 'Api\PostController@store')->middleware('user_can_manage_post');
});

Route::group(['prefix' => 'v1'], function () {
    Route::post('token', 'Api\APIController@token');

    //Authentication
    Route::post('login', 'Api\UserController@login');

    Route::get('languages', 'Api\LanguageController@index');

    //Post
    Route::get('posts', 'Api\PostController@index');
    Route::get('post/{id?}', 'Api\PostController@show');

    //Page
    Route::get('page/{id?}', 'Api\PageController@show');

    //Home
    Route::get('home/{id?}', 'Api\HomeController@show');

    //Experience
    Route::get('experience/{id?}', 'Api\ExperienceController@show');

    //Car
    Route::get('car/{id?}', 'Api\CarController@show');
});
