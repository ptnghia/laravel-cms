<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Include admin routes
require __DIR__.'/admin.php';
