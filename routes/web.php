<?php

use Showcase\Services\Photos;
use Illuminate\Http\Request;

// Authentication
\Route::get('/login',       'AuthController@GetLogin')      ->name('auth.login');
\Route::get('/logout',      'AuthController@GetLogout')     ->name('auth.logout');

// Global Pages
\Route::get('/',                'GlobalController@GetIndex')    ->name('index');
\Route::get('/region/{region}', 'GlobalController@GetRegion')   ->name('region');
\Route::get('/{batch}',         'GlobalController@GetBatch')    ->name('batch');

// Event Pages
\Route::prefix('/{batch}/{event}')->group(function(){
    \Route::get('/',                        'EventController@GetIndex')         ->name('event');
    \Route::get('/ideas',                   'EventController@GetIdeas')         ->name('event.ideas');
    \Route::get('/teams',                   'EventController@GetTeams')         ->name('event.teams');
    \Route::get('/photos',                  'EventController@GetPhotos')        ->name('event.photos');
    \Route::get('/wrapup',                  'EventController@GetWrapup')        ->name('event.wrapup');

    \Route::get('/presentations',           'EventController@GetPresentations') ->name('event.presentations');
    \Route::post('/presentations/shuffle',  'EventController@PostShuffle')      ->name('event.presentations.shuffle');
    \Route::post('/presentations/order',    'EventController@PostOrder')        ->name('event.presentations.order');
}); 

// Edit Controllers
// TODO(@tylermenezes): After account linking is complete, add check for changing current ones to middleware.
\Route::prefix('/{batch}/{event}/edit')->namespace('Edit')->middleware('edit')->group(function(){
    \Route::apiResource('photo', 'PhotoController');
    \Route::apiResource('idea', 'IdeaController');
    \Route::apiResource('team', 'TeamController');
});
