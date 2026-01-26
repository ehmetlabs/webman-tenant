<?php

declare(strict_types=1);

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

return [
    'default' => [
        'handlers' => [
            [
                'class' => RotatingFileHandler::class,
                'constructor' => [
                    runtime_path() . '/logs/tenant.log',
                    7,
                    Logger::DEBUG,
                ],
                'formatter' => [
                    'class' => LineFormatter::class,
                    'constructor' => [null, 'Y-m-d H:i:s', true],
                ],
            ],
        ],
    ],
];
