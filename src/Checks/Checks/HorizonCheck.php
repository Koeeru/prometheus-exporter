<?php

namespace Koeeru\PrometheusExporter\Checks\Checks;

use Exception;
use Koeeru\PrometheusExporter\Checks\Check;
use Koeeru\PrometheusExporter\Checks\Result;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;

class HorizonCheck extends Check
{
    public function run(): Result
    {
        $result = Result::make();

        try {
            $horizon = app(MasterSupervisorRepository::class);
        } catch (Exception) {
            return $result->failed('Horizon does not seem to be installed correctly.');
        }

        $masterSupervisors = $horizon->all();

        if (count($masterSupervisors) === 0) {
            return $result
                ->failed('Horizon is not running.')
                ->shortSummary('Not running');
        }

        $masterSupervisor = $masterSupervisors[0];

        if ($masterSupervisor->status === 'paused') {
            return $result
                ->warning('Horizon is running, but the status is paused.')
                ->shortSummary('Paused');
        }

        return $result->ok()->shortSummary('Running');
    }
}
