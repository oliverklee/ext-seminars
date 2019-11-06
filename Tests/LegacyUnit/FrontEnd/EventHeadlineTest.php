<?php
declare(strict_types = 1);

use OliverKlee\PhpUnit\TestCase;

/**
 * Test case.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_FrontEnd_EventHeadlineTest extends TestCase
{
    /**
     * @var \Tx_Seminars_FrontEnd_EventHeadline
     */
    private $subject;

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    /**
     * @var \Tx_Seminars_Mapper_Event
     */
    private $mapper;

    /**
     * @var int event begin date
     */
    private $eventDate = 0;

    /**
     * @var int UID of the event to create the headline for
     */
    private $eventId = 0;

    protected function setUp()
    {
        $pluginConfiguration = new \Tx_Oelib_Configuration();
        $pluginConfiguration->setAsString('dateFormatYMD', '%d.%m.%Y');
        $configurationRegistry = \Tx_Oelib_ConfigurationRegistry::getInstance();
        $configurationRegistry->set('plugin.tx_seminars', $pluginConfiguration);
        $configurationRegistry->set('config', new \Tx_Oelib_Configuration());
        $configurationRegistry->set('page.config', new \Tx_Oelib_Configuration());
        $configurationRegistry->set('plugin.tx_seminars._LOCAL_LANG.default', new \Tx_Oelib_Configuration());

        \Tx_Oelib_ConfigurationProxy::getInstance('seminars')->setAsBoolean('enableConfigCheck', false);

        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');
        $this->testingFramework->createFakeFrontEnd();

        // just picked some random date (2001-01-01 00:00:00)
        $this->eventDate = 978303600;

        $this->mapper = new \Tx_Seminars_Mapper_Event();
        $event = $this->mapper->getLoadedTestingModel(
            [
                'pid' => 0,
                'title' => 'Test event',
                'begin_date' => $this->eventDate,
            ]
        );
        $this->eventId = $event->getUid();

        $this->subject = new \Tx_Seminars_FrontEnd_EventHeadline(
            [
                'isStaticTemplateLoaded' => 1,
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
            ],
            $GLOBALS['TSFE']->cObj
        );
        $this->subject->injectEventMapper($this->mapper);
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();

        \Tx_Seminars_Service_RegistrationManager::purgeInstance();
    }

    //////////////////////////////////
    // Tests for the render function
    //////////////////////////////////

    /**
     * @test
     */
    public function renderWithoutCallingInjectEventMapperFirstThrowsBadMethodCallException()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('The method injectEventMapper() needs to be called first.');
        $this->expectExceptionCode(1333614794);

        $this->subject->injectEventMapper(null);
        $this->subject->render();
    }

    /**
     * @test
     */
    public function renderWithUidOfExistingEventReturnsTitleOfSelectedEvent()
    {
        $this->subject->piVars['uid'] = $this->eventId;

        self::assertContains(
            'Test event',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWithUidOfExistingEventReturnsHtmlSpecialCharedTitleOfSelectedEvent()
    {
        /** @var \Tx_Seminars_Model_Event $event */
        $event = $this->mapper->find($this->eventId);
        $event->setTitle('<test>Test event</test>');
        $this->subject->piVars['uid'] = $this->eventId;

        self::assertContains(
            htmlspecialchars('<test>Test event</test>'),
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWithUidOfExistingEventReturnsDateOfSelectedEvent()
    {
        $dateFormat = '%d.%m.%Y';
        $configuration = new \Tx_Oelib_Configuration();
        $configuration->setAsString('dateFormatYMD', $dateFormat);
        \Tx_Oelib_ConfigurationRegistry::getInstance()->set('plugin.tx_seminars_seminars', $configuration);

        $this->subject->piVars['uid'] = $this->eventId;

        self::assertContains(
            strftime($dateFormat, $this->eventDate),
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderReturnsEmptyStringIfNoUidIsSetInPiVar()
    {
        unset($this->subject->piVars['uid']);

        self::assertEquals(
            '',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderReturnsEmptyStringIfUidOfInexistentEventIsSetInPiVar()
    {
        $this->subject->piVars['uid'] = $this->testingFramework->getAutoIncrement('tx_seminars_seminars');

        self::assertEquals(
            '',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderReturnsEmptyStringIfNonNumericEventUidIsSetInPiVar()
    {
        $this->subject->piVars['uid'] = 'foo';

        self::assertEquals(
            '',
            $this->subject->render()
        );
    }
}
