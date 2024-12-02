<?php

namespace Koeeru\PrometheusExporter\ResultStores;

use Illuminate\Support\Collection;
use Koeeru\PrometheusExporter\ResultStores\StoredCheckResults\StoredCheckResults;

interface ResultStore
{
    /** @param  Collection<int, \Koeeru\PrometheusExporter\Checks\Result>  $checkResults */
    public function save(Collection $checkResults): void;

    public function latestResults(): ?StoredCheckResults;
}
