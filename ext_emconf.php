<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Seminar Manager',
    'description' => 'Allows you to create and manage a list of seminars, workshops, lectures, theater performances and other events, allowing front-end users to sign up. FE users also can create and edit events.',
    'version' => '3.4.0',
    'category' => 'plugin',
    'constraints' => [
        'depends' => [
            'php' => '7.2.0-8.0.99',
            'typo3' => '9.5.0-9.5.99',
            'oelib' => '3.6.3-4.99.99',
            'mkforms' => '9.5.2-9.99.99',
            'rn_base' => '1.13.13-1.99.99',
            'static_info_tables' => '6.9.5-6.99.99',
        ],
        'conflicts' => [
            'sourceopt' => '',
        ],
        'suggests' => [
            'femanager' => '5.1.0-',
            'onetimeaccount' => '',
            'sr_feuser_register' => '5.1.0-',
        ],
    ],
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'author' => 'Oliver Klee',
    'author_email' => 'typo3-coding@oliverklee.de',
    'author_company' => 'oliverklee.de',
    'autoload' => [
        'psr-4' => [
            'OliverKlee\\Seminars\\' => 'Classes/',
        ],
    ],
    'autoload-dev' => [
        'psr-4' => [
            'OliverKlee\\Seminars\\Tests\\' => 'Tests/',
        ],
    ],
];
