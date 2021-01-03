<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\SchedulerTask;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\SchedulerTask\RegistrationDigest;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophecy\ProphecySubjectInterface;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class RegistrationDigestTest extends TestCase
{
    /**
     * @var RegistrationDigest
     */
    private $subject = null;

    /**
     * @var \Tx_Oelib_Configuration
     */
    private $configuration = null;

    /**
     * @var \Tx_Seminars_Mapper_Event|ObjectProphecy
     */
    private $eventMapperProphecy = null;

    /**
     * @var \Tx_Seminars_Mapper_Event|ProphecySubjectInterface
     */
    private $eventMapper = null;

    /**
     * @var ObjectManager|ObjectProphecy
     */
    private $objectManagerProphecy = null;

    /**
     * @var MailMessage|ObjectProphecy
     */
    private $emailProphecy = null;

    /**
     * @var StandaloneView|ObjectProphecy
     */
    private $viewProphecy = null;

    /**
     * @var int
     */
    private $now = 1509028643;

    protected function setUp()
    {
        if (!ExtensionManagementUtility::isLoaded('scheduler')) {
            self::markTestSkipped('This tests needs the scheduler extension.');
        }

        $GLOBALS['SIM_EXEC_TIME'] = $this->now;

        $this->subject = new RegistrationDigest();

        $this->configuration = new \Tx_Oelib_Configuration();
        $this->subject->setConfiguration($this->configuration);

        $this->objectManagerProphecy = $this->prophesize(ObjectManager::class);
        /** @var ObjectManager|ProphecySubjectInterface $objectManager */
        $objectManager = $this->objectManagerProphecy->reveal();
        $this->subject->injectObjectManager($objectManager);

        $this->eventMapperProphecy = $this->prophesize(\Tx_Seminars_Mapper_Event::class);
        $this->eventMapper = $this->eventMapperProphecy->reveal();
        $this->subject->setEventMapper($this->eventMapper);

        $this->emailProphecy = $this->prophesize(MailMessage::class);
        $this->viewProphecy = $this->prophesize(StandaloneView::class);
    }

    /**
     * @test
     */
    public function setConfigurationSetsConfiguration()
    {
        self::assertSame($this->configuration, $this->subject->getConfiguration());
    }

    /**
     * @test
     */
    public function setEventMapperSetsEventMapper()
    {
        self::assertSame($this->eventMapper, $this->subject->getEventMapper());
    }

    /**
     * @return void
     */
    private function setObjectManagerReturnValues()
    {
        $this->objectManagerProphecy->get(StandaloneView::class)->willReturn($this->viewProphecy->reveal());
        $this->objectManagerProphecy->get(MailMessage::class)->willReturn($this->emailProphecy->reveal());
    }

    /**
     * @test
     */
    public function executeForDisabledDigestAndOneApplicableEventNotSendsEmail()
    {
        $this->configuration->setAsBoolean('enable', false);

        $events = new Collection();
        $event = new \Tx_Seminars_Model_Event();
        $events->add($event);
        $this->eventMapperProphecy->findForRegistrationDigestEmail()->willReturn($events);

        /** @var MailMessage|ObjectProphecy $emailProphecy */
        $emailProphecy = $this->prophesize(MailMessage::class);
        $this->objectManagerProphecy->get(MailMessage::class)->willReturn($emailProphecy->reveal());

        $this->subject->execute();

        $emailProphecy->send()->shouldNotHaveBeenCalled();
        /** @var StandaloneView|ObjectProphecy $viewProphecy */
        $this->prophesize(StandaloneView::class);
    }

    /**
     * @test
     */
    public function executeForEnabledDigestAndNoApplicableEventsNotSendsEmail()
    {
        $this->setObjectManagerReturnValues();
        $this->configuration->setAsBoolean('enable', true);

        $this->eventMapperProphecy->findForRegistrationDigestEmail()->willReturn(new Collection());

        $this->subject->execute();

        $this->emailProphecy->send()->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function executeForEnabledDigestAndOneApplicableEventSendsEmail()
    {
        $this->setObjectManagerReturnValues();
        $this->configuration->setAsBoolean('enable', true);

        $events = new Collection();
        $event = new \Tx_Seminars_Model_Event();
        $events->add($event);
        $this->eventMapperProphecy->findForRegistrationDigestEmail()->willReturn($events);
        $this->eventMapperProphecy->save($event)->shouldBeCalled();

        $this->subject->execute();

        $this->emailProphecy->send()->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function emailUsesSenderFromConfiguration()
    {
        $this->setObjectManagerReturnValues();
        $this->configuration->setAsBoolean('enable', true);

        $fromEmail = 'jane@example.com';
        $fromName = 'Jane Doe';
        $this->configuration->setAsString('fromEmail', $fromEmail);
        $this->configuration->setAsString('fromName', $fromName);

        $events = new Collection();
        $event = new \Tx_Seminars_Model_Event();
        $events->add($event);
        $this->eventMapperProphecy->findForRegistrationDigestEmail()->willReturn($events);
        $this->eventMapperProphecy->save($event)->shouldBeCalled();

        $this->subject->execute();

        $this->emailProphecy->setFrom($fromEmail, $fromName)->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function emailUsesToFromConfiguration()
    {
        $this->setObjectManagerReturnValues();
        $this->configuration->setAsBoolean('enable', true);

        $toEmail = 'joe@example.com';
        $toName = 'Joe Doe';
        $this->configuration->setAsString('toEmail', $toEmail);
        $this->configuration->setAsString('toName', $toName);

        $events = new Collection();
        $event = new \Tx_Seminars_Model_Event();
        $events->add($event);
        $this->eventMapperProphecy->findForRegistrationDigestEmail()->willReturn($events);
        $this->eventMapperProphecy->save($event)->shouldBeCalled();

        $this->subject->execute();

        $this->emailProphecy->setTo($toEmail, $toName)->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function emailHasLocalizedSubject()
    {
        $this->setObjectManagerReturnValues();
        $this->configuration->setAsBoolean('enable', true);

        $toEmail = 'joe@example.com';
        $toName = 'Joe Doe';
        $this->configuration->setAsString('toEmail', $toEmail);
        $this->configuration->setAsString('toName', $toName);

        $events = new Collection();
        $event = new \Tx_Seminars_Model_Event();
        $events->add($event);
        $this->eventMapperProphecy->findForRegistrationDigestEmail()->willReturn($events);
        $this->eventMapperProphecy->save($event)->shouldBeCalled();

        $this->subject->execute();

        $expectedSubject = LocalizationUtility::translate('registrationDigestEmail_Subject', 'seminars');
        $this->emailProphecy->setSubject($expectedSubject)->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function emailHasContentFromTemplateWithEvents()
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
        $event = new \Tx_Seminars_Model_Event();
        $events->add($event);
        $this->eventMapperProphecy->findForRegistrationDigestEmail()->willReturn($events);
        $this->eventMapperProphecy->save($event)->shouldBeCalled();

        $expectedBody = 'Text body';
        $this->viewProphecy->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($plaintextTemplatePath))
            ->shouldBeCalled();
        $this->viewProphecy->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($htmlTemplatePath))
            ->shouldBeCalled();
        $this->viewProphecy->assign('events', $events)->shouldBeCalled();
        $this->viewProphecy->render()->willReturn($expectedBody);

        $this->subject->execute();

        $this->emailProphecy->setBody($expectedBody)->shouldHaveBeenCalled();
        $this->emailProphecy->addPart($expectedBody, 'text/html')->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function executeSetsDateOfLastDigestInEventsToNow()
    {
        $this->setObjectManagerReturnValues();
        $this->configuration->setAsBoolean('enable', true);

        $events = new Collection();
        $event = new \Tx_Seminars_Model_Event();
        $events->add($event);
        $this->eventMapperProphecy->findForRegistrationDigestEmail()->willReturn($events);
        $this->eventMapperProphecy->save($event)->shouldBeCalled();

        $this->subject->execute();

        self::assertSame($this->now, $event->getDateOfLastRegistrationDigestEmailAsUnixTimeStamp());
    }

    /**
     * @test
     */
    public function executeSavesEvents()
    {
        $this->setObjectManagerReturnValues();
        $this->configuration->setAsBoolean('enable', true);

        $events = new Collection();
        $event = new \Tx_Seminars_Model_Event();
        $events->add($event);
        $this->eventMapperProphecy->findForRegistrationDigestEmail()->willReturn($events);
        $this->eventMapperProphecy->save($event)->shouldBeCalled();

        $this->subject->execute();
    }
}
