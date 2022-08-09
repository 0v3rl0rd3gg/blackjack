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

//Route::get('/dashboard', 'App\Http\Controllers\Controller@index');
Route::get('/dashboard', [\App\Http\Controllers\Controller::class, 'index']);
Route::post('post-bet', [\App\Http\Controllers\Controller::class, 'postBet']);

//Route::get('/dashboard', function () {
//   return view('game');
//})->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';