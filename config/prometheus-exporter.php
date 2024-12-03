<?php

// config for Koeeru/PrometheusExporter
return [
    'metric_prefix' => config('app.name'),
    'metrics' => [
        'defaults' => [
            \Koeeru\PrometheusExporter\Checks\Checks\DatabaseCheck::class,
            \Koeeru\PrometheusExporter\Checks\Checks\RedisCheck::class,
            \Koeeru\PrometheusExporter\Checks\Checks\MailCheck::class,
        ],
        'prod' => [
            \Koeeru\PrometheusExporter\Checks\Checks\DebugModeCheck::class,
            \Koeeru\PrometheusExporter\Checks\Checks\UsedDiskSpaceCheck::class,
            \Koeeru\PrometheusExporter\Checks\Checks\CpuLoadCheck::class,
        ],
        'get_ping_checks' => [
            '/',
        ],
        'post_ping_checks' => [

        ],
    ],
    'result_stores' => [
        \Koeeru\PrometheusExporter\ResultStores\CacheHealthResultStore::class => [
            'store' => 'file',
        ],
    ],
];
