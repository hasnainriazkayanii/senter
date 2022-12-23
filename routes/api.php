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
// Route::group(['middleware' =>'auth:api'], function () {
Route::group(['prefix' =>'posts','as'=>'posts.','namespace'=>'App\Http\Controllers\Api'], function () {
    Route::get('', 'PostController@index')->name('list');
    Route::get('edit/{id}', 'PostController@edit')->name('edit');
    Route::post('store', 'PostController@save')->name('save');
    Route::get('delete/{id}', 'PostController@destroy')->name('delete');
    Route::get('delete-all', 'PostController@delete_all')->name('delete.all');
});

Route::group(['prefix' =>'sports-categories','as'=>'sports.categories.','namespace'=>'App\Http\Controllers\Api'], function () {
    Route::get('', 'SportCategoryController@index')->name('list');
    Route::get('edit/{id}', 'SportCategoryController@edit')->name('edit');
    Route::post('store', 'SportCategoryController@save')->name('save');
    Route::get('delete/{id}', 'SportCategoryController@destroy')->name('delete');
    Route::get('delete-all', 'SportCategoryController@delete_all')->name('delete.all');
});


Route::group(['prefix' =>'customers','as'=>'customers.','namespace'=>'App\Http\Controllers\Api'], function () {
    Route::get('', 'CustomerController@index')->name('list');
    Route::get('edit/{id}', 'CustomerController@edit')->name('edit');
    Route::post('store', 'CustomerController@save')->name('save');
    Route::get('delete/{id}', 'CustomerController@destroy')->name('delete');
    Route::post('login', 'CustomerController@login')->name('login');
    Route::post('verify-account','CustomerController@verify_code')->name('email.verify');
    Route::post('send-verification-otp','CustomerController@send_verification_code')->name('email.send.verification.otp');
    Route::post('forget-password','CustomerController@forget_password')->name('forget.password');
    Route::post('reset-password','CustomerController@update_password')->name('reset.password');
    Route::get('delete-all', 'CustomerController@delete_all')->name('delete.all');
});


Route::group(['prefix' =>'likes','as'=>'likes.','namespace'=>'App\Http\Controllers\Api'], function () {
    Route::get('/{post_id}', 'PostLikeController@index')->name('list');
    Route::post('store', 'PostLikeController@save')->name('save');
    Route::get('delete/{post_id}/{customer_id}', 'PostLikeController@destroy')->name('delete');
    Route::get('delete-all', 'PostLikeController@delete_all')->name('delete.all');
});


Route::group(['prefix' =>'comments','as'=>'comments.','namespace'=>'App\Http\Controllers\Api'], function () {
    Route::get('/{post_id}', 'PostCommentController@index')->name('list');
    Route::get('edit/{id}', 'PostCommentController@edit')->name('edit');
    Route::post('store', 'PostCommentController@save')->name('save');
    Route::get('delete/{id}', 'PostCommentController@destroy')->name('delete');
    Route::get('delete-all', 'PostCommentController@delete_all')->name('delete.all');
});


Route::group(['prefix' =>'sub-comments','as'=>'sub-comments.','namespace'=>'App\Http\Controllers\Api'], function () {
    Route::get('/{comment_id}', 'SubCommentController@index')->name('list');
    Route::get('edit/{id}', 'SubCommentController@edit')->name('edit');
    Route::post('store', 'SubCommentController@save')->name('save');
    Route::get('delete/{id}', 'SubCommentController@destroy')->name('delete');
    Route::get('delete-all', 'SubCommentController@delete_all')->name('delete.all');
});

Route::group(['prefix' =>'shares','as'=>'shares.','namespace'=>'App\Http\Controllers\Api'], function () {
    Route::get('/{post_id}', 'PostShareController@index')->name('list');
    Route::get('users/{user_id}', 'PostShareController@user_shared')->name('user.list');
    Route::get('edit/{id}', 'PostShareController@edit')->name('edit');
    Route::post('store', 'PostShareController@save')->name('save');
    Route::get('delete/{id}', 'PostShareController@destroy')->name('delete');
    Route::get('delete-all', 'PostShareController@delete_all')->name('delete.all');
});


Route::group(['prefix' =>'saved','as'=>'saved.','namespace'=>'App\Http\Controllers\Api'], function () {
    Route::get('/{customer_id}', 'LaterPostsController@index')->name('list');
    Route::get('edit/{id}', 'LaterPostsController@edit')->name('edit');
    Route::post('store', 'LaterPostsController@save')->name('save');
    Route::get('delete/{id}', 'LaterPostsController@destroy')->name('delete');
    Route::get('delete-all', 'LaterPostsController@delete_all')->name('delete.all');
});


Route::group(['prefix' =>'followers','as'=>'followers.','namespace'=>'App\Http\Controllers\Api'], function () {
    Route::get('/{customer_id}', 'FollowerController@index')->name('list');
    Route::get('following/{customer_id}', 'FollowerController@following')->name('following');
    Route::post('follow', 'FollowerController@save')->name('save');
    Route::get('unfollow/{customer_id}/{follower_id}', 'FollowerController@destroy')->name('delete');
    Route::get('delete-all', 'FollowerController@delete_all')->name('delete.all');
});
// });
