<?php

namespace Koeeru\PrometheusExporter;

use Koeeru\PrometheusExporter\Commands\RunHealthChecksCommand;
use Koeeru\PrometheusExporter\ResultStores\ResultStores;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PrometheusExporterServiceProvider extends PackageServiceProvider
{
    public array $routeFileNames = [
        'web',
    ];

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('prometheus-exporter')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_prometheus_exporter_table')
            ->hasCommand(RunHealthChecksCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(PrometheusExporter::class);
        $this->app->alias(PrometheusExporter::class, 'prometheus');
        $this->app->bind(ResultStores::class, fn () => ResultStores::createFromConfig()->first());
    }
}
