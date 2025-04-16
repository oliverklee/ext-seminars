<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Localization;

use OliverKlee\Seminars\Localization\TranslateTrait;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Localization\TranslateTrait
 */
final class TranslateTraitTest extends FunctionalTestCase
{
    use TranslateTrait;

    protected array $testExtensionsToLoad = [
        'sjbr/static-info-tables',
        'oliverklee/feuserextrafields',
        'oliverklee/oelib',
        'oliverklee/seminars',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpLanguageService();
    }

    public function setUpLanguageService(): void
    {
        $languageService = GeneralUtility::makeInstance(LanguageServiceFactory::class)->create('default');
        $languageService->includeLLFile('EXT:seminars/Resources/Private/Language/locallang.xlf');

        $GLOBALS['LANG'] = $languageService;
    }

    /**
     * @test
     */
    public function translateWithoutMatchReturnsKey(): void
    {
        $key = 'translation-without-match';

        $result = $this->translate($key);

        self::assertSame($key, $result);
    }

    /**
     * @test
     */
    public function translateWithMatchReturnsLocalizedString(): void
    {
        $result = $this->translate('test-label');

        self::assertSame('This is a test label.', $result);
    }
}
