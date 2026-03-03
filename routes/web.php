<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/verify-document/{token}', function ($token) {
    return view('verify-document');
});