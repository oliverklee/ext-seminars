<?php

use OliverKlee\PhpUnit\TestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_Service_SingleViewLinkBuilderTest extends TestCase
{
    /**
     * @var \Tx_Oelib_TestingFramework
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
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');

        $this->postBackup = $_POST;
        $this->getBackup = $_GET;
        $this->typo3confVarsBackup = $GLOBALS['TYPO3_CONF_VARS'];

        \Tx_Oelib_ConfigurationRegistry::getInstance()
            ->set('plugin.tx_seminars_pi1', new \Tx_Oelib_Configuration());
    }

    protected function tearDown()
    {
        $GLOBALS['TYPO3_CONF_VARS'] = $this->typo3confVarsBackup;
        $_GET = $this->getBackup;
        $_POST = $this->postBackup;

        $this->testingFramework->cleanUp();
    }

    ///////////////////////////////////////////////
    // Tests concerning getSingleViewPageForEvent
    ///////////////////////////////////////////////

    /**
     * @test
     */
    public function getSingleViewPageForEventForEventWithSingleViewPageAndNoConfigurationReturnsSingleViewPageFromEvent(
    ) {
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['hasCombinedSingleViewPage', 'getCombinedSingleViewPage']
        );
        $event->method('hasCombinedSingleViewPage')
            ->willReturn(true);
        $event->method('getCombinedSingleViewPage')
            ->willReturn('42');

        /** @var \Tx_Seminars_Tests_Unit_Fixtures_Service_TestingSingleViewLinkBuilder|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_Tests_Unit_Fixtures_Service_TestingSingleViewLinkBuilder::class,
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
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['hasCombinedSingleViewPage', 'getCombinedSingleViewPage']
        );
        $event->method('hasCombinedSingleViewPage')
            ->willReturn(false);
        $event->method('getCombinedSingleViewPage')
            ->willReturn('');

        /** @var \Tx_Seminars_Tests_Unit_Fixtures_Service_TestingSingleViewLinkBuilder|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_Tests_Unit_Fixtures_Service_TestingSingleViewLinkBuilder::class,
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
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['hasCombinedSingleViewPage', 'getCombinedSingleViewPage']
        );
        $event->method('hasCombinedSingleViewPage')
            ->willReturn(true);
        $event->method('getCombinedSingleViewPage')
            ->willReturn('42');

        /** @var \Tx_Seminars_Tests_Unit_Fixtures_Service_TestingSingleViewLinkBuilder|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_Tests_Unit_Fixtures_Service_TestingSingleViewLinkBuilder::class,
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
    public function getSingleViewPageForEventForEventWithoutSingleViewPageAndConfigurationWithoutSettingReturnsEmptyString(
    ) {
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['hasCombinedSingleViewPage', 'getCombinedSingleViewPage']
        );
        $event->method('hasCombinedSingleViewPage')
            ->willReturn(false);
        $event->method('getCombinedSingleViewPage')
            ->willReturn('');

        /** @var \Tx_Seminars_Tests_Unit_Fixtures_Service_TestingSingleViewLinkBuilder|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_Tests_Unit_Fixtures_Service_TestingSingleViewLinkBuilder::class,
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
        /** @var \Tx_Seminars_Tests_Unit_Fixtures_Service_TestingSingleViewLinkBuilder|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_Tests_Unit_Fixtures_Service_TestingSingleViewLinkBuilder::class,
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
        /** @var \Tx_Seminars_Tests_Unit_Fixtures_Service_TestingSingleViewLinkBuilder|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_Tests_Unit_Fixtures_Service_TestingSingleViewLinkBuilder::class,
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
        $plugin = $this->createPartialMock(
            \Tx_Oelib_TemplateHelper::class,
            ['hasConfValueInteger', 'getConfValueInteger']
        );
        $plugin->method('hasConfValueInteger')
            ->willReturn(true);
        $plugin->method('getConfValueInteger')
            ->with('detailPID')->willReturn(42);

        $subject = new \Tx_Seminars_Tests_Unit_Fixtures_Service_TestingSingleViewLinkBuilder();
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
        \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars_pi1')
            ->set('detailPID', 91);

        $subject = new \Tx_Seminars_Tests_Unit_Fixtures_Service_TestingSingleViewLinkBuilder();

        self::assertEquals(
            91,
            $subject->getSingleViewPageFromConfiguration()
        );
    }

    /////////////////////////////////////////////////////////////////////////////
    // Tests concerning createAbsoluteUrlForEvent and createRelativeUrlForEvent
    /////////////////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function createAbsoluteUrlForEventReturnsRelativeUrlMadeAbsolute()
    {
        $relativeUrl = 'index.php?id=42&tx_seminars%5BshowUid%5D=17';
        $event = $this->createMock(\Tx_Seminars_Model_Event::class);

        /** @var \Tx_Seminars_Tests_Unit_Fixtures_Service_TestingSingleViewLinkBuilder|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_Tests_Unit_Fixtures_Service_TestingSingleViewLinkBuilder::class,
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

        /** @var \Tx_Seminars_Tests_Unit_Fixtures_Service_TestingSingleViewLinkBuilder|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_Tests_Unit_Fixtures_Service_TestingSingleViewLinkBuilder::class,
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

        /** @var \Tx_Seminars_Tests_Unit_Fixtures_Service_TestingSingleViewLinkBuilder|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_Tests_Unit_Fixtures_Service_TestingSingleViewLinkBuilder::class,
            ['getContentObject', 'getSingleViewPageForEvent']
        );
        $subject->method('getContentObject')
            ->willReturn($contentObject);

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
        $event = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['details_page' => 'www.example.com']);

        $subject = new \Tx_Seminars_Tests_Unit_Fixtures_Service_TestingSingleViewLinkBuilder();

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

        $event = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['details_page' => $pageUid]);

        $subject = new \Tx_Seminars_Tests_Unit_Fixtures_Service_TestingSingleViewLinkBuilder();

        self::assertContains(
            '?id=' . $pageUid . '&tx_seminars_pi1%5BshowUid%5D=' . $event->getUid(),
            $subject->createAbsoluteUrlForEvent($event)
        );
    }

    //////////////////////////////////////
    // Tests concerning getContentObject
    //////////////////////////////////////

    /**
     * @test
     */
    public function getContentObjectForAvailableFrontEndReturnsFrontEndContentObject()
    {
        $this->testingFramework->createFakeFrontEnd();

        $subject = new \Tx_Seminars_Tests_Unit_Fixtures_Service_TestingSingleViewLinkBuilder();

        self::assertSame(
            $GLOBALS['TSFE']->cObj,
            $subject->getContentObject()
        );
    }

    /**
     * @test
     */
    public function getContentObjectForNoFrontEndReturnsContentObjectRenderer()
    {
        $subject = new \Tx_Seminars_Tests_Unit_Fixtures_Service_TestingSingleViewLinkBuilder();

        self::assertInstanceOf(ContentObjectRenderer::class, $subject->getContentObject());
    }
}
