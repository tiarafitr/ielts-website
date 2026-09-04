<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Only one route: the dashboard shell. It's a single Blade view that mounts
| the Vue.js app (built with Vite) which then talks to the /api/speaking/*
| endpoints above.
*/

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
});
