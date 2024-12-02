<?php

namespace Spatie\Health\Support;

use Illuminate\Support\Collection;
use Koeeru\PrometheusExporter\Checks\Check;

class Checks extends Collection
{
    /** @param  array<int, Check>  $checks */
    public function __construct(array $checks)
    {
        parent::__construct($checks);
    }

    public function run(): void {}
}