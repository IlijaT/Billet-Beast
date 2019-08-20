<?php

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
  return "Laravel";
});


Route::get('/concerts/{concert}', 'ConcertsController@show')->name('concerts.show');
Route::post('/concerts/{concert}/orders', 'ConcertOrdersController@store');
Route::get('/orders/{confirmationNumber}', 'OrdersController@show');

Route::get('/login', 'Auth\LoginController@showLoginForm');
Route::post('/login', 'Auth\LoginController@login')->name('auth.login');
Route::post('/logout', 'Auth\LoginController@logout')->name('auth.logout');


Route::group(['middleware' => 'auth', 'prefix' => 'backstage', 'namespace' => 'Backstage'], function () {
  Route::get('concerts', 'ConcertsController@index')->name('backstage.concerts.index');
  Route::post('concerts', 'ConcertsController@store')->name('backstage.concerts.store');
  Route::get('concerts/new', 'ConcertsController@create')->name('backstage.concerts.new');
  Route::get('concerts/{id}/edit', 'ConcertsController@edit')->name('backstage.concerts.edit');
  Route::patch('concerts/{id}', 'ConcertsController@update')->name('backstage.concerts.update');
  Route::post('published-concerts', 'PublishedConcertsController@store');
});
