<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyFunctional\SchedulerTasks;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\CacheNullifyer;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\Model\Event;
use OliverKlee\Seminars\SchedulerTasks\RegistrationDigest;
use OliverKlee\Seminars\Tests\Support\LanguageHelper;
use OliverKlee\Seminars\Tests\Unit\Traits\EmailTrait;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\DateTimeAspect;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\SchedulerTasks\RegistrationDigest
 */
final class RegistrationDigestTest extends FunctionalTestCase
{
    use EmailTrait;
    use LanguageHelper;

    protected array $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    private RegistrationDigest $subject;

    private DummyConfiguration $configuration;

    /**
     * @var EventMapper&MockObject
     */
    private EventMapper $eventMapperMock;

    /**
     * @var StandaloneView&MockObject
     */
    private StandaloneView $plaintextViewMock;

    /**
     * @var StandaloneView&MockObject
     */
    private StandaloneView $htmlViewMock;

    /**
     * @var positive-int
     */
    private int $now;

    protected function setUp(): void
    {
        parent::setUp();

        (new CacheNullifyer())->setAllCoreCaches();

        $context = GeneralUtility::makeInstance(Context::class);
        $context->setAspect('date', new DateTimeAspect(new \DateTimeImmutable('2018-04-26 12:42:23')));
        $this->now = (int)$context->getPropertyFromAspect('date', 'timestamp');

        $configuration = [
            'fromEmail' => 'from@example.com',
            'fromName' => 'the sender',
            'toEmail' => 'to@example.com',
            'toName' => 'the recipient',
        ];
        $this->configuration = new DummyConfiguration($configuration);
        ConfigurationRegistry::getInstance()->set('plugin.tx_seminars.registrationDigestEmail', $this->configuration);

        $this->eventMapperMock = $this->createMock(EventMapper::class);
        MapperRegistry::set(EventMapper::class, $this->eventMapperMock);

        $this->subject = new RegistrationDigest();

        $this->plaintextViewMock = $this->createMock(StandaloneView::class);
        GeneralUtility::addInstance(StandaloneView::class, $this->plaintextViewMock);
        $this->htmlViewMock = $this->createMock(StandaloneView::class);
        GeneralUtility::addInstance(StandaloneView::class, $this->htmlViewMock);

        $this->email = $this->createEmailMock();
        GeneralUtility::addInstance(MailMessage::class, $this->email);

        $this->getLanguageService();
    }

    protected function tearDown(): void
    {
        // Manually purge the TYPO3 FIFO queue
        GeneralUtility::makeInstance(MailMessage::class);
        GeneralUtility::makeInstance(MailMessage::class);
        GeneralUtility::makeInstance(StandaloneView::class);
        GeneralUtility::makeInstance(StandaloneView::class);

        ConfigurationRegistry::purgeInstance();
        MapperRegistry::purgeInstance();

        parent::tearDown();
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
        $this->eventMapperMock->method('findForRegistrationDigestEmail')->willReturn($events);

        $this->email->expects(self::never())->method('send');

        $this->subject->execute();
    }

    /**
     * @test
     */
    public function executeForEnabledDigestAndNoApplicableEventsNotSendsEmail(): void
    {
        $this->configuration->setAsBoolean('enable', true);

        $this->eventMapperMock->method('findForRegistrationDigestEmail')->willReturn(new Collection());

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
        $this->eventMapperMock->method('findForRegistrationDigestEmail')->willReturn($events);
        $this->eventMapperMock->expects(self::once())->method('save')->with($event);

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
        $this->eventMapperMock->method('findForRegistrationDigestEmail')->willReturn($events);
        $this->eventMapperMock->expects(self::once())->method('save')->with($event);

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
        $this->eventMapperMock->method('findForRegistrationDigestEmail')->willReturn($events);
        $this->eventMapperMock->expects(self::once())->method('save')->with($event);

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
        $this->eventMapperMock->method('findForRegistrationDigestEmail')->willReturn($events);
        $this->eventMapperMock->expects(self::once())->method('save')->with($event);

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
        $this->eventMapperMock->method('findForRegistrationDigestEmail')->willReturn($events);
        $this->eventMapperMock->expects(self::once())->method('save')->with($event);

        $this->plaintextViewMock->expects(self::once())->method('setTemplatePathAndFilename')
            ->with(GeneralUtility::getFileAbsFileName($plaintextTemplatePath));
        $this->plaintextViewMock->expects(self::once())->method('assign')->with('events', $events);
        $expectedPlaintextBody = 'Text body';
        $this->plaintextViewMock->method('render')->willReturn($expectedPlaintextBody);

        $this->htmlViewMock->expects(self::once())->method('setTemplatePathAndFilename')
            ->with(GeneralUtility::getFileAbsFileName($htmlTemplatePath));
        $this->htmlViewMock->expects(self::once())->method('assign')->with('events', $events);
        $expectedHtmlBody = 'Text body';
        $this->htmlViewMock->method('render')->willReturn($expectedHtmlBody);

        $this->subject->execute();

        self::assertSame($expectedPlaintextBody, $this->email->getTextBody());
        self::assertSame($expectedHtmlBody, $this->email->getTextBody());
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
        $this->eventMapperMock->method('findForRegistrationDigestEmail')->willReturn($events);
        $this->eventMapperMock->expects(self::once())->method('save')->with($event);

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
        $this->eventMapperMock->method('findForRegistrationDigestEmail')->willReturn($events);
        $this->eventMapperMock->expects(self::once())->method('save')->with($event);

        $this->subject->execute();
    }
}
