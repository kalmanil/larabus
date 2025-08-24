<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/', function () {
        return view($_ENV['DOMAIN_VIEW_TEMPLATE'], [
            'siteName' => $_ENV['DOMAIN_SITE_TITLE'],
            'domain' => request()->getHost(),
            'app' => $_ENV['DOMAIN_APP_NAME'],
            'themeColor' => $_ENV['DOMAIN_THEME_COLOR']
        ]);
    });

    Route::get('/about', function () {
        return "About " . $_ENV['DOMAIN_SITE_TITLE'] . " - powered by Larabus" .
               "<br>Domain: " . request()->getHost() .
               "<br>App: " . $_ENV['DOMAIN_APP_NAME'] .
               "<br>Theme: " . $_ENV['DOMAIN_THEME_COLOR'];
    });
});