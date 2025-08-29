<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Admin routes are now loaded by LarabusServiceProvider for built-in apps
