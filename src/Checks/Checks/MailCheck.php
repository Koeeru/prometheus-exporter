<?php

namespace Koeeru\PrometheusExporter\Checks\Checks;

use Illuminate\Support\Facades\Config;
use Koeeru\PrometheusExporter\Checks\Check;
use Koeeru\PrometheusExporter\Checks\Result;

class MailCheck extends Check
{
    public function run(): Result
    {
        $result = Result::make()->meta([
            'connection_name' => 'SMTP',
        ]);

        try {
            // Attempt to connect to the SMTP server
            $transport = new \Swift_SmtpTransport(
                Config::get('mail.mailers.smtp.host'),
                Config::get('mail.mailers.smtp.port')
            );
            $transport->setUsername(Config::get('mail.mailers.smtp.username'));
            $transport->setPassword(Config::get('mail.mailers.smtp.password'));
            $mailer = new \Swift_Mailer($transport);
            $mailer->getTransport()->start();

            return $result->ok();
        } catch (\Exception $e) {
            return $result->failed("Could not connect to the SMTP server: `{$e->getMessage()}`");
        }
    }
}
