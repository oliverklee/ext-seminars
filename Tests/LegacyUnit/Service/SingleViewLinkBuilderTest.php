<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Service;

use OliverKlee\Oelib\Configuration\Configuration;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Templating\TemplateHelper;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\Service\TestingSingleViewLinkBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

final class SingleViewLinkBuilderTest extends TestCase
{
    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * backup of $_POST
     *
     * @var array
     */
    private $postBackup;

    /**
     * backup of $_GET
     *
     * @var array
     */
    private $getBackup;

    /**
     * backup of $GLOBALS['TYPO3_CONF_VARS']
     *
     * @var array
     */
    private $typo3confVarsBackup;

    protected function setUp()
    {
        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->postBackup = $_POST;
        $this->getBackup = $_GET;
        $this->typo3confVarsBackup = $GLOBALS['TYPO3_CONF_VARS'];

        ConfigurationRegistry::getInstance()->set('plugin.tx_seminars_pi1', new Configuration());
    }

    protected function tearDown()
    {
        $GLOBALS['TYPO3_CONF_VARS'] = $this->typo3confVarsBackup;
        $_GET = $this->getBackup;
        $_POST = $this->postBackup;

        $this->testingFramework->cleanUp();
    }

    private function getFrontEndController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    // Tests concerning getSingleViewPageForEvent

    /**
     * @test
     */
    public function getSingleViewPageForEventForEventWithSingleViewPageAndNoConfigurationReturnsSingleViewPageOfEvent()
    {
        /** @var \Tx_Seminars_Model_Event&MockObject $event */
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['hasCombinedSingleViewPage', 'getCombinedSingleViewPage']
        );
        $event->method('hasCombinedSingleViewPage')
            ->willReturn(true);
        $event->method('getCombinedSingleViewPage')
            ->willReturn('42');

        /** @var TestingSingleViewLinkBuilder&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingSingleViewLinkBuilder::class,
            [
                'configurationHasSingleViewPage',
                'getSingleViewPageFromConfiguration',
            ]
        );
        $subject->method('configurationHasSingleViewPage')
            ->willReturn(false);
        $subject
            ->method('getSingleViewPageFromConfiguration')
            ->willReturn(0);

        self::assertEquals(
            '42',
            $subject->getSingleViewPageForEvent($event)
        );
    }

    /**
     * @test
     */
    public function getSingleViewPageForEventForEventWithoutSingleViewPageReturnsSingleViewPageFromConfiguration()
    {
        /** @var \Tx_Seminars_Model_Event&MockObject $event */
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['hasCombinedSingleViewPage', 'getCombinedSingleViewPage']
        );
        $event->method('hasCombinedSingleViewPage')
            ->willReturn(false);
        $event->method('getCombinedSingleViewPage')
            ->willReturn('');

        /** @var TestingSingleViewLinkBuilder&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingSingleViewLinkBuilder::class,
            [
                'configurationHasSingleViewPage',
                'getSingleViewPageFromConfiguration',
            ]
        );
        $subject->method('configurationHasSingleViewPage')
            ->willReturn(true);
        $subject
            ->method('getSingleViewPageFromConfiguration')
            ->willReturn(91);

        self::assertEquals(
            '91',
            $subject->getSingleViewPageForEvent($event)
        );
    }

    /**
     * @test
     */
    public function getSingleViewPageForEventForEventAndConfigurationWithSingleViewPageReturnsSingleViewPageFromEvent()
    {
        /** @var \Tx_Seminars_Model_Event&MockObject $event */
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['hasCombinedSingleViewPage', 'getCombinedSingleViewPage']
        );
        $event->method('hasCombinedSingleViewPage')
            ->willReturn(true);
        $event->method('getCombinedSingleViewPage')
            ->willReturn('42');

        /** @var TestingSingleViewLinkBuilder&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingSingleViewLinkBuilder::class,
            [
                'configurationHasSingleViewPage',
                'getSingleViewPageFromConfiguration',
            ]
        );
        $subject->method('configurationHasSingleViewPage')
            ->willReturn(true);
        $subject
            ->method('getSingleViewPageFromConfiguration')
            ->willReturn(91);

        self::assertEquals(
            '42',
            $subject->getSingleViewPageForEvent($event)
        );
    }

    /**
     * @test
     */
    public function getSingleViewPageForEventForEventWithoutSingleViewPageAndConfigurationWithoutSettingIsEmpty()
    {
        /** @var \Tx_Seminars_Model_Event&MockObject $event */
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['hasCombinedSingleViewPage', 'getCombinedSingleViewPage']
        );
        $event->method('hasCombinedSingleViewPage')
            ->willReturn(false);
        $event->method('getCombinedSingleViewPage')
            ->willReturn('');

        /** @var TestingSingleViewLinkBuilder&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingSingleViewLinkBuilder::class,
            [
                'configurationHasSingleViewPage',
                'getSingleViewPageFromConfiguration',
            ]
        );
        $subject->method('configurationHasSingleViewPage')
            ->willReturn(false);
        $subject
            ->method('getSingleViewPageFromConfiguration')
            ->willReturn(0);

        self::assertEquals(
            '',
            $subject->getSingleViewPageForEvent($event)
        );
    }

    ////////////////////////////////////////////////////
    // Tests concerning configurationHasSingleViewPage
    ////////////////////////////////////////////////////

    /**
     * @test
     */
    public function configurationHasSingleViewPageForZeroPageFromConfigurationReturnsFalse()
    {
        /** @var TestingSingleViewLinkBuilder&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingSingleViewLinkBuilder::class,
            ['getSingleViewPageFromConfiguration']
        );
        $subject
            ->method('getSingleViewPageFromConfiguration')
            ->willReturn(0);

        self::assertFalse(
            $subject->configurationHasSingleViewPage()
        );
    }

    /**
     * @test
     */
    public function configurationHasSingleViewPageForNonZeroPageFromConfigurationReturnsTrue()
    {
        /** @var TestingSingleViewLinkBuilder&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingSingleViewLinkBuilder::class,
            ['getSingleViewPageFromConfiguration']
        );
        $subject
            ->method('getSingleViewPageFromConfiguration')
            ->willReturn(42);

        self::assertTrue(
            $subject->configurationHasSingleViewPage()
        );
    }

    ////////////////////////////////////////////////////////
    // Tests concerning getSingleViewPageFromConfiguration
    ////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function getSingleViewPageFromConfigurationForPluginSetReturnsPageUidFromPluginConfiguration()
    {
        /** @var TemplateHelper&MockObject $plugin */
        $plugin = $this->createPartialMock(TemplateHelper::class, ['hasConfValueInteger', 'getConfValueInteger']);
        $plugin->method('hasConfValueInteger')->willReturn(true);
        $plugin->method('getConfValueInteger')->with('detailPID')->willReturn(42);

        $subject = new TestingSingleViewLinkBuilder();
        $subject->setPlugin($plugin);

        self::assertEquals(
            42,
            $subject->getSingleViewPageFromConfiguration()
        );
    }

    /**
     * @test
     */
    public function getSingleViewPageFromConfigurationForNoPluginSetReturnsPageUidFromTypoScriptSetup()
    {
        /** @var Configuration $configuration */
        $configuration = ConfigurationRegistry::get('plugin.tx_seminars_pi1');
        $configuration->set('detailPID', 91);

        $subject = new TestingSingleViewLinkBuilder();

        self::assertEquals(
            91,
            $subject->getSingleViewPageFromConfiguration()
        );
    }

    // Tests concerning createAbsoluteUrlForEvent and createRelativeUrlForEvent

    /**
     * @test
     */
    public function createAbsoluteUrlForEventReturnsRelativeUrlMadeAbsolute()
    {
        $relativeUrl = 'index.php?id=42&tx_seminars%5BshowUid%5D=17';
        /** @var \Tx_Seminars_Model_Event&MockObject $event */
        $event = $this->createMock(\Tx_Seminars_Model_Event::class);

        /** @var TestingSingleViewLinkBuilder&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingSingleViewLinkBuilder::class,
            ['createRelativeUrlForEvent']
        );
        $subject->method('createRelativeUrlForEvent')
            ->willReturn($relativeUrl);

        self::assertEquals(
            GeneralUtility::locationHeaderUrl($relativeUrl),
            $subject->createAbsoluteUrlForEvent($event)
        );
    }

    /**
     * @test
     */
    public function createRelativeUrlForEventCreatesUrlViaTslibContent()
    {
        $eventUid = 19;
        $singleViewPageUid = 42;

        /** @var \Tx_Seminars_Model_Event&MockObject $event */
        $event = $this->createPartialMock(\Tx_Seminars_Model_Event::class, ['getUid']);
        $event->method('getUid')
            ->willReturn($eventUid);

        $contentObject = $this->createPartialMock(ContentObjectRenderer::class, ['typoLink_URL']);
        $contentObject->expects(self::once())->method('typoLink_URL')
            ->with(
                [
                    'parameter' => (string)$singleViewPageUid,
                    'additionalParams' => '&tx_seminars_pi1%5BshowUid%5D=' . $eventUid,
                ]
            );

        /** @var TestingSingleViewLinkBuilder&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingSingleViewLinkBuilder::class,
            ['getContentObject', 'getSingleViewPageForEvent']
        );
        $subject->method('getContentObject')
            ->willReturn($contentObject);
        $subject
            ->method('getSingleViewPageForEvent')
            ->willReturn($singleViewPageUid);

        $subject->createRelativeUrlForEvent($event);
    }

    /**
     * @test
     */
    public function createRelativeUrlReturnsUrlFromTypolinkUrl()
    {
        $relativeUrl = 'index.php?id=42&tx_seminars%5BshowUid%5D=17';

        $contentObject = $this->createPartialMock(ContentObjectRenderer::class, ['typoLink_URL']);
        $contentObject->expects(self::once())->method('typoLink_URL')
            ->willReturn($relativeUrl);

        /** @var TestingSingleViewLinkBuilder&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingSingleViewLinkBuilder::class,
            ['getContentObject', 'getSingleViewPageForEvent']
        );
        $subject->method('getContentObject')
            ->willReturn($contentObject);

        /** @var \Tx_Seminars_Model_Event&MockObject $event */
        $event = $this->createMock(\Tx_Seminars_Model_Event::class);

        self::assertEquals(
            $relativeUrl,
            $subject->createRelativeUrlForEvent($event)
        );
    }

    /**
     * @test
     */
    public function createAbsoluteUrlForEventWithExternalDetailsPageAddsProtocolAndNoSeminarParameter()
    {
        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['details_page' => 'www.example.com']);

        $subject = new TestingSingleViewLinkBuilder();

        self::assertEquals(
            'http://www.example.com',
            $subject->createAbsoluteUrlForEvent($event)
        );
    }

    /**
     * @test
     */
    public function createAbsoluteUrlForEventWithInternalDetailsPageAddsSeminarParameter()
    {
        $pageUid = $this->testingFramework->createFrontEndPage();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['details_page' => $pageUid]);

        $subject = new TestingSingleViewLinkBuilder();

        self::assertStringContainsString(
            '?id=' . $pageUid . '&tx_seminars_pi1%5BshowUid%5D=' . $event->getUid(),
            $subject->createAbsoluteUrlForEvent($event)
        );
    }

    // Tests concerning getContentObject

    /**
     * @test
     */
    public function getContentObjectForAvailableFrontEndReturnsFrontEndContentObject()
    {
        $this->testingFramework->createFakeFrontEnd();

        $subject = new TestingSingleViewLinkBuilder();

        self::assertSame(
            $this->getFrontEndController()->cObj,
            $subject->getContentObject()
        );
    }

    /**
     * @test
     */
    public function getContentObjectForNoFrontEndReturnsContentObjectRenderer()
    {
        $subject = new TestingSingleViewLinkBuilder();

        self::assertInstanceOf(ContentObjectRenderer::class, $subject->getContentObject());
    }
}
