<?php

declare(strict_types=1);

use App\Kernel;

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

return static function (array $context): Kernel {
    // Garantiza que APP_ENV y APP_DEBUG existan y sean consistentes
    $env = $context['APP_ENV'] ?? 'prod';
    $debug = isset($context['APP_DEBUG']) ? (bool) $context['APP_DEBUG'] : false;

    // Opcional: habilitar display_errors solo en dev
    if ($debug) {
        ini_set('display_errors', '1');
        error_reporting(E_ALL);
    } else {
        ini_set('display_errors', '0');
    }

    return new Kernel($env, $debug);
};
