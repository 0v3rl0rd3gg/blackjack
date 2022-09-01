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
	return redirect('/login');
});

Route::get( '/dashboard', [ \App\Http\Controllers\Controller::class, 'index' ] )->middleware('auth');
Route::post( 'post-bet', [ \App\Http\Controllers\Controller::class, 'postBet' ] );
Route::get( 'hit', [ \App\Http\Controllers\Controller::class, 'hit' ] );
Route::get( 'split', [ \App\Http\Controllers\Controller::class, 'split' ] );
Route::get( 'double-down', [ \App\Http\Controllers\Controller::class, 'doubleDown' ] );
Route::get( 'dealers-turn', [ \App\Http\Controllers\Controller::class, 'dealersTurn' ] );
Route::get( 'calculate-winner', [ \App\Http\Controllers\Controller::class, 'calculateWinner' ] );

require __DIR__.'/auth.php';
