<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\FrontEnd;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @covers \Tx_Seminars_FrontEnd_Countdown
 */
final class CountdownTest extends TestCase
{
    /**
     * @var \Tx_Seminars_FrontEnd_Countdown
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var \Tx_Seminars_Mapper_Event&MockObject
     */
    private $mapper = null;

    protected function setUp()
    {
        $configurationRegistry = ConfigurationRegistry::getInstance();
        $configurationRegistry->set('config', new DummyConfiguration());
        $configurationRegistry->set('page.config', new DummyConfiguration());
        $configurationRegistry->set('plugin.tx_seminars._LOCAL_LANG.default', new DummyConfiguration());

        $this->testingFramework = new TestingFramework('tx_seminars');
        $this->testingFramework->createFakeFrontEnd();

        /** @var \Tx_Seminars_Mapper_Event&MockObject $mapper */
        $mapper = $this->getMockBuilder(\Tx_Seminars_Mapper_Event::class)->setMethods(['findNextUpcoming'])->getMock();
        $this->mapper = $mapper;

        $this->subject = new \Tx_Seminars_FrontEnd_Countdown(
            [
                'isStaticTemplateLoaded' => 1,
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
            ],
            $this->getFrontEndController()->cObj
        );
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();

        \Tx_Seminars_Service_RegistrationManager::purgeInstance();
    }

    private function getFrontEndController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    //////////////////////////////////////////
    // General tests concerning the fixture.
    //////////////////////////////////////////

    /**
     * @test
     */
    public function fixtureIsAFrontEndCountdownObject()
    {
        self::assertInstanceOf(\Tx_Seminars_FrontEnd_Countdown::class, $this->subject);
    }

    ////////////////////////////////
    // Tests for render()
    ////////////////////////////////

    /**
     * @test
     */
    public function renderWithoutCallingInjectEventMapperFirstThrowsBadMethodCallException()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('The method injectEventMapper() needs to be called first.');
        $this->expectExceptionCode(1333617194);

        $this->subject->render();
    }

    /**
     * @test
     */
    public function renderWithMapperFindNextUpcomingThrowingEmptyQueryResultExceptionReturnsNoEventsFoundMessage()
    {
        $this->subject->injectEventMapper($this->mapper);
        $this->mapper->expects(self::once())
            ->method('findNextUpcoming')
            ->will(self::throwException(new NotFoundException()));

        self::assertStringContainsString(
            'There are no upcoming events. Please come back later.',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderCallsRenderMethodOfCountdownViewHelperWithNextUpcomingEventsBeginDateAsUnixTimeStamp()
    {
        $this->subject->injectEventMapper($this->mapper);
        $event = $this->mapper->getLoadedTestingModel(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'pid' => 0,
                'title' => 'Test event',
                'begin_date' => $GLOBALS['SIM_ACCESS_TIME'] + 1000,
            ]
        );

        $this->mapper->expects(self::once())
            ->method('findNextUpcoming')
            ->willReturn($event);

        /** @var \Tx_Seminars_ViewHelper_Countdown&MockObject $viewHelper */
        $viewHelper = $this->createPartialMock(\Tx_Seminars_ViewHelper_Countdown::class, ['render']);
        $viewHelper->expects(self::once())
            ->method('render')
            ->with(self::equalTo($event->getBeginDateAsUnixTimeStamp()));

        $this->subject->injectCountDownViewHelper($viewHelper);

        $this->subject->render();
    }
}
