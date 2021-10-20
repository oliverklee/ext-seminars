<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\SchedulerTasks;

use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Testing\CacheNullifyer;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\Model\Event;
use OliverKlee\Seminars\SchedulerTasks\RegistrationDigest;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

final class RegistrationDigestTest extends TestCase
{
    /**
     * @var RegistrationDigest
     */
    private $subject = null;

    /**
     * @var DummyConfiguration
     */
    private $configuration = null;

    /**
     * @var ObjectProphecy
     */
    private $eventMapperProphecy = null;

    /**
     * @var EventMapper
     */
    private $eventMapper = null;

    /**
     * @var ObjectProphecy
     */
    private $objectManagerProphecy = null;

    /**
     * @var ObjectProphecy
     */
    private $emailProphecy = null;

    /**
     * @var ObjectProphecy
     */
    private $viewProphecy = null;

    /**
     * @var int
     */
    private $now = 1509028643;

    protected function setUp(): void
    {
        if (!ExtensionManagementUtility::isLoaded('scheduler')) {
            self::markTestSkipped('This tests needs the scheduler extension.');
        }

        (new CacheNullifyer())->disableCoreCaches();

        $GLOBALS['SIM_EXEC_TIME'] = $this->now;

        $this->subject = new RegistrationDigest();

        $this->configuration = new DummyConfiguration();
        $this->subject->setConfiguration($this->configuration);

        $this->objectManagerProphecy = $this->prophesize(ObjectManager::class);
        /** @var ObjectManager $objectManager */
        $objectManager = $this->objectManagerProphecy->reveal();
        $this->subject->injectObjectManager($objectManager);

        $this->eventMapperProphecy = $this->prophesize(EventMapper::class);
        $this->eventMapper = $this->eventMapperProphecy->reveal();
        $this->subject->setEventMapper($this->eventMapper);

        $this->emailProphecy = $this->prophesize(MailMessage::class);
        $this->viewProphecy = $this->prophesize(StandaloneView::class);
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
     * @return void
     */
    private function setObjectManagerReturnValues(): void
    {
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->objectManagerProphecy->get(StandaloneView::class)->willReturn($this->viewProphecy->reveal());
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->objectManagerProphecy->get(MailMessage::class)->willReturn($this->emailProphecy->reveal());
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
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->eventMapperProphecy->findForRegistrationDigestEmail()->willReturn($events);

        $emailProphecy = $this->prophesize(MailMessage::class);
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->objectManagerProphecy->get(MailMessage::class)->willReturn($emailProphecy->reveal());

        $this->subject->execute();

        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $emailProphecy->send()->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function executeForEnabledDigestAndNoApplicableEventsNotSendsEmail(): void
    {
        $this->setObjectManagerReturnValues();
        $this->configuration->setAsBoolean('enable', true);

        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->eventMapperProphecy->findForRegistrationDigestEmail()->willReturn(new Collection());

        $this->subject->execute();

        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->emailProphecy->send()->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function executeForEnabledDigestAndOneApplicableEventSendsEmail(): void
    {
        $this->setObjectManagerReturnValues();
        $this->configuration->setAsBoolean('enable', true);

        $events = new Collection();
        $event = new Event();
        $events->add($event);
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->eventMapperProphecy->findForRegistrationDigestEmail()->willReturn($events);
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->eventMapperProphecy->save($event)->shouldBeCalled();

        $this->subject->execute();

        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->emailProphecy->send()->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function emailUsesSenderFromConfiguration(): void
    {
        $this->setObjectManagerReturnValues();
        $this->configuration->setAsBoolean('enable', true);

        $fromEmail = 'jane@example.com';
        $fromName = 'Jane Doe';
        $this->configuration->setAsString('fromEmail', $fromEmail);
        $this->configuration->setAsString('fromName', $fromName);

        $events = new Collection();
        $event = new Event();
        $events->add($event);
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->eventMapperProphecy->findForRegistrationDigestEmail()->willReturn($events);
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->eventMapperProphecy->save($event)->shouldBeCalled();

        $this->subject->execute();

        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->emailProphecy->setFrom($fromEmail, $fromName)->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function emailUsesToFromConfiguration(): void
    {
        $this->setObjectManagerReturnValues();
        $this->configuration->setAsBoolean('enable', true);

        $toEmail = 'joe@example.com';
        $toName = 'Joe Doe';
        $this->configuration->setAsString('toEmail', $toEmail);
        $this->configuration->setAsString('toName', $toName);

        $events = new Collection();
        $event = new Event();
        $events->add($event);
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->eventMapperProphecy->findForRegistrationDigestEmail()->willReturn($events);
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->eventMapperProphecy->save($event)->shouldBeCalled();

        $this->subject->execute();

        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->emailProphecy->setTo($toEmail, $toName)->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function emailHasLocalizedSubject(): void
    {
        $this->setObjectManagerReturnValues();
        $this->configuration->setAsBoolean('enable', true);

        $toEmail = 'joe@example.com';
        $toName = 'Joe Doe';
        $this->configuration->setAsString('toEmail', $toEmail);
        $this->configuration->setAsString('toName', $toName);

        $events = new Collection();
        $event = new Event();
        $events->add($event);
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->eventMapperProphecy->findForRegistrationDigestEmail()->willReturn($events);
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->eventMapperProphecy->save($event)->shouldBeCalled();

        $this->subject->execute();

        $expectedSubject = LocalizationUtility::translate('registrationDigestEmail_Subject', 'seminars');
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->emailProphecy->setSubject($expectedSubject)->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function emailHasContentFromTemplateWithEvents(): void
    {
        $plaintextTemplatePath = 'EXT:seminars/Resources/Private/Templates/Mail/RegistrationDigest.txt';
        $htmlTemplatePath = 'EXT:seminars/Resources/Private/Templates/Mail/RegistrationDigest.html';

        $this->setObjectManagerReturnValues();
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
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->eventMapperProphecy->findForRegistrationDigestEmail()->willReturn($events);
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->eventMapperProphecy->save($event)->shouldBeCalled();

        $expectedBody = 'Text body';
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->viewProphecy->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($plaintextTemplatePath))
            ->shouldBeCalled();
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->viewProphecy->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($htmlTemplatePath))
            ->shouldBeCalled();
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->viewProphecy->assign('events', $events)->shouldBeCalled();
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->viewProphecy->render()->willReturn($expectedBody);

        $this->subject->execute();

        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->emailProphecy->setBody($expectedBody)->shouldHaveBeenCalled();
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->emailProphecy->addPart($expectedBody, 'text/html')->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function executeSetsDateOfLastDigestInEventsToNow(): void
    {
        $this->setObjectManagerReturnValues();
        $this->configuration->setAsBoolean('enable', true);

        $events = new Collection();
        $event = new Event();
        $events->add($event);
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->eventMapperProphecy->findForRegistrationDigestEmail()->willReturn($events);
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->eventMapperProphecy->save($event)->shouldBeCalled();

        $this->subject->execute();

        self::assertSame($this->now, $event->getDateOfLastRegistrationDigestEmailAsUnixTimeStamp());
    }

    /**
     * @test
     */
    public function executeSavesEvents(): void
    {
        $this->setObjectManagerReturnValues();
        $this->configuration->setAsBoolean('enable', true);

        $events = new Collection();
        $event = new Event();
        $events->add($event);
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->eventMapperProphecy->findForRegistrationDigestEmail()->willReturn($events);
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->eventMapperProphecy->save($event)->shouldBeCalled();

        $this->subject->execute();
    }
}
