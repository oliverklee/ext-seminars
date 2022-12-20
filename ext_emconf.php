<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Seminar Manager',
    'description' => 'Allows you to create and manage a list of seminars, workshops, lectures, theater performances and other events, allowing front-end users to sign up. FE users also can create and edit events.',
    'version' => '5.1.0',
    'category' => 'plugin',
    'constraints' => [
        'depends' => [
            'php' => '7.2.0-8.2.99',
            'typo3' => '10.4.22-11.5.99',
            'extbase' => '10.4.22-11.5.99',
            'feuserextrafields' => '5.2.2-5.99.99',
            'oelib' => '5.1.0-5.99.99',
            'static_info_tables' => '6.9.6-11.5.99',
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
