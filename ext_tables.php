<?php
defined('TYPO3_MODE') or die('Access denied.');

$boot = static function () {
    $moduleConfiguration = [
        'routeTarget' => \OliverKlee\Seminars\BackEnd\Controller::class . '::mainAction',
        'access' => 'user,group',
        'name' => 'web_seminars',
        'labels' => [
            'll_ref' => 'LLL:EXT:seminars/Resources/Private/Language/BackEnd/locallang_mod.xlf',
        ],
        'icon' => 'EXT:seminars/Resources/Public/Icons/BackEndModule.gif',
    ];

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('web', 'seminars', '', '', $moduleConfiguration);
};

$boot();
unset($boot);
