<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Seminar Manager',
    'description' => 'Allows you to create and manage a list of seminars, workshops, lectures, theater performances and other events, allowing front-end users to sign up. FE users also can create and edit events.',
    'version' => '4.4.0',
    'category' => 'plugin',
    'constraints' => [
        'depends' => [
            'php' => '7.2.0-8.0.99',
            'typo3' => '10.4.11-10.4.99',
            'extbase' => '10.4.11-10.4.99',
            'feuserextrafields' => '3.2.1-5.99.99',
            'oelib' => '4.3.1-5.99.99',
            'mkforms' => '10.1.0-11.99.99',
            'rn_base' => '1.15.0-1.15.99',
            'static_info_tables' => '6.9.5-11.5.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
            'onetimeaccount' => '',
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
