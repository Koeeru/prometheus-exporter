<?php

namespace Koeeru\PrometheusExporter\Checks\Checks;

use Illuminate\Database\ConnectionResolverInterface;
use Koeeru\PrometheusExporter\Checks\Check;
use Koeeru\PrometheusExporter\Checks\Result;
use Spatie\Health\Support\DbConnectionInfo;

class DatabaseSizeCheck extends Check
{
    protected ?string $connectionName = null;

    protected float $failWhenSizeAboveGb = 1;

    public function connectionName(string $connectionName): self
    {
        $this->connectionName = $connectionName;

        return $this;
    }

    public function failWhenSizeAboveGb(float $errorThresholdGb): self
    {
        $this->failWhenSizeAboveGb = $errorThresholdGb;

        return $this;
    }

    public function run(): Result
    {
        $databaseSizeInGb = $this->getDatabaseSizeInGb();

        $result = Result::make()
            ->meta([
                'database_size' => $databaseSizeInGb,
            ])
            ->shortSummary("{$databaseSizeInGb} GB");

        return $databaseSizeInGb >= $this->failWhenSizeAboveGb
            ? $result->failed("Database size is {$databaseSizeInGb} GB, which is above the threshold of {$this->failWhenSizeAboveGb} GB")
            : $result->ok();
    }

    protected function getDefaultConnectionName(): string
    {
        return config('database.default');
    }

    protected function getDatabaseSizeInGb(): float
    {
        $connectionName = $this->connectionName ?? $this->getDefaultConnectionName();

        $connection = app(ConnectionResolverInterface::class)->connection($connectionName);

        return round((new DbConnectionInfo)->databaseSizeInMb($connection) / 1000, 2);
    }
}
