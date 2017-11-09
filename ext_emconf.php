<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "seminars".
 *
 * Auto generated 18-01-2015 16:48
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'Seminar Manager',
    'description' => 'This extension allows you to create and manage a list of seminars, workshops, lectures, theater performances and other events, allowing front-end users to sign up. FE users also can create and edit events.',
    'category' => 'plugin',
    'shy' => 0,
    'dependencies' => 'static_info_tables,oelib,mkforms',
    'conflicts' => 'sourceopt',
    'priority' => '',
    'loadOrder' => '',
    'module' => 'BackEnd',
    'state' => 'stable',
    'internal' => 0,
    'uploadfolder' => 1,
    'createDirs' => '',
    'modify_tables' => 'be_groups,fe_groups,fe_users',
    'clearCacheOnLoad' => 1,
    'lockType' => '',
    'author' => 'Oliver Klee',
    'author_email' => 'typo3-coding@oliverklee.de',
    'author_company' => 'oliverklee.de',
    'CGLcompliance' => '',
    'CGLcompliance_note' => '',
    'version' => '1.2.1',
    '_md5_values_when_last_written' => '',
    'constraints' => [
        'depends' => [
            'php' => '5.5.0-7.0.99',
            'typo3' => '6.2.0-7.9.99',
            'static_info_tables' => '6.2.0-',
            'oelib' => '1.0.0-1.9.99',
            'mkforms' => '2.0.0-3.99.99',
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
        'classmap' => ['Classes', 'Tests'],
    ],
];
