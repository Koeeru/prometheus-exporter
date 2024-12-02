<?php

namespace Koeeru\PrometheusExporter\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Koeeru\PrometheusExporter\Checks\Check;
use Koeeru\PrometheusExporter\Checks\Result;
use Koeeru\PrometheusExporter\Enums\Status;
use Koeeru\PrometheusExporter\Exceptions\CheckDidNotComplete;
use Koeeru\PrometheusExporter\PrometheusExporter;
use Koeeru\PrometheusExporter\ResultStores\ResultStore;


class RunHealthChecksCommand extends Command
{
    protected $signature = 'health:check';

    protected $description = 'Run all health checks';

    /** @var array<int, Exception> */
    protected array $thrownExceptions = [];

    public function handle(): int
    {
        $this->info('Running checks...');

        $results = $this->runChecks();

        $this->storeResults($results);

        $this->line('');
        $this->info('All done!');

        return $this->determineCommandResult($results);
    }

    public function runCheck(Check $check): Result
    {
        try {
            $this->line('');
            $this->line("Running check: {$check->getLabel()}...");
            $result = $check->run();
        } catch (Exception $exception) {
            $exception = CheckDidNotComplete::make($check, $exception);
            report($exception);

            $this->thrownExceptions[] = $exception;

            $result = $check->markAsCrashed();
        }

        $result
            ->check($check)
            ->endedAt(now());

        $this->outputResult($result, $exception ?? null);


        return $result;
    }

    /** @return Collection<int, Result> */
    protected function runChecks(): Collection
    {
        return app(PrometheusExporter::class)
            ->registeredChecks()
            ->map(function (Check $check): Result {
                return $check->shouldRun()
                    ? $this->runCheck($check)
                    : (new Result(Status::skipped))->check($check)->endedAt(now());
            });
    }

    protected function storeResults(Collection $results): self
    {
        app(PrometheusExporter::class)
            ->resultStores()
            ->each(fn (ResultStore $store) => $store->save($results));

        return $this;
    }

    protected function outputResult(Result $result, ?Exception $exception = null): void
    {
        $status = ucfirst((string) $result->status->value);

        $okMessage = $status;

        if (! empty($result->shortSummary)) {
            $okMessage .= ": {$result->shortSummary}";
        }

        match ($result->status) {
            Status::ok => $this->info($okMessage),
            Status::warning => $this->comment("{$status}: {$result->getNotificationMessage()}"),
            Status::failed => $this->error("{$status}: {$result->getNotificationMessage()}"),
            Status::crashed => $this->error("{$status}}: `{$exception?->getMessage()}`"),
            default => null,
        };
    }

    protected function determineCommandResult(Collection $results): int
    {

        if (count($this->thrownExceptions)) {
            return self::FAILURE;
        }

        $containsFailingCheck = $results->contains(function (Result $result) {
            return in_array($result->status, [
                Status::crashed,
                Status::failed,
                Status::warning,
            ]);
        });

        return $containsFailingCheck
            ? self::FAILURE
            : self::SUCCESS;
    }
}
