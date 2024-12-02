<?php

namespace Koeeru\PrometheusExporter\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Koeeru\PrometheusExporter\PrometheusExporter
 */
class PrometheusExporter extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Koeeru\PrometheusExporter\PrometheusExporter::class;
    }
}
