<?php

namespace Koeeru\PrometheusExporter\Checks\Checks;

use Koeeru\PrometheusExporter\Checks\Check;
use Koeeru\PrometheusExporter\Checks\Result;

use function app;

class EnvironmentCheck extends Check
{
    protected string $expectedEnvironment = 'production';

    public function expectEnvironment(string $expectedEnvironment): self
    {
        $this->expectedEnvironment = $expectedEnvironment;

        return $this;
    }

    public function run(): Result
    {
        $actualEnvironment = (string) app()->environment();

        $result = Result::make()
            ->meta([
                'handle' => app()->environment(),
                'actual' => $actualEnvironment,
                'expected' => $this->expectedEnvironment,
            ])
            ->shortSummary($actualEnvironment);

        return $this->expectedEnvironment === $actualEnvironment
            ? $result->ok()
            : $result->failed('The environment was expected to be `:expected`, but actually was `:actual`');
    }
}
