<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Localization;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Localization\TranslateTrait;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Localization\LanguageService;

/**
 * @covers \OliverKlee\Seminars\Localization\TranslateTrait
 */
final class TranslateTraitTest extends FunctionalTestCase
{
    use TranslateTrait;

    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpLanguageService();
    }

    public function setUpLanguageService(): void
    {
        if ((new Typo3Version())->getMajorVersion() >= 10) {
            $languageService = LanguageService::create('default');
        } else {
            // @phpstan-ignore-next-line This line is for TYPO3 9LTS only, and we currently are on 10LTS.
            $languageService = new LanguageService();
            $languageService->init('default');
        }
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
