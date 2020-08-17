<?php

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['namespace' => 'Api', 'as' => 'api.'], function () {

    Route::post('/auth/login', 'AuthController@login')->name('login');
    Route::post('/usuario', 'UserController@store')->name('user.store');

    Route::group(['middleware' => ['apiProtected']], function () {
        Route::get('/usuario', 'UserController@index')->name('user.index');

        Route::get('/auth/logout', 'AuthController@logout')->name('auth.logout');
        Route::get('/auth/atualizar-token', 'AuthController@refresh')->name('auth.update.token');
        Route::get('/auth/usuario', 'AuthController@me')->name('auth.me');

        Route::get('/usuario/{id}', 'UserController@show')->name('user.user');
        Route::put('/usuario', 'UserController@update')->name('user.update');
        Route::post('/usuario/avatar', 'UserController@avatar')->name('user.avatar');
        Route::put('/usuario/senha', 'UserController@password')->name('user.password');

        Route::get('/cursos', 'CursosController@index')->name('cursos.index');
        Route::get('/cursos/{cursos}', 'CursosController@show')->name('cursos.show');
        Route::get('/categorias/cursos/{tipo}', 'CategoriasController@cursos')->name('categorias.cursos');
        Route::get('/cursos/busca/{titulo}', 'CursosController@busca')->name('cursos.busca');
    });
});
