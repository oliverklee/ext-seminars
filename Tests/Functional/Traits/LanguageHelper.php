<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Traits;

use OliverKlee\Oelib\System\Typo3Version;
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
            if (Typo3Version::isAtLeast(10)) {
                $languageService = LanguageService::create('default');
            } else {
                // @phpstan-ignore-next-line This line is for TYPO3 9LTS only, and we currently are on 10LTS.
                $languageService = new LanguageService();
                $languageService->init('default');
            }
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
