<?php

/**
 * Testcase.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_FrontEnd_CountdownTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Seminars_FrontEnd_Countdown
     */
    private $fixture = null;

    /**
     * @var Tx_Oelib_TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var Tx_Seminars_Mapper_Event|PHPUnit_Framework_MockObject_MockObject
     */
    private $mapper = null;

    /**
     * @var Tx_Seminars_ViewHelper_Countdown|PHPUnit_Framework_MockObject_MockObject
     */
    private $viewHelper = null;

    protected function setUp()
    {
        $configurationRegistry = Tx_Oelib_ConfigurationRegistry::getInstance();
        $configurationRegistry->set('config', new Tx_Oelib_Configuration());
        $configurationRegistry->set('page.config', new Tx_Oelib_Configuration());
        $configurationRegistry->set('plugin.tx_seminars._LOCAL_LANG.default', new Tx_Oelib_Configuration());

        Tx_Oelib_ConfigurationProxy::getInstance('seminars')->setAsBoolean('enableConfigCheck', false);

        $this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');
        $this->testingFramework->createFakeFrontEnd();

        $this->mapper = $this->getMock(Tx_Seminars_Mapper_Event::class, ['findNextUpcoming']);

        $this->fixture = new Tx_Seminars_FrontEnd_Countdown(
            [
                'isStaticTemplateLoaded' => 1,
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
            ],
            $GLOBALS['TSFE']->cObj
        );
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();

        Tx_Seminars_Service_RegistrationManager::purgeInstance();
    }

    //////////////////////////////////////////
    // General tests concerning the fixture.
    //////////////////////////////////////////

    /**
     * @test
     */
    public function fixtureIsAFrontEndCountdownObject()
    {
        self::assertInstanceOf(Tx_Seminars_FrontEnd_Countdown::class, $this->fixture);
    }

    ////////////////////////////////
    // Tests for render()
    ////////////////////////////////

    /**
     * @test
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage The method injectEventMapper() needs to be called first.
     * @expectedExceptionCode 1333617194
     */
    public function renderWithoutCallingInjectEventMapperFirstThrowsBadMethodCallException()
    {
        $this->fixture->render();
    }

    /**
     * @test
     */
    public function renderWithMapperFindNextUpcomingThrowingEmptyQueryResultExceptionReturnsNoEventsFoundMessage()
    {
        $this->fixture->injectEventMapper($this->mapper);
        $this->mapper->expects(self::once())
            ->method('findNextUpcoming')
            ->will(self::throwException(new Tx_Oelib_Exception_NotFound()));

        self::assertContains(
            'There are no upcoming events. Please come back later.',
            $this->fixture->render()
        );
    }

    /**
     * @test
     */
    public function renderCallsRenderMethodOfCountdownViewHelperWithNextUpcomingEventsBeginDateAsUnixTimeStamp()
    {
        $this->fixture->injectEventMapper($this->mapper);
        $event = $this->mapper->getLoadedTestingModel([
            'object_type' => Tx_Seminars_Model_Event::TYPE_COMPLETE,
            'pid' => 0,
            'title' => 'Test event',
            'begin_date' => $GLOBALS['SIM_ACCESS_TIME'] + 1000,
        ]);

        $this->mapper->expects(self::once())
            ->method('findNextUpcoming')
            ->will(self::returnValue($event));

        $this->viewHelper = $this->getMock(Tx_Seminars_ViewHelper_Countdown::class, ['render']);
        $this->viewHelper->expects(self::once())
            ->method('render')
            ->with(self::equalTo($event->getBeginDateAsUnixTimeStamp()));

        $this->fixture->injectCountDownViewHelper($this->viewHelper);

        $this->fixture->render();
    }
}
