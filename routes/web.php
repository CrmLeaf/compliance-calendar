<?php

declare(strict_types=1);

use Crmleaf\Payroll\Tools\ComplianceCalendar\Http\Controllers\ComplianceCalendarController;
use Illuminate\Support\Facades\Route;

/*
 * Loaded by ComplianceCalendarServiceProvider only when config('compliance-calendar.route.enabled')
 * is true, so requiring the package never adds a URL on its own.
 */

/** @var \Illuminate\Contracts\Config\Repository $config */
$config = app('config');

Route::middleware((array) $config->get('compliance-calendar.route.middleware', ['web']))
    ->prefix((string) $config->get('compliance-calendar.route.prefix', 'tools'))
    ->group(static function () use ($config): void {
        Route::match(['get', 'post'], '/compliance-calendar', ComplianceCalendarController::class)
            ->name((string) $config->get('compliance-calendar.route.name', 'compliance-calendar'));
    });
