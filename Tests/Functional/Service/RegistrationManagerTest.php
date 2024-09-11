<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Service;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Mapper\CountryMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Templating\Template;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Domain\Model\Event\EventDateInterface;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\FrontEnd\DefaultController;
use OliverKlee\Seminars\Hooks\Interfaces\RegistrationEmail;
use OliverKlee\Seminars\Mapper\RegistrationMapper;
use OliverKlee\Seminars\Model\FrontEndUser;
use OliverKlee\Seminars\OldModel\LegacyEvent;
use OliverKlee\Seminars\OldModel\LegacyRegistration;
use OliverKlee\Seminars\Service\RegistrationManager;
use OliverKlee\Seminars\Service\SingleViewLinkBuilder;
use OliverKlee\Seminars\Tests\Support\LanguageHelper;
use OliverKlee\Seminars\Tests\Unit\Traits\EmailTrait;
use OliverKlee\Seminars\Tests\Unit\Traits\MakeInstanceTrait;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Mime\Part\DataPart;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\DateTimeAspect;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Service\RegistrationManager
 * @covers \OliverKlee\Seminars\Templating\TemplateHelper
 */
final class RegistrationManagerTest extends FunctionalTestCase
{
    use LanguageHelper;
    use EmailTrait;
    use MakeInstanceTrait;

    /**
     * @var non-empty-string
     */
    private const EMAIL_TEMPLATE_PATH = 'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html';

    /**
     * @var positive-int
     */
    private $now;

    protected $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * @var RegistrationManager
     */
    private $subject;

    /**
     * @var DummyConfiguration
     */
    private $configuration;

    /**
     * @var positive-int
     */
    private $seminarUid;

    /**
     * @var positive-int
     */
    private $organizerUid;

    /**
     * @var MailMessage&MockObject
     */
    private $secondEmail;

    protected function setUp(): void
    {
        parent::setUp();

        $context = GeneralUtility::makeInstance(Context::class);
        $context->setAspect('date', new DateTimeAspect(new \DateTimeImmutable('2018-04-26 12:42:23')));
        $this->now = (int)$context->getPropertyFromAspect('date', 'timestamp');

        $this->initializeBackEndLanguage();

        LegacyRegistration::purgeCachedSeminars();
        $configurationRegistry = ConfigurationRegistry::getInstance();
        $this->configuration = new DummyConfiguration(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html',
            ]
        );
        $configurationRegistry->set('plugin.tx_seminars', $this->configuration);
        $configurationRegistry->set('plugin.tx_seminars._LOCAL_LANG.default', new DummyConfiguration());
        $configurationRegistry->set('config', new DummyConfiguration());
        $configurationRegistry->set('page.config', new DummyConfiguration());

        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->email = $this->createEmailMock();
        $this->secondEmail = $this->createEmailMock();
        $this->addMockedInstance(MailMessage::class, $this->email);
        $this->addMockedInstance(MailMessage::class, $this->secondEmail);

        $this->subject = new RegistrationManager();

        $linkBuilder = $this->createPartialMock(SingleViewLinkBuilder::class, ['createAbsoluteUrlForEvent']);
        $linkBuilder->method('createAbsoluteUrlForEvent')->willReturn('https://singleview.example.com/');
        $this->subject->setLinkBuilder($linkBuilder);
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUpWithoutDatabase();

        ConfigurationRegistry::purgeInstance();
        RegistrationManager::purgeInstance();
        // Purge the FIFO buffer of mocks
        GeneralUtility::makeInstance(MailMessage::class);
        GeneralUtility::makeInstance(MailMessage::class);

        parent::tearDown();
    }

    private function getFrontEndController(): TypoScriptFrontendController
    {
        $controller = $GLOBALS['TSFE'];
        if (!$controller instanceof TypoScriptFrontendController) {
            throw new \RuntimeException('No FE present!', 1645868170);
        }

        return $controller;
    }

    private function setUpFakeFrontEnd(): DefaultController
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationPage.xml');
        $this->testingFramework->createFakeFrontEnd(1);
        $controller = new DefaultController();
        $controller->cObj = $this->getFrontEndController()->cObj;
        $controller->conf = ['registerPID' => '3'];

        return $controller;
    }

    /**
     * @param array<non-empty-string, string|int<0, max>> $additionalEventData
     */
    private function createEventWithOrganizer(array $additionalEventData = []): void
    {
        $this->organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            [
                'title' => 'test organizer',
                'email' => 'mail@example.com',
            ]
        );
        $originalEventData = [
            'title' => 'test event',
            'subtitle' => 'juggling with burning chainsaws',
            'begin_date' => $this->now + 1000,
            'end_date' => $this->now + 2000,
            'attendees_min' => 1,
            'attendees_max' => 10,
            'needs_registration' => 1,
            'organizers' => 1,
        ];
        $eventData = \array_merge($originalEventData, $additionalEventData);
        $this->seminarUid = $this->testingFramework->createRecord('tx_seminars_seminars', $eventData);
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_organizers_mm',
            $this->seminarUid,
            $this->organizerUid
        );
    }

    /**
     * Returns and creates a registration.
     *
     * A new front-end user will be created and the event in $this->seminar will be used.
     *
     * @return LegacyRegistration the created registration
     */
    private function createRegistration(): LegacyRegistration
    {
        $frontEndUserUid = $this->testingFramework->createFrontEndUser(
            '',
            [
                'name' => 'Harry Callagan',
                'email' => 'foo@bar.com',
            ]
        );

        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->seminarUid,
                'user' => $frontEndUserUid,
                'food' => 'something nice to eat',
                'accommodation' => 'a nice, dry place',
                'interests' => 'learning Ruby on Rails',
            ]
        );

        return new LegacyRegistration($registrationUid);
    }

    /**
     * Imports static records - but only if they aren't already available as static data.
     */
    private function importStaticData(): void
    {
        if (
            GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('static_countries')
                ->count('*', 'static_countries', []) === 0
        ) {
            $this->importDataSet(__DIR__ . '/Fixtures/Countries.xml');
        }
    }

    /**
     * @return non-empty-string
     */
    private function formatDateForCalendar(int $dateAsUnixTimeStamp): string
    {
        return \date('Ymd\\THis', $dateAsUnixTimeStamp);
    }

    /**
     * @test
     */
    public function canBeCreatedWithMakeInstance(): void
    {
        $instance = GeneralUtility::makeInstance(RegistrationManager::class);

        self::assertInstanceOf(RegistrationManager::class, $instance);
    }

    // Tests concerning notifyOrganizers

    /**
     * @test
     */
    public function notifyOrganizersForEventWithOneVacancyShowsVacanciesLabelWithVacancyNumber(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationManagerRecords.xml');
        $this->addMockedInstance(MailMessage::class, $this->email);
        $registration = LegacyRegistration::fromUid(1);

        $this->configuration->setAsBoolean('sendNotification', true);
        $this->configuration->setAsString('templateFile', self::EMAIL_TEMPLATE_PATH);
        $this->configuration->setAsString('showSeminarFieldsInNotificationMail', 'vacancies');

        $this->subject->notifyOrganizers($registration);

        $expectedExpression = '/' . $this->translate('label_vacancies') . ': 1\\n*$/';
        self::assertRegExp($expectedExpression, $this->email->getTextBody());
    }

    // Tests concerning getRegistrationLink

    /**
     * @test
     */
    public function getRegistrationLinkWithEventWithVacanciesReturnsLinkToRegistrationPage(): void
    {
        $plugin = $this->setUpFakeFrontEnd();

        $this->importDataSet(__DIR__ . '/Fixtures/EventWithVacancies.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);

        $result = $this->subject->getRegistrationLink($plugin, $event);

        self::assertStringContainsString('/registration', $result);
    }

    /**
     * @test
     */
    public function getRegistrationLinkWithEventWithVacanciesReturnsLinkWithEventUid(): void
    {
        $plugin = $this->setUpFakeFrontEnd();

        $this->importDataSet(__DIR__ . '/Fixtures/EventWithVacancies.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);

        $result = $this->subject->getRegistrationLink($plugin, $event);

        self::assertStringContainsString('%5Bevent%5D=1', $result);
    }

    /**
     * @test
     */
    public function getRegistrationLinkWithFullyBookedEventReturnsEmptyString(): void
    {
        $plugin = $this->setUpFakeFrontEnd();

        $this->importDataSet(__DIR__ . '/Fixtures/FullyBookedEvent.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);

        $result = $this->subject->getRegistrationLink($plugin, $event);

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function getRegistrationLinkWithEventWithUnlimitedVacanciesReturnsLinkWithEventUid(): void
    {
        $plugin = $this->setUpFakeFrontEnd();

        $this->importDataSet(__DIR__ . '/Fixtures/EventWithUnlimitedVacancies.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);

        $result = $this->subject->getRegistrationLink($plugin, $event);

        self::assertStringContainsString('%5Bevent%5D=1', $result);
    }

    /**
     * @test
     */
    public function getRegistrationLinkWithFullyBookedEventWithQueueReturnsLinkWithEventUid(): void
    {
        $plugin = $this->setUpFakeFrontEnd();

        $this->importDataSet(__DIR__ . '/Fixtures/FullyBookedEventWithQueue.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);

        $result = $this->subject->getRegistrationLink($plugin, $event);

        self::assertStringContainsString('%5Bevent%5D=1', $result);
    }

    // Tests concerning getLinkToRegistrationPage

    /**
     * @test
     */
    public function getLinkToRegistrationPageCreatesLinkToRegistrationPageWithEventUid(): void
    {
        $plugin = $this->setUpFakeFrontEnd();
        $this->importDataSet(__DIR__ . '/Fixtures/EventWithVacancies.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);

        $result = $this->subject->getLinkToRegistrationPage($plugin, $event);

        self::assertStringContainsString('/registration', $result);
        self::assertStringContainsString('%5Bevent%5D=1', $result);
    }

    /**
     * @test
     */
    public function getLinkToRegistrationPageWithSeparateDetailsPageCreatesLinkToRegistrationPage(): void
    {
        $plugin = $this->setUpFakeFrontEnd();
        $this->importDataSet(__DIR__ . '/Fixtures/EventWithVacanciesWithSeparateDetailsPage.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);

        $result = $this->subject->getLinkToRegistrationPage($plugin, $event);

        self::assertStringContainsString('/registration', $result);
        self::assertStringContainsString('%5Bevent%5D=1', $result);
    }

    /**
     * @test
     */
    public function getLinkToRegistrationPageWithEventWithoutDateCreatesPrebookingLabel(): void
    {
        $plugin = $this->setUpFakeFrontEnd();
        $this->importDataSet(__DIR__ . '/Fixtures/EventWithoutDate.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);

        $result = $this->subject->getLinkToRegistrationPage($plugin, $event);

        self::assertStringContainsString($this->translate('label_onlinePrebooking'), $result);
    }

    /**
     * @test
     */
    public function getLinkToRegistrationPageWithFullyBookedWithoutDateCreatesRegistrationLabel(): void
    {
        $plugin = $this->setUpFakeFrontEnd();
        $this->importDataSet(__DIR__ . '/Fixtures/FullyBookedEventWithoutDate.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);

        $result = $this->subject->getLinkToRegistrationPage($plugin, $event);

        self::assertStringContainsString($this->translate('label_onlineRegistration'), $result);
    }

    /**
     * @test
     */
    public function getLinkToRegistrationPageWithFullyBookedWithQueueCreatesQueueLabel(): void
    {
        $plugin = $this->setUpFakeFrontEnd();
        $this->importDataSet(__DIR__ . '/Fixtures/FullyBookedEventWithQueue.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);

        $result = $this->subject->getLinkToRegistrationPage($plugin, $event);

        $expected = \sprintf($this->translate('label_onlineRegistrationOnQueue'), 0);
        self::assertStringContainsString($expected, $result);
    }

    // Tests concerning notifyAttendee

    /**
     * @test
     */
    public function notifyAttendeeSendsMailToAttendeesMailAddress(): void
    {
        $this->setUpFakeFrontEnd();
        $this->createEventWithOrganizer();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertArrayHasKey('foo@bar.com', $this->getToOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForAttendeeWithoutMailAddressNotSendsEmail(): void
    {
        $this->setUpFakeFrontEnd();
        $this->createEventWithOrganizer();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $controller = new DefaultController();
        $controller->init();

        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->seminarUid,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );
        $registration = new LegacyRegistration($registrationUid);

        $this->email->expects(self::never())->method('send');

        $this->subject->notifyAttendee($registration, $controller);
    }

    /**
     * @test
     */
    public function notifyAttendeeForSendConfirmationTrueCallsRegistrationEmailHookMethodsForPlainTextEmail(): void
    {
        $this->setUpFakeFrontEnd();
        $this->createEventWithOrganizer();
        $this->configuration->setAsBoolean('sendConfirmation', true);

        $registrationOld = $this->createRegistration();
        $registrationUid = $registrationOld->getUid();
        \assert($registrationUid > 0);
        $registration = MapperRegistry::get(RegistrationMapper::class)->find($registrationUid);

        $hook = $this->createMock(RegistrationEmail::class);
        $hook->expects(self::once())->method('modifyAttendeeEmail')->with(
            self::isInstanceOf(MailMessage::class),
            $registration,
            'confirmation'
        );
        $hook->expects(self::once())->method('modifyAttendeeEmailBodyPlainText')->with(
            self::isInstanceOf(Template::class),
            $registration,
            'confirmation'
        );
        $hook->expects(self::never())->method('modifyOrganizerEmail');
        $hook->expects(self::never())->method('modifyAdditionalEmail');

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][RegistrationEmail::class][] = $hookClass;
        $this->addMockedInstance($hookClass, $hook);

        $controller = new DefaultController();
        $controller->init();

        $this->subject->notifyAttendee($registrationOld, $controller);
    }

    /**
     * @test
     */
    public function notifyAttendeeForSendConfirmationTrueCallsRegistrationEmailHookMethodsForHtmlEmail(): void
    {
        $this->setUpFakeFrontEnd();
        $this->createEventWithOrganizer();
        $this->configuration->setAsBoolean('sendConfirmation', true);

        $registrationOld = $this->createRegistration();
        $registrationUid = $registrationOld->getUid();
        \assert($registrationUid > 0);
        $registration = MapperRegistry::get(RegistrationMapper::class)->find($registrationUid);

        $hook = $this->createMock(RegistrationEmail::class);
        $hook->expects(self::once())->method('modifyAttendeeEmail')->with(
            self::isInstanceOf(MailMessage::class),
            $registration,
            'confirmation'
        );
        $hook->expects(self::once())->method('modifyAttendeeEmailBodyPlainText')->with(
            self::isInstanceOf(Template::class),
            $registration,
            'confirmation'
        );
        $hook->expects(self::once())->method('modifyAttendeeEmailBodyHtml')->with(
            self::isInstanceOf(Template::class),
            $registration,
            'confirmation'
        );
        $hook->expects(self::never())->method('modifyOrganizerEmail');
        $hook->expects(self::never())->method('modifyAdditionalEmail');

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][RegistrationEmail::class][] = $hookClass;
        $this->addMockedInstance($hookClass, $hook);

        $controller = new DefaultController();
        $controller->init();

        $this->subject->notifyAttendee($registrationOld, $controller);
    }

    /**
     * @test
     */
    public function notifyAttendeemailSubjectContainsConfirmationSubject(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $controller = new DefaultController();
        $controller->init();

        $this->createEventWithOrganizer();
        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringContainsString(
            $this->translate('email_confirmationSubject'),
            $this->email->getSubject()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeEmailBodyContainsEventTitle(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $controller = new DefaultController();
        $controller->init();

        $this->createEventWithOrganizer();
        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringContainsString('test event', $this->email->getTextBody());
    }

    /**
     * @test
     */
    public function notifyAttendeeEmailBodyNotContainsRawTemplateMarkers(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $controller = new DefaultController();
        $controller->init();

        $this->createEventWithOrganizer();
        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertNotContainsRawLabelKey($this->email->getTextBody());
    }

    /**
     * @test
     */
    public function notifyAttendeeEmailBodyNotContainsSpaceBeforeComma(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $controller = new DefaultController();
        $controller->init();

        $this->createEventWithOrganizer();
        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringNotContainsString(' ,', $this->email->getTextBody());
    }

    /**
     * @test
     */
    public function notifyAttendeeEmailBodyContainsRegistrationFood(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $controller = new DefaultController();
        $controller->init();

        $this->createEventWithOrganizer();
        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringContainsString('something nice to eat', $this->email->getTextBody());
    }

    /**
     * @test
     */
    public function notifyAttendeeEmailBodyContainsRegistrationAccommodation(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $controller = new DefaultController();
        $controller->init();

        $this->createEventWithOrganizer();
        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringContainsString('a nice, dry place', $this->email->getTextBody());
    }

    /**
     * @test
     */
    public function notifyAttendeeEmailBodyContainsRegistrationInterests(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $controller = new DefaultController();
        $controller->init();

        $this->createEventWithOrganizer();
        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringContainsString('learning Ruby on Rails', $this->email->getTextBody());
    }

    /**
     * @test
     */
    public function notifyAttendeemailSubjectContainsEventTitle(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $controller = new DefaultController();
        $controller->init();

        $this->createEventWithOrganizer();
        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringContainsString(
            'test event',
            $this->email->getSubject()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeSetsTypo3DefaultFromAddressAsSender(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $controller = new DefaultController();
        $controller->init();

        $this->createEventWithOrganizer();
        $registration = $this->createRegistration();

        $defaultMailFromAddress = 'system-foo@example.com';
        $defaultMailFromName = 'Mr. Default';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = $defaultMailFromAddress;
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = $defaultMailFromName;

        $this->subject->notifyAttendee($registration, $controller);

        self::assertSame([$defaultMailFromAddress => $defaultMailFromName], $this->getFromOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeSetsOrganizerAsReplyTo(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $controller = new DefaultController();
        $controller->init();

        $this->createEventWithOrganizer();
        $registration = $this->createRegistration();

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'system-foo@example.com';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = 'Mr. Default';

        $this->subject->notifyAttendee($registration, $controller);

        self::assertSame(['mail@example.com' => 'test organizer'], $this->getReplyToOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeWithoutTypo3DefaultFromAddressSetsOrganizerAsSender(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $controller = new DefaultController();
        $controller->init();

        $this->createEventWithOrganizer();
        $registration = $this->createRegistration();

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = '';

        $this->subject->notifyAttendee($registration, $controller);

        self::assertSame(['mail@example.com' => 'test organizer'], $this->getFromOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeHasHtmlBody(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $controller = new DefaultController();
        $controller->init();

        $this->createEventWithOrganizer();
        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringContainsString('<html', (string)$this->email->getHtmlBody());
    }

    /**
     * @test
     */
    public function notifyAttendeeForTextMailSetHasNoUnreplacedMarkers(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $controller = new DefaultController();
        $controller->init();

        $this->createEventWithOrganizer();
        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringNotContainsString('###', $this->email->getTextBody());
    }

    /**
     * @test
     */
    public function notifyAttendeeForHtmlMailHasNoUnreplacedMarkers(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $controller = new DefaultController();
        $controller->init();

        $this->createEventWithOrganizer();
        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringNotContainsString('###', (string)$this->email->getHtmlBody());
    }

    /**
     * @test
     */
    public function notifyAttendeeForHtmlMailsContainsNameOfUserInBody(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);

        $this->createEventWithOrganizer();
        $registration = $this->createRegistration();
        $user = $registration->getFrontEndUser();
        self::assertInstanceOf(FrontEndUser::class, $user);
        $userUid = $user->getUid();
        \assert($userUid > 0);
        $this->testingFramework->changeRecord(
            'fe_users',
            $userUid,
            ['email' => 'foo@bar.com']
        );
        $controller = new DefaultController();
        $controller->init();

        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringContainsString('Harry Callagan', (string)$this->email->getHtmlBody());
    }

    /**
     * @test
     */
    public function notifyAttendeeForHtmlMailsHasLinkToSeminarInBody(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);

        $this->createEventWithOrganizer();
        $registration = $this->createRegistration();
        $registration->getFrontEndUser()->setData(
            ['email' => 'foo@bar.com']
        );
        $controller = new DefaultController();
        $controller->init();

        $this->subject->notifyAttendee($registration, $controller);
        $seminarLink = 'https://singleview.example.com/';

        self::assertStringContainsString('<a href="' . $seminarLink, (string)$this->email->getHtmlBody());
    }

    /**
     * @test
     */
    public function notifyAttendeeAppendsOrganizersFooterToMailTextBody(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $controller = new DefaultController();
        $controller->init();

        $this->createEventWithOrganizer();
        $registration = $this->createRegistration();

        $footer = 'organizer footer';
        $this->testingFramework->changeRecord(
            'tx_seminars_organizers',
            $this->organizerUid,
            ['email_footer' => $footer]
        );

        $this->subject->notifyAttendee($registration, $controller);
        $result = $this->email->getTextBody();

        self::assertIsString($result);
        self::assertStringContainsString("\n-- \n" . $footer, $result);
    }

    /**
     * @test
     */
    public function notifyAttendeeKeepsLinebreaksInOrganizerFooterInTextBody(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);

        $controller = new DefaultController();
        $controller->init();

        $this->createEventWithOrganizer();
        $registration = $this->createRegistration();

        $footer = "organizer\nfooter";
        $this->testingFramework->changeRecord(
            'tx_seminars_organizers',
            $this->organizerUid,
            ['email_footer' => $footer]
        );

        $this->subject->notifyAttendee($registration, $controller);
        $result = $this->email->getTextBody();

        self::assertIsString($result);
        self::assertStringContainsString("\n-- \n" . $footer, $result);
    }

    /**
     * @test
     */
    public function notifyAttendeeAppendsOrganizersFooterToMailHtmlBody(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $controller = new DefaultController();
        $controller->init();

        $this->createEventWithOrganizer();
        $registration = $this->createRegistration();

        $footer = 'organizer footer';
        $this->testingFramework->changeRecord(
            'tx_seminars_organizers',
            $this->organizerUid,
            ['email_footer' => $footer]
        );

        $this->subject->notifyAttendee($registration, $controller);
        $result = $this->email->getHtmlBody();

        self::assertIsString($result);
        self::assertStringContainsString($footer, $result);
    }

    /**
     * @test
     */
    public function notifyAttendeeConvertsLinebreaksInOrganizerFooterInTextBody(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);

        $controller = new DefaultController();
        $controller->init();

        $this->createEventWithOrganizer();
        $registration = $this->createRegistration();

        $footer = "organizer\nfooter";
        $this->testingFramework->changeRecord(
            'tx_seminars_organizers',
            $this->organizerUid,
            ['email_footer' => $footer]
        );

        $this->subject->notifyAttendee($registration, $controller);
        $result = $this->email->getHtmlBody();

        self::assertIsString($result);
        self::assertStringContainsString("organizer<br />\nfooter", $result);
    }

    /**
     * @test
     */
    public function notifyAttendeeForConfirmedEventNotHasPlannedDisclaimer(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->createEventWithOrganizer();
        $registration = $this->createRegistration();
        $registration->getSeminarObject()->setStatus(
            EventInterface::STATUS_CONFIRMED
        );

        $controller = new DefaultController();
        $controller->init();

        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringNotContainsString(
            $this->translate('label_planned_disclaimer'),
            $this->email->getTextBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForCancelledEventNotHasPlannedDisclaimer(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->createEventWithOrganizer();
        $registration = $this->createRegistration();
        $registration->getSeminarObject()->setStatus(
            EventInterface::STATUS_CANCELED
        );

        $controller = new DefaultController();
        $controller->init();

        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringNotContainsString(
            $this->translate('label_planned_disclaimer'),
            $this->email->getTextBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlannedEventDisplaysPlannedDisclaimer(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->createEventWithOrganizer();
        $registration = $this->createRegistration();
        $registration->getSeminarObject()->setStatus(
            EventInterface::STATUS_PLANNED
        );

        $controller = new DefaultController();
        $controller->init();

        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringContainsString(
            $this->translate('label_planned_disclaimer'),
            $this->email->getTextBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeWithHiddenDisclaimerFieldAndPlannedEventHidesPlannedDisclaimer(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->configuration->setAsString('hideFieldsInThankYouMail', 'planned_disclaimer');
        $this->createEventWithOrganizer();
        $registration = $this->createRegistration();
        $registration->getSeminarObject()->setStatus(
            EventInterface::STATUS_PLANNED
        );

        $controller = new DefaultController();
        $controller->init();

        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringNotContainsString(
            $this->translate('label_planned_disclaimer'),
            $this->email->getTextBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForHtmlMailsHasCssStylesFromFile(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->configuration->setAsString(
            'cssFileForAttendeeMail',
            'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
        );

        $controller = new DefaultController();
        $controller->init();

        $this->createEventWithOrganizer();
        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringContainsString('style=', (string)$this->email->getHtmlBody());
    }

    /**
     * @test
     */
    public function notifyAttendeeEmailBodyCanContainAttendeesNames(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $controller = new DefaultController();
        $controller->init();

        $this->createEventWithOrganizer();
        $registration = $this->createRegistration();
        $registration->setAttendeesNames('foo1 foo2');
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringContainsString('foo1 foo2', $this->email->getTextBody());
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlainTextMailEnumeratesAttendeesNames(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $controller = new DefaultController();
        $controller->init();

        $this->createEventWithOrganizer();
        $registration = $this->createRegistration();
        $registration->setAttendeesNames("foo1\nfoo2");
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringContainsString("1. foo1\n2. foo2", $this->email->getTextBody());
    }

    /**
     * @test
     */
    public function notifyAttendeeForHtmlMailReturnsAttendeesNames(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->configuration->setAsString(
            'cssFileForAttendeeMail',
            'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
        );

        $controller = new DefaultController();
        $controller->init();

        $this->createEventWithOrganizer();
        $registration = $this->createRegistration();
        $registration->setAttendeesNames("foo1\nfoo2");
        $this->subject->notifyAttendee($registration, $controller);

        $emailBody = (string)$this->email->getHtmlBody();

        self::assertStringContainsString('foo1', $emailBody);
        self::assertStringContainsString('foo2', $emailBody);
    }

    /**
     * @test
     */
    public function notifyAttendeeCanSendPlaceTitleInMailBody(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);

        $this->createEventWithOrganizer();
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'foo_place']
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringContainsString('foo_place', $this->email->getTextBody());
    }

    /**
     * @test
     */
    public function notifyAttendeeCanSendPlaceAddressInMailBody(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);

        $this->createEventWithOrganizer();
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['address' => 'foo_street']
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringContainsString('foo_street', $this->email->getTextBody());
    }

    /**
     * @test
     */
    public function notifyAttendeeForEventWithNoPlaceSendsWillBeAnnouncedMessage(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $controller = new DefaultController();
        $controller->init();

        $this->createEventWithOrganizer();
        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringContainsString(
            $this->translate('message_willBeAnnounced'),
            $this->email->getTextBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlainTextMailSeparatesPlacesTitleAndAddressWithLinefeed(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->createEventWithOrganizer();
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'place_title', 'address' => 'place_address']
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringContainsString(
            "place_title\nplace_address",
            $this->email->getTextBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForHtmlMailHasPlacesTitleAndAddress(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->configuration->setAsString(
            'cssFileForAttendeeMail',
            'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
        );

        $this->createEventWithOrganizer();
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'place_title', 'address' => 'place_address']
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        $emailBody = (string)$this->email->getHtmlBody();

        self::assertStringContainsString('place_title', $emailBody);
        self::assertStringContainsString('place_address', $emailBody);
    }

    /**
     * @test
     */
    public function notifyAttendeeStripsHtmlTagsFromPlaceAddress(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->createEventWithOrganizer();
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'place_title', 'address' => 'place<h2>_address</h2>']
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringContainsString("place_title\nplace_address", $this->email->getTextBody());
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressReplacesLineFeedsWithSpaces(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->createEventWithOrganizer();
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['address' => "address1\naddress2"]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringContainsString('address1 address2', $this->email->getTextBody());
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressReplacesCarriageReturnsWithSpaces(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->createEventWithOrganizer();
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['address' => 'address1' . "\r" . 'address2']
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringContainsString('address1 address2', $this->email->getTextBody());
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressReplacesCarriageReturnAndLineFeedWithOneSpace(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->createEventWithOrganizer();
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['address' => "address1\r\naddress2"]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringContainsString('address1 address2', $this->email->getTextBody());
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressReplacesMultipleCarriageReturnsWithOneSpace(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->createEventWithOrganizer();
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['address' => 'address1' . "\r" . "\r" . 'address2']
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringContainsString('address1 address2', $this->email->getTextBody());
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndPlainTextMailsReplacesMultipleLineFeedsWithSpaces(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->createEventWithOrganizer();
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['address' => "address1\n\naddress2"]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringContainsString('address1 address2', $this->email->getTextBody());
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndHtmlMailsReplacesMultipleLineFeedsWithSpaces(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->configuration->setAsString(
            'cssFileForAttendeeMail',
            'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
        );

        $this->createEventWithOrganizer();
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['address' => "address1\n\naddress2"]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringContainsString('address1 address2', (string)$this->email->getHtmlBody());
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressReplacesMultipleLineFeedAndCarriageReturnsWithSpaces(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->configuration->setAsString(
            'cssFileForAttendeeMail',
            'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
        );

        $this->createEventWithOrganizer();
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['address' => "address1\naddress2\r\r\naddress3"]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringContainsString('address1 address2 address3', $this->email->getTextBody());
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndPlainTextMailsSendsCityOfPlace(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->createEventWithOrganizer();
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['city' => 'footown']
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringContainsString('footown', $this->email->getTextBody());
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndPlainTextMailsSendsZipAndCityOfPlace(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->createEventWithOrganizer();
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['zip' => '12345', 'city' => 'footown']
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringContainsString('12345 footown', $this->email->getTextBody());
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndPlainTextMailsSendsCountryOfPlace(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);

        $this->importStaticData();
        $this->createEventWithOrganizer();
        $mapper = MapperRegistry::get(CountryMapper::class);
        $country = $mapper->find(54);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['city' => 'footown', 'country' => $country->getIsoAlpha2Code()]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringContainsString($country->getLocalShortName(), $this->email->getTextBody());
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndPlainTextMailsSeparatesAddressAndCityWithNewline(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->createEventWithOrganizer();
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['address' => 'address', 'city' => 'footown']
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringContainsString("address\nfootown", $this->email->getTextBody());
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndHtmlMailsHasAddressAndCity(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->configuration->setAsString(
            'cssFileForAttendeeMail',
            'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
        );

        $this->createEventWithOrganizer();
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['address' => 'address', 'city' => 'footown']
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        $emailBody = (string)$this->email->getHtmlBody();

        self::assertStringContainsString('address', $emailBody);
        self::assertStringContainsString('footown', $emailBody);
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressWithCountryAndCitySeparatesCountryAndCityWithComma(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);

        $this->importStaticData();
        $this->createEventWithOrganizer();
        $mapper = MapperRegistry::get(CountryMapper::class);
        $country = $mapper->find(54);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            [
                'address' => 'address',
                'city' => 'footown',
                'country' => $country->getIsoAlpha2Code(),
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringContainsString(
            'footown, ' . $country->getLocalShortName(),
            $this->email->getTextBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressWithCityAndNoCountryNotAddsSurplusCommaAfterCity(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->createEventWithOrganizer();
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['address' => 'address', 'city' => 'footown']
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        self::assertStringNotContainsString('footown,', $this->email->getTextBody());
    }

    /**
     * Checks that $string does not contain a raw label key.
     *
     * @param string $string
     */
    private static function assertNotContainsRawLabelKey(string $string): void
    {
        self::assertStringNotContainsString('_', $string);
        self::assertStringNotContainsString('salutation', $string);
        self::assertStringNotContainsString('formal', $string);
    }

    /**
     * @test
     */
    public function notifyAttendeeIncludesCalendarInviteInEmail(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->createEventWithOrganizer();

        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        $icsAttachments = $this->filterEmailAttachmentsByType($this->email, 'text/calendar');

        self::assertCount(1, $icsAttachments);
        $firstIcsAttachment = $icsAttachments[0];
        self::assertInstanceOf(DataPart::class, $firstIcsAttachment);
        self::assertSame('text', $firstIcsAttachment->getMediaType());
        self::assertStringStartsWith('calendar', $firstIcsAttachment->getMediaSubtype());
    }

    /**
     * @test
     */
    public function notifyAttendeeIncludesEventTitleAsSummaryInCalendarInvite(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->createEventWithOrganizer();

        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        $icsAttachments = $this->filterEmailAttachmentsByType($this->email, 'text/calendar');
        $firstIcsAttachment = $icsAttachments[0] ?? null;
        self::assertInstanceOf(DataPart::class, $firstIcsAttachment);

        $body = $firstIcsAttachment->getBody();
        self::assertStringContainsString('SUMMARY:test event', $body);
    }

    /**
     * @test
     */
    public function notifyAttendeeForEventWithBeginDateHasStartDateInCalendarInvite(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $beginDate = $this->now + 1000;
        $this->createEventWithOrganizer(['begin_date' => $beginDate]);

        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        $icsAttachments = $this->filterEmailAttachmentsByType($this->email, 'text/calendar');
        $firstIcsAttachment = $icsAttachments[0] ?? null;
        self::assertInstanceOf(DataPart::class, $firstIcsAttachment);

        $body = $firstIcsAttachment->getBody();
        self::assertStringContainsString('DTSTART:' . $this->formatDateForCalendar($beginDate), $body);
    }

    /**
     * @test
     */
    public function notifyAttendeeForEventWithoutBeginDateHasNoStartDateInCalendarInvite(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->createEventWithOrganizer(['begin_date' => 0]);

        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        $icsAttachments = $this->filterEmailAttachmentsByType($this->email, 'text/calendar');
        $firstIcsAttachment = $icsAttachments[0] ?? null;
        self::assertInstanceOf(DataPart::class, $firstIcsAttachment);

        $body = $firstIcsAttachment->getBody();
        self::assertStringNotContainsString('DTSTART:', $body);
    }

    /**
     * @test
     */
    public function notifyAttendeeForEventWithEndDateHasEndDateInCalendarInvite(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $endDate = $this->now + 2000;
        $this->createEventWithOrganizer(['end_date' => $endDate]);

        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        $icsAttachments = $this->filterEmailAttachmentsByType($this->email, 'text/calendar');
        $firstIcsAttachment = $icsAttachments[0] ?? null;
        self::assertInstanceOf(DataPart::class, $firstIcsAttachment);

        $body = $firstIcsAttachment->getBody();
        self::assertStringContainsString('DTEND:' . $this->formatDateForCalendar($endDate), $body);
    }

    /**
     * @test
     */
    public function notifyAttendeeForEventWithoutEndDateHasNoEndDateInCalendarInvite(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->createEventWithOrganizer(['end_date' => 0]);

        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        $icsAttachments = $this->filterEmailAttachmentsByType($this->email, 'text/calendar');
        $firstIcsAttachment = $icsAttachments[0] ?? null;
        self::assertInstanceOf(DataPart::class, $firstIcsAttachment);

        $body = $firstIcsAttachment->getBody();
        self::assertStringNotContainsString('DTEND:', $body);
    }

    /**
     * @test
     */
    public function notifyAttendeeForOnSiteEventWithoutVenuesHasNoLocationInCalendarInvite(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->createEventWithOrganizer();

        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        $icsAttachments = $this->filterEmailAttachmentsByType($this->email, 'text/calendar');
        $firstIcsAttachment = $icsAttachments[0] ?? null;
        self::assertInstanceOf(DataPart::class, $firstIcsAttachment);

        $body = $firstIcsAttachment->getBody();
        self::assertStringNotContainsString('LOCATION:', $body);
    }

    /**
     * @test
     */
    public function notifyAttendeeForOnSiteEventWithOneVenueHasVenueLocationInCalendarInvite(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->createEventWithOrganizer();
        $venueTitle = 'Hotel California';
        $venueAddress = 'Born in the USA';
        $venueUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => $venueTitle, 'address' => $venueAddress]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $venueUid,
            'place'
        );

        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        $icsAttachments = $this->filterEmailAttachmentsByType($this->email, 'text/calendar');
        $firstIcsAttachment = $icsAttachments[0] ?? null;
        self::assertInstanceOf(DataPart::class, $firstIcsAttachment);

        $body = $firstIcsAttachment->getBody();
        self::assertStringContainsString('LOCATION:' . $venueTitle . ', ' . $venueAddress, $body);
    }

    /**
     * @test
     */
    public function notifyAttendeeForOnSiteEventWithTwoVenuesHasNoLocationInCalendarInvite(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->createEventWithOrganizer();
        $venueUid1 = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'Hotel California', 'address' => 'Born in the USA']
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $venueUid1,
            'place'
        );
        $venueUid2 = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'JH Bonn', 'address' => 'Bonn']
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $venueUid2,
            'place'
        );
        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        $icsAttachments = $this->filterEmailAttachmentsByType($this->email, 'text/calendar');
        $firstIcsAttachment = $icsAttachments[0] ?? null;
        self::assertInstanceOf(DataPart::class, $firstIcsAttachment);

        $body = $firstIcsAttachment->getBody();
        self::assertStringNotContainsString('LOCATION:', $body);
    }

    /**
     * @test
     */
    public function notifyAttendeeForHybridEventWithoutVenuesAndWithoutWebinarUrlHasNoLocationInCalendarInvite(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->createEventWithOrganizer(['event_format' => EventDateInterface::EVENT_FORMAT_HYBRID]);

        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        $icsAttachments = $this->filterEmailAttachmentsByType($this->email, 'text/calendar');
        $firstIcsAttachment = $icsAttachments[0] ?? null;
        self::assertInstanceOf(DataPart::class, $firstIcsAttachment);

        $body = $firstIcsAttachment->getBody();
        self::assertStringNotContainsString('LOCATION', $body);
    }

    /**
     * @test
     */
    public function notifyAttendeeForHybridEventWithOneVenueAndWithoutWebinarUrlHasVenueLocationInCalendarInvite(): void
    {
        $this->setUpFakeFrontEnd();
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->createEventWithOrganizer(['event_format' => EventDateInterface::EVENT_FORMAT_HYBRID]);
        $venueTitle = 'Hotel California';
        $venueAddress = 'Born in the USA';
        $venueUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => $venueTitle, 'address' => $venueAddress]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $venueUid,
            'place'
        );
        $controller = new DefaultController();
        $controller->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $controller);

        $icsAttachments = $this->filterEmailAttachmentsByType($this->email, 'text/calendar');
        $firstIcsAttachment = $icsAttachments[0] ?? null;
        self::assertInstanceOf(DataPart::class, $firstIcsAttachment);

        $body = $firstIcsAttachment->getBody();
        self::assertStringContainsString('LOCATION:' . $venueTitle . ', ' . $venueAddress, $body);
    }
}
