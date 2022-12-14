<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Traits;

use TYPO3\CMS\Core\Localization\LanguageService;

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
            $languageService = LanguageService::create('default');
            $languageService->includeLLFile('EXT:seminars/Resources/Private/Language/locallang.xlf');
            $this->languageService = $languageService;
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
