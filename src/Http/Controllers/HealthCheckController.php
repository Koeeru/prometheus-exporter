<?php

namespace Koeeru\PrometheusExporter\Http;

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
        if ($request->has('fresh') || config('health.oh_dear_endpoint.always_send_fresh_results')) {
            Artisan::call(RunHealthChecksCommand::class);
        }

        $checkResults = $resultStore->latestResults();

        $result = '';

        foreach ($checkResults->storedCheckResults as $checkResult) {
            $result .= "# HELP {$checkResult->name}\n";
            $result .= config('health.metric_prefix', 'service').'_'.Str::snake($checkResult->name).'_healthy_status: '.CheckResultStatus::fromName(Str::upper($checkResult->status))->value."\n";
        }

        return response($result, config('health.json_results_failure_status', 200))
            ->header('Content-Type', 'text/plain; version=0.0.4')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
    }
}
