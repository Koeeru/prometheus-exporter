<?php

namespace Koeeru\PrometheusExporter\ResultStores;

use Illuminate\Support\Collection;
use Koeeru\PrometheusExporter\Checks\Result;
use Koeeru\PrometheusExporter\ResultStores\StoredCheckResults\StoredCheckResult;
use Koeeru\PrometheusExporter\ResultStores\StoredCheckResults\StoredCheckResults;

class CacheHealthResultStore implements ResultStore
{
    public function __construct(
        public string $store = 'file',
        public string $cacheKey = 'health:storeResults',
    ) {}

    public function save(Collection $checkResults): void
    {
        $report = new StoredCheckResults(now());

        $checkResults
            ->map(function (Result $result) {
                return new StoredCheckResult(
                    name: $result->check->getName(),
                    label: $result->check->getLabel(),
                    notificationMessage: $result->getNotificationMessage(),
                    shortSummary: $result->getShortSummary(),
                    status: (string) $result->status->value,
                    meta: $result->meta,
                );
            })
            ->each(function (StoredCheckResult $check) use ($report) {
                $report->addCheck($check);
            });

        cache()
            ->store($this->store)
            ->put($this->cacheKey, $report->toJson());
    }

    public function latestResults(): ?StoredCheckResults
    {
        $healthResultsJson = cache()
            ->store($this->store)
            ->get($this->cacheKey);

        if (! $healthResultsJson) {
            return null;
        }

        return StoredCheckResults::fromJson($healthResultsJson);
    }
}
