<?php

namespace Koeeru\PrometheusExporter\ResultStores;

use Exception;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Koeeru\PrometheusExporter\Checks\Result;
use Koeeru\PrometheusExporter\ResultStores\StoredCheckResults\StoredCheckResult;
use Koeeru\PrometheusExporter\ResultStores\StoredCheckResults\StoredCheckResults;

class JsonFileHealthResultStore implements ResultStore
{
    protected FilesystemAdapter $disk;

    protected string $path;

    public function __construct(string $disk, string $path)
    {
        $this->disk = Storage::disk($disk);

        $this->path = $path;
    }

    /** @param  Collection<int, \Koeeru\PrometheusExporter\Checks\Result>  $checkResults */
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

        $contents = $report->toJson();

        if ($this->disk->exists($this->path)) {
            $this->disk->delete($this->path);
        }
        $this->disk->write($this->path, $contents);
    }

    public function latestResults(): ?StoredCheckResults
    {
        $content = null;

        try {
            $content = $this->disk->read($this->path);
        } catch (Exception $exception) {
            report($exception);
        }

        if (! $content) {
            return null;
        }

        return StoredCheckResults::fromJson($content);
    }
}
