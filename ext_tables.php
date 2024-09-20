<?php

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use OliverKlee\Seminars\Controller\BackEnd\ModuleController;
use OliverKlee\Seminars\Controller\BackEnd\EventController;
use OliverKlee\Seminars\Controller\BackEnd\RegistrationController;
use OliverKlee\Seminars\Controller\BackEnd\EmailController;

defined('TYPO3') or die('Access denied.');

$boot = static function (): void {
    /**
     * BE module
     */
    ExtensionUtility::registerModule(
        'Seminars',
        'web',
        'events',
        '',
        [
            ModuleController::class => 'overview',
            // @deprecated `exportCsv` will be removed in version 6.0.0 in #3134
            EventController::class
            => 'exportCsv, hide, unhide, delete, search, duplicate',
            RegistrationController::class
            => 'showForEvent, exportCsvForEvent, exportCsvForPageUid, delete',
            EmailController::class => 'compose, send',
        ],
        [
            'access' => 'user,group',
            'icon' => 'EXT:seminars/Resources/Public/Icons/BackEndModule.svg',
            'labels' => 'LLL:EXT:seminars/Resources/Private/Language/locallang.xlf',
        ]
    );
};

$boot();
unset($boot);
