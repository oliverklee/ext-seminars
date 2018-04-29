<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Seminar Manager',
    'description' => 'Allows you to create and manage a list of seminars, workshops, lectures, theater performances and other events, allowing front-end users to sign up. FE users also can create and edit events.',
    'category' => 'plugin',
    'shy' => 0,
    'dependencies' => 'static_info_tables,oelib,mkforms',
    'conflicts' => 'sourceopt',
    'priority' => '',
    'loadOrder' => '',
    'module' => 'BackEnd',
    'state' => 'stable',
    'internal' => 0,
    'createDirs' => 'uploads/tx_seminars/',
    'modify_tables' => 'be_groups,fe_groups,fe_users',
    'clearCacheOnLoad' => 1,
    'lockType' => '',
    'author' => 'Oliver Klee',
    'author_email' => 'typo3-coding@oliverklee.de',
    'author_company' => 'oliverklee.de',
    'CGLcompliance' => '',
    'CGLcompliance_note' => '',
    'version' => '1.5.0',
    '_md5_values_when_last_written' => '',
    'constraints' => [
        'depends' => [
            'php' => '5.6.0-7.0.99',
            'typo3' => '7.6.0-7.9.99',
            'static_info_tables' => '6.4.0-',
            'oelib' => '2.0.0-2.9.99',
            'mkforms' => '3.0.14-3.99.99',
        ],
        'conflicts' => [
            'sourceopt' => '',
        ],
        'suggests' => [
            'onetimeaccount' => '',
            'sr_feuser_register' => '',
        ],
    ],
    'autoload' => [
        'classmap' => [
            'Classes',
            'Tests',
        ],
    ],
];
