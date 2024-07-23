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
            \OliverKlee\Seminars\Controller\BackEnd\EventController::class => 'exportCsv, hide, unhide',
            \OliverKlee\Seminars\Controller\BackEnd\RegistrationController::class
            => 'showForEvent, exportCsvForEvent, exportCsvForPageUid',
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
