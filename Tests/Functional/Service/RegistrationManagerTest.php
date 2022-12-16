<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Service;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\FrontEnd\DefaultController;
use OliverKlee\Seminars\OldModel\LegacyEvent;
use OliverKlee\Seminars\OldModel\LegacyRegistration;
use OliverKlee\Seminars\Service\RegistrationManager;
use OliverKlee\Seminars\Tests\Functional\Traits\LanguageHelper;
use OliverKlee\Seminars\Tests\Unit\Traits\EmailTrait;
use OliverKlee\Seminars\Tests\Unit\Traits\MakeInstanceTrait;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @covers \OliverKlee\Seminars\Service\RegistrationManager
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

    protected $testExtensionsToLoad = [
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->initializeBackEndLanguage();
        $this->configuration = new DummyConfiguration([]);
        ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', $this->configuration);

        $this->email = $this->createEmailMock();

        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = new RegistrationManager();
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

    private function setUpFrontEndForRegistrationLink(): DefaultController
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationPage.xml');
        $this->testingFramework->createFakeFrontEnd(1);
        $controller = new DefaultController();
        $controller->cObj = $this->getFrontEndController()->cObj;
        $controller->conf = ['registerPID' => '3'];

        return $controller;
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
        $plugin = $this->setUpFrontEndForRegistrationLink();

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
        $plugin = $this->setUpFrontEndForRegistrationLink();

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
        $plugin = $this->setUpFrontEndForRegistrationLink();

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
        $plugin = $this->setUpFrontEndForRegistrationLink();

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
        $plugin = $this->setUpFrontEndForRegistrationLink();

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
        $plugin = $this->setUpFrontEndForRegistrationLink();
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
        $plugin = $this->setUpFrontEndForRegistrationLink();
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
        $plugin = $this->setUpFrontEndForRegistrationLink();
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
        $plugin = $this->setUpFrontEndForRegistrationLink();
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
        $plugin = $this->setUpFrontEndForRegistrationLink();
        $this->importDataSet(__DIR__ . '/Fixtures/FullyBookedEventWithQueue.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);

        $result = $this->subject->getLinkToRegistrationPage($plugin, $event);

        $expected = \sprintf($this->translate('label_onlineRegistrationOnQueue'), 0);
        self::assertStringContainsString($expected, $result);
    }
}
