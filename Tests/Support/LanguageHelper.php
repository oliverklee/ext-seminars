<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Support;

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This trait provides methods useful for initializing languages.
 */
trait LanguageHelper
{
    /**
     * @var LanguageService|null
     */
    private $languageService;

    private function getLanguageService(): LanguageService
    {
        if (!$this->languageService instanceof LanguageService) {
            if ((new Typo3Version())->getMajorVersion() >= 11) {
                $languageService = GeneralUtility::makeInstance(LanguageServiceFactory::class)->create('default');
            } else {
                $languageService = LanguageService::create('default');
            }
            $languageService->includeLLFile('EXT:seminars/Resources/Private/Language/locallang.xlf');
            $this->languageService = $languageService;
            $GLOBALS['LANG'] = $languageService;
        }

        return $this->languageService;
    }

    /**
     * Sets $GLOBALS['LANG'].
     */
    private function initializeBackEndLanguage(): void
    {
        $GLOBALS['LANG'] = $this->getLanguageService();
    }

    /**
     * Convenience function for `$this->getLanguageService()->getLL()`
     *
     * @param non-empty-string $key
     */
    private function translate(string $key): string
    {
        return $this->getLanguageService()->getLL($key);
    }
}
