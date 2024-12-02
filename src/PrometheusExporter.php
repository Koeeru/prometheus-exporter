<?php

namespace Koeeru\PrometheusExporter;

use Illuminate\Support\Collection;
use Koeeru\PrometheusExporter\Checks\Check;
use Koeeru\PrometheusExporter\Exceptions\DuplicateCheckNamesFound;
use Koeeru\PrometheusExporter\Exceptions\InvalidCheck;
use Koeeru\PrometheusExporter\ResultStores\ResultStore;
use Koeeru\PrometheusExporter\ResultStores\ResultStores;

class PrometheusExporter
{
    /** @var array<int, Check> */
    protected array $checks = [];

    /** @param array<int, Check> $checks
     * @throws InvalidCheck|DuplicateCheckNamesFound
     */
    public function checks(array $checks): self
    {
        $this->ensureCheckInstances($checks);

        $this->checks = array_merge($this->checks, $checks);

        $this->guardAgainstDuplicateCheckNames();

        return $this;
    }

    public function clearChecks(): self
    {
        $this->checks = [];

        return $this;
    }

    /** @return Collection<int, Check> */
    public function registeredChecks(): Collection
    {
        return collect($this->checks);
    }

    /** @param array<int,mixed> $checks
     * @throws InvalidCheck
     */
    protected function ensureCheckInstances(array $checks): void
    {
        foreach ($checks as $check) {
            if (! $check instanceof Check) {
                throw InvalidCheck::doesNotExtendCheck($check);
            }
        }
    }

    /** @return Collection<int, ResultStore> */
    public function resultStores(): Collection
    {
        return ResultStores::createFromConfig();
    }

    /**
     * @throws DuplicateCheckNamesFound
     */
    protected function guardAgainstDuplicateCheckNames(): void
    {
        $duplicateCheckNames = collect($this->checks)
            ->map(fn (Check $check) => $check->getName())
            ->duplicates();

        if ($duplicateCheckNames->isNotEmpty()) {
            throw DuplicateCheckNamesFound::make($duplicateCheckNames);
        }
    }
}
