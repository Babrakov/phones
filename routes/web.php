<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});


//Route::get('admin/register', function () {
//    return redirect('/');
//})->name('backpack.auth.register');

//Route::get('/check/{phone}', 'App\Http\Controllers\Admin\PhoneCrudController@check')->name('check');
Route::get('/dbcf6fd7-1f31-4603-97cf-16c6f68c29a8/{phone}', 'App\Http\Controllers\Admin\PhoneCrudController@check')->name('check');


