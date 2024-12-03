<?php

namespace Koeeru\PrometheusExporter\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Koeeru\PrometheusExporter\Commands\RunHealthChecksCommand;
use Koeeru\PrometheusExporter\Enums\CheckResultStatus;
use Koeeru\PrometheusExporter\ResultStores\ResultStore;

class HealthCheckController
{
    public function __invoke(Request $request, ResultStore $resultStore): \Illuminate\Http\Response
    {
        if ($request->has('fresh')) {
            Artisan::call(RunHealthChecksCommand::class);
        }

        $checkResults = $resultStore->latestResults();
        $storedCheckResults = $checkResults?->storedCheckResults ?? [];

        $result = '';

        foreach ($storedCheckResults as $checkResult) {
            $type = $checkResult->meta['type'] === 'services' ? Str::snake($checkResult->name) : 'http';
            $result .= config('prometheus-exporter.metric_prefix', 'service').'_';
            $result .= $type.'_health_status';

            $handler = '';

            if ($checkResult->meta['type'] === 'http' && count($checkResult->meta) > 1) {
                $labels = [];

                foreach ($checkResult->meta as $key => $value) {
                    if ($key !== 'type') {
                        $labels[] = $key.'="'.$value.'"';
                    }
                }
                $handler = '{'.implode(',', $labels).'}';
            }
            $result .= $handler;

            //            $result .= json_encode(array_filter($checkResult->meta, fn($key) => $key !== 'type'), ARRAY_FILTER_USE_KEY);

            $result .= ' '.CheckResultStatus::fromName(Str::upper($checkResult->status))->value."\n";
            //            dd($result);
            //            $result .= config('prometheus-exporter.metric_prefix', 'service').'_'.Str::snake($checkResult->name).'_healthy_status: '.CheckResultStatus::fromName(Str::upper($checkResult->status))->value."\n";
        }

        return response($result, config('health.json_results_failure_status', 200))
            ->header('Content-Type', 'text/plain; version=0.0.4')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
    }
}
