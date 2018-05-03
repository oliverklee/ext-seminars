<?php

return [
    'web_seminars' => [
        'path' => '/seminars/configuration/',
        'target' => \OliverKlee\Seminars\Controller\ConfigurationController::class . '::mainAction',
    ],
];
