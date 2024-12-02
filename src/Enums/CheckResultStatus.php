<?php

namespace Koeeru\PrometheusExporter\Enums;

enum CheckResultStatus: int
{
    case OK = 0;
    case WARNING = 1;
    case FAILED = 2;
    case UNKNOWN = 3;

    public static function fromName(string $name)
    {
        return constant("self::$name");
    }
}
