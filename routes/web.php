<?php

use Illuminate\Support\Facades\Route;

Route::get('metrics', \Koeeru\PrometheusExporter\Http\Controllers\HealthCheckController::class);
