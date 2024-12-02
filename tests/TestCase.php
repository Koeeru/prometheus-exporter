<?php

namespace Koeeru\PrometheusExporter\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Koeeru\PrometheusExporter\PrometheusExporterServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Koeeru\\PrometheusExporter\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            PrometheusExporterServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_prometheus-exporter_table.php.stub';
        $migration->up();
        */
    }
}
