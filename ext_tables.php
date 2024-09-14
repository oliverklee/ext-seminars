<?php
defined('TYPO3') or die('Access denied.');

$boot = static function (): void {
    /**
     * BE module
     */
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'Seminars',
        'web',
        'events',
        '',
        [
            \OliverKlee\Seminars\Controller\BackEnd\ModuleController::class => 'overview',
            // @deprecated `exportCsv` will be removed in version 6.0.0 in #3134
            \OliverKlee\Seminars\Controller\BackEnd\EventController::class
            => 'exportCsv, hide, unhide, delete, search, duplicate',
            \OliverKlee\Seminars\Controller\BackEnd\RegistrationController::class
            => 'showForEvent, exportCsvForEvent, exportCsvForPageUid, delete',
            \OliverKlee\Seminars\Controller\BackEnd\EmailController::class => 'compose, send',
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
