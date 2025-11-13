<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Controller\BackEnd;

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @phpstan-require-extends UnitTestCase
 */
trait BackEndControllerTestHelper
{
    /**
     * @var LanguageService&MockObject
     */
    private LanguageService $languageServiceMock;

    /**
     * Note: This is a real mockfest. We need to convert the BE controller tests to functional tests first.
     */
    private function createModuleTemplateFactory(): ModuleTemplateFactory
    {
        GeneralUtility::addInstance(StandaloneView::class, $this->createStub(StandaloneView::class));
        $packageManagerMock = $this->createMock(PackageManager::class);
        $packageManagerMock->method('getActivePackages')->willReturn([]);
        GeneralUtility::setSingletonInstance(PackageManager::class, $packageManagerMock);
        $this->languageServiceMock = $this->createMock(LanguageService::class);
        $this->languageServiceMock->lang = 'default';
        $GLOBALS['LANG'] = $this->languageServiceMock;
        $extensionConfigurationMock = $this->createMock(ExtensionConfiguration::class);
        $extensionConfigurationMock->method('get')->with('backend', 'backendFavicon')->willReturn('icon.svg');
        GeneralUtility::addInstance(ExtensionConfiguration::class, $extensionConfigurationMock);
        $GLOBALS['BE_USER'] = $this->createStub(BackendUserAuthentication::class);

        $pageRenderMock = $this->createMock(PageRenderer::class);
        $pageRenderMock->method('getLanguage')->willReturn('default');

        return new ModuleTemplateFactory(
            $pageRenderMock,
            $this->createStub(IconFactory::class),
            $this->createStub(FlashMessageService::class),
        );
    }
}
