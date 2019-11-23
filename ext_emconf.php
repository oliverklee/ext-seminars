<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Seminar Manager',
    'description' => 'Allows you to create and manage a list of seminars, workshops, lectures, theater performances and other events, allowing front-end users to sign up. FE users also can create and edit events.',
    'version' => '2.2.1',
    'category' => 'plugin',
    'constraints' => [
        'depends' => [
            'php' => '7.0.0-7.2.99',
            'typo3' => '8.7.0-9.5.99',
            'static_info_tables' => '6.7.5-',
            'oelib' => '3.0.1-3.99.99',
            'mkforms' => '3.0.21-9.5.99',
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
    'uploadfolder' => true,
    'createDirs' => 'uploads/tx_seminars/',
    'clearCacheOnLoad' => true,
    'author' => 'Oliver Klee',
    'author_email' => 'typo3-coding@oliverklee.de',
    'author_company' => 'oliverklee.de',
    'autoload' => [
        'classmap' => [
            'Classes',
        ],
    ],
    'autoload-dev' => [
        'classmap' => [
            'Tests',
        ],
    ],
];
