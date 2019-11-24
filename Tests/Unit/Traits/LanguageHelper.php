<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Traits;

use TYPO3\CMS\Lang\LanguageService;

/**
 * This trait provides methods useful for initializing languages.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
trait LanguageHelper
{
    /**
     * @var LanguageService
     */
    private $languageService = null;

    private function getLanguageService(): LanguageService
    {
        if ($this->languageService === null) {
            $languageService = new LanguageService();
            $languageService->init('default');
            $languageService->includeLLFile('EXT:seminars/Resources/Private/Language/locallang.xlf');
            $this->languageService = $languageService;
        }

        return $this->languageService;
    }
}
