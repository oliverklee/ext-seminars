<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die('Access denied.');

ExtensionManagementUtility::addStaticFile('seminars', 'Configuration/TypoScript', 'Seminars');
