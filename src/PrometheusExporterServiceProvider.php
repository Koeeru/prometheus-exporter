<?php

namespace Koeeru\PrometheusExporter;

use Koeeru\PrometheusExporter\Checks\Check;
use Koeeru\PrometheusExporter\Checks\Checks\PingCheck;
use Koeeru\PrometheusExporter\Commands\RunHealthChecksCommand;
use Koeeru\PrometheusExporter\ResultStores\ResultStore;
use Koeeru\PrometheusExporter\ResultStores\ResultStores;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PrometheusExporterServiceProvider extends PackageServiceProvider
{

    public function configurePackage(Package $package): void
    {
        $package
            ->name('prometheus-exporter')
            ->hasConfigFile()
            ->hasRoute('web')
            ->hasCommand(RunHealthChecksCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(PrometheusExporter::class);
        $this->app->alias(PrometheusExporter::class, 'prometheus-exporter');
        $this->app->bind(ResultStore::class, fn () => ResultStores::createFromConfig()->first());

        \Koeeru\PrometheusExporter\Facades\PrometheusExporter::checks([
            ...$this->registerDefaultChecks(),
            ...(app()->environment() !== 'local' ? $this->registerProductionChecks() : []),
            ...$this->registerGetPingChecks(),
            ...$this->registerPostPingChecks(),
        ]);
    }

    public function registerDefaultChecks(): array
    {
        return array_map(function ($check) {
            return $check instanceof Check ? $check : new $check;
        }, config('prometheus-exporter.metrics.defaults'));
    }

    public function registerProductionChecks(): array
    {
        return array_map(function ($check) {
            return $check instanceof Check ? $check : new $check;
        }, config('prometheus-exporter.metrics.prod'));
    }

    private function registerGetPingChecks(): array
    {
        return $this->registerPingCheck(config('prometheus-exporter.metrics.get_ping_checks'), 'GET');
    }

    private function registerPostPingChecks(): array
    {
        return $this->registerPingCheck(config('prometheus-exporter.metrics.post_ping_checks'), 'POST');
    }

    private function registerPingCheck(array $urls, string $method): array
    {
        return array_map(function ($url) use ($method) {
            $basePath = getenv('APP_URL');

            return PingCheck::new()
                ->url($basePath.$url)
                ->name($url)
                ->method($method);
        }, $urls);
    }
}
