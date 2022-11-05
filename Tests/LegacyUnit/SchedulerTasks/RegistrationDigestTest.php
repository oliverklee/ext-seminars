<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\SchedulerTasks;

use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Testing\CacheNullifyer;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\Model\Event;
use OliverKlee\Seminars\SchedulerTasks\RegistrationDigest;
use OliverKlee\Seminars\Tests\Unit\Traits\EmailTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * @covers \OliverKlee\Seminars\SchedulerTasks\RegistrationDigest
 */
final class RegistrationDigestTest extends TestCase
{
    use EmailTrait;

    /**
     * @var RegistrationDigest
     */
    private $subject;

    /**
     * @var DummyConfiguration
     */
    private $configuration;

    /**
     * @var ObjectProphecy<EventMapper>
     */
    private $eventMapperProphecy;

    /**
     * @var EventMapper
     */
    private $eventMapper;

    /**
     * @var ObjectManager&MockObject
     */
    private $objectManagerMock;

    /**
     * @var ObjectProphecy<StandaloneView>
     */
    private $plaintextViewProphecy;

    /**
     * @var ObjectProphecy<StandaloneView>
     */
    private $htmlViewProphecy;

    /**
     * @var int
     */
    private $now = 1509028643;

    protected function setUp(): void
    {
        (new CacheNullifyer())->setAllCoreCaches();

        $GLOBALS['SIM_EXEC_TIME'] = $this->now;

        $this->subject = new RegistrationDigest();

        $configuration = [
            'fromEmail' => 'from@example.com',
            'fromName' => 'the sender',
            'toEmail' => 'to@example.com',
            'toName' => 'the recipient',
        ];
        $this->configuration = new DummyConfiguration($configuration);
        $this->subject->setConfiguration($this->configuration);

        $this->objectManagerMock = $this->createMock(ObjectManager::class);
        $this->subject->injectObjectManager($this->objectManagerMock);

        $this->eventMapperProphecy = $this->prophesize(EventMapper::class);
        $this->eventMapper = $this->eventMapperProphecy->reveal();
        $this->subject->setEventMapper($this->eventMapper);

        $this->plaintextViewProphecy = $this->prophesize(StandaloneView::class);
        $this->htmlViewProphecy = $this->prophesize(StandaloneView::class);
        $this->objectManagerMock->method('get')->with(StandaloneView::class)
            ->willReturnOnConsecutiveCalls($this->plaintextViewProphecy->reveal(), $this->htmlViewProphecy->reveal());

        $this->email = $this->createEmailMock();
        GeneralUtility::addInstance(MailMessage::class, $this->email);
    }

    protected function tearDown(): void
    {
        // Manually purge the TYPO3 FIFO queue
        GeneralUtility::makeInstance(MailMessage::class);
        GeneralUtility::makeInstance(MailMessage::class);
    }

    /**
     * @test
     */
    public function setConfigurationSetsConfiguration(): void
    {
        self::assertSame($this->configuration, $this->subject->getConfiguration());
    }

    /**
     * @test
     */
    public function setEventMapperSetsEventMapper(): void
    {
        self::assertSame($this->eventMapper, $this->subject->getEventMapper());
    }

    /**
     * @test
     */
    public function executeForDisabledDigestAndOneApplicableEventNotSendsEmail(): void
    {
        $this->configuration->setAsBoolean('enable', false);

        $events = new Collection();
        $event = new Event();
        $events->add($event);
        $this->eventMapperProphecy->findForRegistrationDigestEmail()->willReturn($events);

        $this->email->expects(self::never())->method('send');

        $this->subject->execute();
    }

    /**
     * @test
     */
    public function executeForEnabledDigestAndNoApplicableEventsNotSendsEmail(): void
    {
        $this->configuration->setAsBoolean('enable', true);

        $this->eventMapperProphecy->findForRegistrationDigestEmail()->willReturn(new Collection());

        $this->email->expects(self::never())->method('send');

        $this->subject->execute();
    }

    /**
     * @test
     */
    public function executeForEnabledDigestAndOneApplicableEventSendsEmail(): void
    {
        $this->configuration->setAsBoolean('enable', true);

        $events = new Collection();
        $event = new Event();
        $events->add($event);
        $this->eventMapperProphecy->findForRegistrationDigestEmail()->willReturn($events);
        $this->eventMapperProphecy->save($event)->shouldBeCalled();

        $this->email->expects(self::once())->method('send');

        $this->subject->execute();
    }

    /**
     * @test
     */
    public function emailUsesSenderFromConfiguration(): void
    {
        $this->configuration->setAsBoolean('enable', true);

        $fromEmail = 'jane@example.com';
        $fromName = 'Jane Doe';
        $this->configuration->setAsString('fromEmail', $fromEmail);
        $this->configuration->setAsString('fromName', $fromName);

        $events = new Collection();
        $event = new Event();
        $events->add($event);
        $this->eventMapperProphecy->findForRegistrationDigestEmail()->willReturn($events);
        $this->eventMapperProphecy->save($event)->shouldBeCalled();

        $this->subject->execute();

        self::assertSame([$fromEmail => $fromName], $this->getFromOfEmail($this->email));
    }

    /**
     * @test
     */
    public function emailUsesToFromConfiguration(): void
    {
        $this->configuration->setAsBoolean('enable', true);

        $toEmail = 'joe@example.com';
        $toName = 'Joe Doe';
        $this->configuration->setAsString('toEmail', $toEmail);
        $this->configuration->setAsString('toName', $toName);

        $events = new Collection();
        $event = new Event();
        $events->add($event);
        $this->eventMapperProphecy->findForRegistrationDigestEmail()->willReturn($events);
        $this->eventMapperProphecy->save($event)->shouldBeCalled();

        $this->subject->execute();

        self::assertSame([$toEmail => $toName], $this->getToOfEmail($this->email));
    }

    /**
     * @test
     */
    public function emailHasLocalizedSubject(): void
    {
        $this->configuration->setAsBoolean('enable', true);

        $toEmail = 'joe@example.com';
        $toName = 'Joe Doe';
        $this->configuration->setAsString('toEmail', $toEmail);
        $this->configuration->setAsString('toName', $toName);

        $events = new Collection();
        $event = new Event();
        $events->add($event);
        $this->eventMapperProphecy->findForRegistrationDigestEmail()->willReturn($events);
        $this->eventMapperProphecy->save($event)->shouldBeCalled();

        $this->subject->execute();

        $expectedSubject = LocalizationUtility::translate('registrationDigestEmail_Subject', 'seminars');
        self::assertSame($expectedSubject, $this->email->getSubject());
    }

    /**
     * @test
     */
    public function emailHasContentFromTemplateWithEvents(): void
    {
        $plaintextTemplatePath = 'EXT:seminars/Resources/Private/Templates/Mail/RegistrationDigest.txt';
        $htmlTemplatePath = 'EXT:seminars/Resources/Private/Templates/Mail/RegistrationDigest.html';

        $this->configuration->setAsBoolean('enable', true);
        $this->configuration->setAsString('plaintextTemplate', $plaintextTemplatePath);
        $this->configuration->setAsString('htmlTemplate', $htmlTemplatePath);

        $toEmail = 'joe@example.com';
        $toName = 'Joe Doe';
        $this->configuration->setAsString('toEmail', $toEmail);
        $this->configuration->setAsString('toName', $toName);

        $events = new Collection();
        $event = new Event();
        $events->add($event);
        $this->eventMapperProphecy->findForRegistrationDigestEmail()->willReturn($events);
        $this->eventMapperProphecy->save($event)->shouldBeCalled();

        $this->plaintextViewProphecy->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($plaintextTemplatePath))
            ->shouldBeCalled();
        $this->plaintextViewProphecy->assign('events', $events)->shouldBeCalled();
        $expectedPlaintextBody = 'Text body';
        $this->plaintextViewProphecy->render()->willReturn($expectedPlaintextBody);

        $this->htmlViewProphecy->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($htmlTemplatePath))
            ->shouldBeCalled();
        $this->htmlViewProphecy->assign('events', $events)->shouldBeCalled();
        $expectedHtmlBody = 'Text body';
        $this->htmlViewProphecy->render()->willReturn($expectedHtmlBody);

        $this->subject->execute();

        self::assertSame($expectedPlaintextBody, $this->getTextBodyOfEmail($this->email));
        self::assertSame($expectedHtmlBody, $this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function executeSetsDateOfLastDigestInEventsToNow(): void
    {
        $this->configuration->setAsBoolean('enable', true);

        $events = new Collection();
        $event = new Event();
        $events->add($event);
        $this->eventMapperProphecy->findForRegistrationDigestEmail()->willReturn($events);
        $this->eventMapperProphecy->save($event)->shouldBeCalled();

        $this->subject->execute();

        self::assertSame($this->now, $event->getDateOfLastRegistrationDigestEmailAsUnixTimeStamp());
    }

    /**
     * @test
     */
    public function executeSavesEvents(): void
    {
        $this->configuration->setAsBoolean('enable', true);

        $events = new Collection();
        $event = new Event();
        $events->add($event);
        $this->eventMapperProphecy->findForRegistrationDigestEmail()->willReturn($events);
        $this->eventMapperProphecy->save($event)->shouldBeCalled();

        $this->subject->execute();
    }
}
