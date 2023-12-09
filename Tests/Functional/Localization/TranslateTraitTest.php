<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Localization;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Localization\TranslateTrait;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @covers \OliverKlee\Seminars\Localization\TranslateTrait
 */
final class TranslateTraitTest extends FunctionalTestCase
{
    use TranslateTrait;

    protected $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpLanguageService();
    }

    public function setUpLanguageService(): void
    {
        if ((new Typo3Version())->getMajorVersion() >= 11) {
            $languageService = GeneralUtility::makeInstance(LanguageServiceFactory::class)->create('default');
        } else {
            $languageService = LanguageService::create('default');
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
