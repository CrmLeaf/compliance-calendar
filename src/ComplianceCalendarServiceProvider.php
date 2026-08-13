<?php

declare(strict_types=1);

namespace Crmleaf\Payroll\Tools\ComplianceCalendar;

use Crmleaf\Payroll\Calculators\ComplianceCalendar;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

/**
 * Registers Compliance Calendar with a Laravel application.
 *
 * Everything this provider adds is either inert or off by default: the
 * calculator binding, one Blade component and a set of publishable paths. The
 * HTTP route is opt-in through `config('compliance-calendar.route.enabled')`, because a
 * package that installs a public URL into your application without being asked
 * is a package that has made a routing decision on your behalf.
 */
final class ComplianceCalendarServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/compliance-calendar.php', 'compliance-calendar');

        // A singleton because the calculator is stateless and its rate
        // repository parses the statutory tables once per process.
        $this->app->singleton(ComplianceCalendar::class, static fn (): ComplianceCalendar => new ComplianceCalendar());
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'compliance-calendar');

        // One component per tool: resources/views/components/compliance-calendar.blade.php,
        // written as <x-crmleaf::compliance-calendar />. Every tool registers the same
        // 'crmleaf' prefix, so fifteen independently installed packages share one
        // component namespace instead of contributing fifteen aliases.
        Blade::anonymousComponentPath(__DIR__.'/../resources/views/components', 'crmleaf');

        if ($this->routeEnabled()) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/compliance-calendar.php' => config_path('compliance-calendar.php'),
            ], 'compliance-calendar-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/compliance-calendar'),
            ], 'compliance-calendar-views');

            $this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/compliance-calendar'),
            ], 'compliance-calendar-assets');
        }
    }

    /**
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            ComplianceCalendar::class,
        ];
    }

    private function routeEnabled(): bool
    {
        /** @var \Illuminate\Contracts\Config\Repository $config */
        $config = $this->app->make('config');

        return (bool) $config->get('compliance-calendar.route.enabled', false);
    }
}
