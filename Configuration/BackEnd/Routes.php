<?php

use OliverKlee\Seminars\BackEnd\Controller;

return [
    'web_seminars' => [
        'path' => '/seminars',
        'target' => Controller::class . '::mainAction',
    ],
];
