<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Traits;

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
    private $languageService = null;

    private function getLanguageService(): LanguageService
    {
        if (!$this->languageService instanceof LanguageService) {
            if (Typo3Version::isAtLeast(10)) {
                // @phpstan-ignore-next-line This line is for TYPO3 10LTS only, and we currently are on 9LTS.
                $languageService = LanguageService::create('default');
            } else {
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
}
