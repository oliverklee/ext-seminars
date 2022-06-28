<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Service;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Authentication\FrontEndLoginManager;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\FrontEnd\DefaultController;
use OliverKlee\Seminars\Mapper\FrontEndUserMapper;
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

    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

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

        $this->subject = RegistrationManager::getInstance();
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

    /**
     * @return positive-int user UID
     */
    private function createAndLogInUser(): int
    {
        $userUid = 1;
        $this->importDataSet(__DIR__ . '/Fixtures/FrontEndUser.xml');
        $this->logInUser($userUid);

        return $userUid;
    }

    private function logInUser(int $uid): void
    {
        $user = MapperRegistry::get(FrontEndUserMapper::class)->find($uid);
        FrontEndLoginManager::getInstance()->logInUser($user);
    }

    private function setUpFrontEndForRegistrationLink(): DefaultController
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationAndLoginPage.xml');
        $this->testingFramework->createFakeFrontEnd(1);
        $controller = new DefaultController();
        $controller->cObj = $this->getFrontEndController()->cObj;
        $controller->conf = ['loginPID' => '2', 'registerPID' => '3'];

        return $controller;
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
        self::assertRegExp($expectedExpression, $this->getTextBodyOfEmail($this->email));
    }

    // Tests concerning getRegistrationLink

    /**
     * @test
     */
    public function getRegistrationLinkWithoutLoginAndPastEventReturnsEmptyString(): void
    {
        $plugin = $this->setUpFrontEndForRegistrationLink();

        $this->importDataSet(__DIR__ . '/Fixtures/PastEventWithVacancies.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);

        $result = $this->subject->getRegistrationLink($plugin, $event);

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function getRegistrationLinkWithoutLoginWithFutureEventWithPassedDeadlineReturnsEmptyString(): void
    {
        $plugin = $this->setUpFrontEndForRegistrationLink();

        $this->importDataSet(__DIR__ . '/Fixtures/FutureEventWithPassedRegistrationDeadline.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);

        $result = $this->subject->getRegistrationLink($plugin, $event);

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function getRegistrationLinkWithoutLoginAndPriceOnRequestReturnsEmptyString(): void
    {
        $plugin = $this->setUpFrontEndForRegistrationLink();

        $this->importDataSet(__DIR__ . '/Fixtures/EventWithPriceOnRequest.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);

        $result = $this->subject->getRegistrationLink($plugin, $event);

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function getRegistrationLinkWithoutLoginAndFullyBookedEventReturnsEmptyString(): void
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
    public function getRegistrationLinkWithoutLoginAndEventWithVacanciesReturnsLoginLink(): void
    {
        $plugin = $this->setUpFrontEndForRegistrationLink();

        $this->importDataSet(__DIR__ . '/Fixtures/EventWithVacancies.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);

        $result = $this->subject->getRegistrationLink($plugin, $event);

        self::assertStringContainsString('/login', $result);
    }

    /**
     * @test
     */
    public function getRegistrationLinkWithoutLoginAndEventWithUnlimitedVacanciesReturnsLoginLink(): void
    {
        $plugin = $this->setUpFrontEndForRegistrationLink();

        $this->importDataSet(__DIR__ . '/Fixtures/EventWithUnlimitedVacancies.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);

        $result = $this->subject->getRegistrationLink($plugin, $event);

        self::assertStringContainsString('/login', $result);
    }

    /**
     * @test
     */
    public function getRegistrationLinkWithoutLoginAndFullyBookedEventWithQueueEnabledReturnsLoginLink(): void
    {
        $plugin = $this->setUpFrontEndForRegistrationLink();

        $this->importDataSet(__DIR__ . '/Fixtures/FullyBookedEventWithQueue.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);

        $result = $this->subject->getRegistrationLink($plugin, $event);

        self::assertStringContainsString('/login', $result);
    }

    /**
     * @test
     */
    public function getRegistrationLinkWithLoginAndEventWithVacanciesReturnsLinkToRegistrationPage(): void
    {
        $plugin = $this->setUpFrontEndForRegistrationLink();
        $this->createAndLogInUser();

        $this->importDataSet(__DIR__ . '/Fixtures/EventWithVacancies.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);

        $result = $this->subject->getRegistrationLink($plugin, $event);

        self::assertStringContainsString('/registration', $result);
    }

    /**
     * @test
     */
    public function getRegistrationLinkWithLoginAndEventWithVacanciesReturnsLinkWithEventUid(): void
    {
        $plugin = $this->setUpFrontEndForRegistrationLink();
        $this->createAndLogInUser();

        $this->importDataSet(__DIR__ . '/Fixtures/EventWithVacancies.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);

        $result = $this->subject->getRegistrationLink($plugin, $event);

        self::assertStringContainsString('%5Bseminar%5D=1', $result);
    }

    /**
     * @test
     */
    public function getRegistrationLinkWithLoginAndFullyBookedEventReturnsEmptyString(): void
    {
        $plugin = $this->setUpFrontEndForRegistrationLink();
        $this->createAndLogInUser();

        $this->importDataSet(__DIR__ . '/Fixtures/FullyBookedEvent.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);

        $result = $this->subject->getRegistrationLink($plugin, $event);

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function getRegistrationLinkWithLoginAndEventWithUnlimitedVacanciesReturnsLinkWithEventUid(): void
    {
        $plugin = $this->setUpFrontEndForRegistrationLink();
        $this->createAndLogInUser();

        $this->importDataSet(__DIR__ . '/Fixtures/EventWithUnlimitedVacancies.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);

        $result = $this->subject->getRegistrationLink($plugin, $event);

        self::assertStringContainsString('%5Bseminar%5D=1', $result);
    }

    /**
     * @test
     */
    public function getRegistrationLinkWithLoginAndFullyBookedEventWithQueueReturnsLinkWithEventUid(): void
    {
        $plugin = $this->setUpFrontEndForRegistrationLink();
        $this->createAndLogInUser();

        $this->importDataSet(__DIR__ . '/Fixtures/FullyBookedEventWithQueue.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);

        $result = $this->subject->getRegistrationLink($plugin, $event);

        self::assertStringContainsString('%5Bseminar%5D=1', $result);
    }

    // Tests concerning getLinkToRegistrationOrLoginPage

    /**
     * @test
     */
    public function getLinkToRegistrationOrLoginPageWithLoggedOutUserCreatesLinkToLoginPage(): void
    {
        $plugin = $this->setUpFrontEndForRegistrationLink();
        $this->importDataSet(__DIR__ . '/Fixtures/EventWithVacancies.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);

        $result = $this->subject->getLinkToRegistrationOrLoginPage($plugin, $event);

        self::assertStringContainsString('href="/login', $result);
    }

    /**
     * @test
     */
    public function getLinkToRegistrationOrLoginPageWithLoggedOutUserCreatesRedirectWithEventUid(): void
    {
        $plugin = $this->setUpFrontEndForRegistrationLink();
        $this->importDataSet(__DIR__ . '/Fixtures/EventWithVacancies.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);

        $result = $this->subject->getLinkToRegistrationOrLoginPage($plugin, $event);

        self::assertStringContainsString('%255Bseminar%255D%3D1', $result);
    }

    /**
     * @test
     */
    public function getLinkToRegistrationOrLoginPageWithLoggedInUserCreatesLinkToRegistrationPageWithEventUid(): void
    {
        $plugin = $this->setUpFrontEndForRegistrationLink();
        $this->importDataSet(__DIR__ . '/Fixtures/EventWithVacancies.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);
        $this->createAndLogInUser();

        $result = $this->subject->getLinkToRegistrationOrLoginPage($plugin, $event);

        self::assertStringContainsString('/registration', $result);
        self::assertStringContainsString('%5Bseminar%5D=1', $result);
    }

    /**
     * @test
     */
    public function getLinkToRegistrationOrLoginPageWithLoggedInUserCreatesLinkWithoutRedirect(): void
    {
        $plugin = $this->setUpFrontEndForRegistrationLink();
        $this->importDataSet(__DIR__ . '/Fixtures/EventWithVacancies.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);
        $this->createAndLogInUser();

        $result = $this->subject->getLinkToRegistrationOrLoginPage($plugin, $event);

        self::assertStringNotContainsString('redirect_url', $result);
    }

    /**
     * @test
     */
    public function getLinkToRegistrationOrLoginPageWithLoginAndSeparateDetailsPageCreatesLinkToRegistrationPage(): void
    {
        $plugin = $this->setUpFrontEndForRegistrationLink();
        $this->importDataSet(__DIR__ . '/Fixtures/EventWithVacanciesWithSeparateDetailsPage.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);
        $this->createAndLogInUser();

        $result = $this->subject->getLinkToRegistrationOrLoginPage($plugin, $event);

        self::assertStringContainsString('/registration', $result);
        self::assertStringContainsString('%5Bseminar%5D=1', $result);
    }

    /**
     * @test
     */
    public function getLinkToRegistrationOrLoginPageWithLoginAndEventWithoutDateCreatesPrebookingLabel(): void
    {
        $plugin = $this->setUpFrontEndForRegistrationLink();
        $this->importDataSet(__DIR__ . '/Fixtures/EventWithoutDate.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);
        $this->createAndLogInUser();

        $result = $this->subject->getLinkToRegistrationOrLoginPage($plugin, $event);

        self::assertStringContainsString($this->translate('label_onlinePrebooking'), $result);
    }

    /**
     * @test
     */
    public function getLinkToRegistrationOrLoginPageWithLoginAndFullyBookedWithoutDateCreatesRegistrationLabel(): void
    {
        $plugin = $this->setUpFrontEndForRegistrationLink();
        $this->importDataSet(__DIR__ . '/Fixtures/FullyBookedEventWithoutDate.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);
        $this->createAndLogInUser();

        $result = $this->subject->getLinkToRegistrationOrLoginPage($plugin, $event);

        self::assertStringContainsString($this->translate('label_onlineRegistration'), $result);
    }

    /**
     * @test
     */
    public function getLinkToRegistrationOrLoginPageWithoutLoginAndFullyBookedWithQueueCreatesQueueLabel(): void
    {
        $plugin = $this->setUpFrontEndForRegistrationLink();
        $this->importDataSet(__DIR__ . '/Fixtures/FullyBookedEventWithQueue.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);

        $result = $this->subject->getLinkToRegistrationOrLoginPage($plugin, $event);

        $expected = \sprintf($this->translate('label_onlineRegistrationOnQueue'), 0);
        self::assertStringContainsString($expected, $result);
    }

    /**
     * @test
     */
    public function getLinkToRegistrationOrLoginPageWithLoginAndFullyBookedWithQueueCreatesQueueLabel(): void
    {
        $plugin = $this->setUpFrontEndForRegistrationLink();
        $this->importDataSet(__DIR__ . '/Fixtures/FullyBookedEventWithQueue.xml');
        $event = LegacyEvent::fromUid(1);
        self::assertInstanceOf(LegacyEvent::class, $event);
        $this->createAndLogInUser();

        $result = $this->subject->getLinkToRegistrationOrLoginPage($plugin, $event);

        $expected = \sprintf($this->translate('label_onlineRegistrationOnQueue'), 0);
        self::assertStringContainsString($expected, $result);
    }
}
