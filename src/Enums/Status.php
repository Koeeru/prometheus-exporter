<?php

namespace Koeeru\PrometheusExporter\Enums;

enum Status: string
{
    case ok = 'ok';
    case failed = 'failed';
    case warning = 'warning';

    case skipped = 'skipped';
    case crashed = 'crashed';
}
