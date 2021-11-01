<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\FrontEnd;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\FrontEnd\Countdown;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\Model\Event;
use OliverKlee\Seminars\Service\RegistrationManager;
use OliverKlee\Seminars\ViewHelpers\CountdownViewHelper;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @covers \OliverKlee\Seminars\FrontEnd\Countdown
 */
final class CountdownTest extends TestCase
{
    /**
     * @var Countdown
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var EventMapper&MockObject
     */
    private $mapper = null;

    protected function setUp(): void
    {
        $configurationRegistry = ConfigurationRegistry::getInstance();
        $configurationRegistry->set('config', new DummyConfiguration());
        $configurationRegistry->set('page.config', new DummyConfiguration());
        $configurationRegistry->set('plugin.tx_seminars._LOCAL_LANG.default', new DummyConfiguration());

        $this->testingFramework = new TestingFramework('tx_seminars');
        $rootPageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($rootPageUid);

        /** @var EventMapper&MockObject $mapper */
        $mapper = $this->getMockBuilder(EventMapper::class)->setMethods(['findNextUpcoming'])->getMock();
        $this->mapper = $mapper;

        $this->subject = new Countdown(
            [
                'isStaticTemplateLoaded' => 1,
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
            ],
            $this->getFrontEndController()->cObj
        );
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();

        RegistrationManager::purgeInstance();
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
    public function fixtureIsAFrontEndCountdownObject(): void
    {
        self::assertInstanceOf(Countdown::class, $this->subject);
    }

    ////////////////////////////////
    // Tests for render()
    ////////////////////////////////

    /**
     * @test
     */
    public function renderWithoutCallingInjectEventMapperFirstThrowsBadMethodCallException(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('The method injectEventMapper() needs to be called first.');
        $this->expectExceptionCode(1333617194);

        $this->subject->render();
    }

    /**
     * @test
     */
    public function renderWithMapperFindNextUpcomingThrowingEmptyQueryResultExceptionReturnsNoEventsFoundMessage(): void
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
    public function renderCallsRenderMethodOfCountdownViewHelperWithNextUpcomingEventsBeginDateAsUnixTimeStamp(): void
    {
        $this->subject->injectEventMapper($this->mapper);
        $event = $this->mapper->getLoadedTestingModel(
            [
                'object_type' => Event::TYPE_COMPLETE,
                'pid' => 0,
                'title' => 'Test event',
                'begin_date' => $GLOBALS['SIM_ACCESS_TIME'] + 1000,
            ]
        );

        $this->mapper->expects(self::once())
            ->method('findNextUpcoming')
            ->willReturn($event);

        /** @var CountdownViewHelper&MockObject $viewHelper */
        $viewHelper = $this->createPartialMock(CountdownViewHelper::class, ['render']);
        $viewHelper->expects(self::once())
            ->method('render')
            ->with(self::equalTo($event->getBeginDateAsUnixTimeStamp()));

        $this->subject->injectCountDownViewHelper($viewHelper);

        $this->subject->render();
    }
}
