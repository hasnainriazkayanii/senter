<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
// use Thread;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('user/{?id}',function($id){
    return $id;
});
Route::get('user',function(){
    return 'Hello';
});
Route::group(['prefix' => 'artsian-commands'], function () {
    Route::get('clear-cache', function () {
        $exitCode = Artisan::call('cache:clear');
        return '<h1>Cache  cleared</h1>';
    })->name('atisan.commands.clear.cache');
    Route::get('map-media-path', function () {
        $exitCode = Artisan::call('storage:link');
        return '<h1>Storage Linked</h1>';
    })->name('atisan.commands.map.public.path');
    Route::get('migrate', function () {
        $exitCode = Artisan::call('migrate');
        return '<h1>Migrated</h1>';
    })->name('atisan.commands.migrate');
    Route::get('seed', function () {
        $exitCode = Artisan::call('db:seed');
        return '<h1>Seeeded</h1>';
    })->name('atisan.commands.seed');
});

Route::group(['as'=>'posts.','namespace'=>'App\Http\Controllers'], function () {
    Route::get('home', 'HomeController@index')->name('list');
});

