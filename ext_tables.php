<?php
defined('TYPO3_MODE') or die('Access denied.');

$boot = static function (): void {
    // The legacy BE module will be removed before our 11LTS-compatible release.
    if ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() >= 11) {
        return;
    }

    $moduleConfiguration = [
        'routeTarget' => \OliverKlee\Seminars\BackEnd\Controller::class . '::mainAction',
        'access' => 'user,group',
        'name' => 'web_seminars',
        'labels' => [
            'll_ref' => 'LLL:EXT:seminars/Resources/Private/Language/locallang.xlf',
        ],
        'icon' => 'EXT:seminars/Resources/Public/Icons/BackEndModule.gif',
    ];

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('web', 'seminars', '', '', $moduleConfiguration);
};

$boot();
unset($boot);
