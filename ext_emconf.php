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

$EM_CONF[$_EXTKEY] = array(
    'title' => 'Seminar Manager',
    'description' => 'This extension allows you to create and manage a list of seminars, workshops, lectures, theater performances and other events, allowing front-end users to sign up. FE users also can create and edit events.',
    'category' => 'plugin',
    'shy' => 0,
    'dependencies' => 'cms,oelib,ameos_formidable,static_info_tables',
    'conflicts' => 'dbal,sourceopt',
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
    'version' => '0.10.56',
    '_md5_values_when_last_written' => '',
    'constraints' => array(
        'depends' => array(
            'php' => '5.5.0-7.0.99',
            'typo3' => '6.2.0-7.9.99',
            'cms' => '',
            'oelib' => '0.9.0-1.9.99',
            'ameos_formidable' => '1.1.564-1.9.99',
            'static_info_tables' => '6.2.0-',
        ),
        'conflicts' => array(
            'dbal' => '',
            'sourceopt' => '',
        ),
        'suggests' => array(
            'onetimeaccount' => '',
            'sr_feuser_register' => '',
        ),
    ),
    'suggests' => array(
    ),
);
