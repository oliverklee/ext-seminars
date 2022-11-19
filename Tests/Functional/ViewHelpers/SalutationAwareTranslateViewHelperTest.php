<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\ViewHelpers;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;

/**
 * @covers \OliverKlee\Seminars\ViewHelpers\SalutationAwareTranslateViewHelper
 */
final class SalutationAwareTranslateViewHelperTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var VariableProviderInterface
     */
    private $variableProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpLanguageService();
        $this->variableProvider = new StandardVariableProvider();
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

    private function embedInHtmlWithNamespace(string $html): string
    {
        return '<html xmlns:s="OliverKlee\Seminars\ViewHelpers" data-namespace-typo3-fluid="true">' .
            $html . '</html>';
    }

    private function renderViewHelper(string $html): string
    {
        $this->setUpBackendUserFromFixture(1);

        $view = new StandaloneView();

        $renderingContext = $view->getRenderingContext();
        $renderingContext->setVariableProvider($this->variableProvider);
        $view->setRenderingContext($renderingContext);

        $view->setTemplateSource($this->embedInHtmlWithNamespace($html));

        return $view->render();
    }

    /**
     * @test
     */
    public function renderForInexistentLabelKeyReturnsLabelKey(): void
    {
        $key = 'translation-without-match';
        $result = $this->renderViewHelper('<s:salutationAwareTranslate key="' . $key . '" />');

        self::assertSame($key, $result);
    }

    /**
     * @test
     */
    public function renderRendersViewHelperArguments(): void
    {
        $result = $this->renderViewHelper(
            '<s:salutationAwareTranslate key="test-label-with-arguments" arguments="{0: \'Oli\'}"/>'
        );

        self::assertSame('Hello Oli!', $result);
    }

    /**
     * @test
     */
    public function renderWithoutAnySettingsWithFullLabelPathCanRenderLabelWithoutSalutationVersions(): void
    {
        $result = $this->renderViewHelper(
            '<s:salutationAwareTranslate key="LLL:EXT:seminars/Resources/Private/Language/locallang.xlf:test-label" />'
        );

        self::assertSame('This is a test label.', $result);
    }

    /**
     * @test
     */
    public function renderWithoutAnySettingsWithShortLabelPathCanRenderLabelWithoutSalutationVersions(): void
    {
        $result = $this->renderViewHelper('<s:salutationAwareTranslate key="test-label" />');

        self::assertSame('This is a test label.', $result);
    }

    /**
     * @test
     */
    public function renderWithoutAnySettingsForLabelWithSalutationByDefaultRendersFormalLabel(): void
    {
        $result = $this->renderViewHelper('<s:salutationAwareTranslate key="test-label-with-salutation" />');

        self::assertSame('This is a test label with a formal salutation.', $result);
    }

    /**
     * @test
     */
    public function renderWithoutAnySettingsForLabelWithSalutationAndFallbackByDefaultRendersFormalLabel(): void
    {
        $result = $this->renderViewHelper(
            '<s:salutationAwareTranslate key="test-label-with-salutation-and-fallback" />'
        );

        self::assertSame('This is a test label with a fallback and a formal salutation.', $result);
    }

    /**
     * @test
     */
    public function renderWithEmptySettingsForLabelWithSalutationByDefaultRendersFormalLabel(): void
    {
        $this->variableProvider->add('settings', []);

        $result = $this->renderViewHelper('<s:salutationAwareTranslate key="test-label-with-salutation" />');

        self::assertSame('This is a test label with a formal salutation.', $result);
    }

    /**
     * @return array<string, array{0: 'formal'|'informal', 1: non-empty-string}>
     */
    public function salutationDataProvider(): array
    {
        return [
            'formal salutation' => ['formal', 'This is a test label with a formal salutation.'],
            'informal salutation' => ['informal', 'This is a test label with an informal salutation.'],
        ];
    }

    /**
     * @test
     * @param non-empty-string $salutation
     * @dataProvider salutationDataProvider
     */
    public function renderWithSalutationInSettingsForLabelWithSalutationByRendersLabelWithGivenSalutationMode(
        string $salutation,
        string $expectedResult
    ): void {
        $this->variableProvider->add('settings', ['salutation' => $salutation]);

        $result = $this->renderViewHelper('<s:salutationAwareTranslate key="test-label-with-salutation" />');

        self::assertSame($expectedResult, $result);
    }

    /**
     * @test
     * @param non-empty-string $salutation
     * @dataProvider salutationDataProvider
     */
    public function renderWithSalutationInSettingsForLabelWithoutSalutationByRendersLabelWithoutSalutation(
        string $salutation
    ): void {
        $this->variableProvider->add('settings', ['salutation' => $salutation]);

        $result = $this->renderViewHelper('<s:salutationAwareTranslate key="test-label" />');

        self::assertSame('This is a test label.', $result);
    }

    /**
     * @test
     * @param non-empty-string $salutation
     * @dataProvider salutationDataProvider
     */
    public function renderWithSalutationInSettingsForInexistentLabelKeyReturnsLabelKey(string $salutation): void
    {
        $this->variableProvider->add('settings', ['salutation' => $salutation]);

        $key = 'translation-without-match';
        $result = $this->renderViewHelper('<s:salutationAwareTranslate key="' . $key . '" />');

        self::assertSame($key, $result);
    }
}
