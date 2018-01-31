<?php

use Illuminate\Http\Request;

\Route::get('/region/{region}.json',        'GlobalController@GetRegionJson')       ->name('region.json');
\Route::get('/region/{region}/photos.json', 'GlobalController@GetRegionPhotosJson') ->name('region.photos.json');
