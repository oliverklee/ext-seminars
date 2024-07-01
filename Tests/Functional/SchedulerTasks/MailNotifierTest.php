<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\SchedulerTasks;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Authentication\BackEndLoginManager;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\BackEndUser;
use OliverKlee\Seminars\SchedulerTasks\MailNotifier;
use OliverKlee\Seminars\SchedulerTasks\RegistrationDigest;
use OliverKlee\Seminars\Tests\Functional\Traits\LanguageHelper;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * @covers \OliverKlee\Seminars\SchedulerTasks\MailNotifier
 */
final class MailNotifierTest extends FunctionalTestCase
{
    use LanguageHelper;

    protected $coreExtensionsToLoad = ['scheduler'];

    protected $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var MailNotifier
     */
    private $subject;

    /**
     * @var LanguageService
     */
    private $languageService;

    /**
     * @var RegistrationDigest&MockObject
     */
    private $registrationDigestMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initializeBackEndLanguage();

        ConfigurationRegistry::getInstance()->set('plugin', new DummyConfiguration());
        ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', new DummyConfiguration());

        $user = new BackEndUser();
        $user->setData([]);
        BackEndLoginManager::getInstance()->setLoggedInUser($user);

        $objectManagerMock = $this->createMock(ObjectManager::class);
        GeneralUtility::setSingletonInstance(ObjectManager::class, $objectManagerMock);

        $this->registrationDigestMock = $this->createMock(RegistrationDigest::class);
        $objectManagerMock->method('get')->with(RegistrationDigest::class)->willReturn($this->registrationDigestMock);

        $this->subject = new MailNotifier();
    }

    protected function tearDown(): void
    {
        MapperRegistry::purgeInstance();
        BackEndLoginManager::purgeInstance();
        ConfigurationRegistry::purgeInstance();
        GeneralUtility::resetSingletonInstances([]);

        parent::tearDown();
    }

    // Basic tests

    /**
     * @test
     */
    public function classIsSchedulerTask(): void
    {
        self::assertInstanceOf(AbstractTask::class, $this->subject);
    }

    /**
     * @test
     */
    public function setConfigurationPageUidSetsConfigurationPageUid(): void
    {
        $uid = 42;
        $this->subject->setConfigurationPageUid($uid);

        $result = $this->subject->getConfigurationPageUid();

        self::assertSame($uid, $result);
    }

    /**
     * @test
     */
    public function executeWithoutPageConfigurationReturnsFalse(): void
    {
        $result = (new MailNotifier())->execute();

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function executeWithZeroPageConfigurationReturnsFalse(): void
    {
        $subject = new MailNotifier();
        $subject->setConfigurationPageUid(0);

        $result = $subject->execute();

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function executeWithPageConfigurationReturnsTrue(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/MailNotifierConfiguration.xml');

        $subject = new MailNotifier();
        $subject->setConfigurationPageUid(1);

        $result = $subject->execute();

        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function executeWithPageConfigurationCallsAllSeparateSteps(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/MailNotifierConfiguration.xml');
        $subject = $this->createPartialMock(
            MailNotifier::class,
            ['sendEventTakesPlaceReminders', 'sendCancellationDeadlineReminders', 'automaticallyChangeEventStatuses']
        );
        $subject->setConfigurationPageUid(1);

        $subject->expects(self::once())->method('sendEventTakesPlaceReminders');
        $subject->expects(self::once())->method('sendCancellationDeadlineReminders');
        $subject->expects(self::once())->method('automaticallyChangeEventStatuses');

        $subject->execute();
    }

    /**
     * @test
     */
    public function executeWithoutPageConfigurationNotCallsAnySeparateStep(): void
    {
        $subject = $this->createPartialMock(
            MailNotifier::class,
            ['sendEventTakesPlaceReminders', 'sendCancellationDeadlineReminders', 'automaticallyChangeEventStatuses']
        );
        $subject->setConfigurationPageUid(0);

        $subject->expects(self::never())->method('sendEventTakesPlaceReminders');
        $subject->expects(self::never())->method('sendCancellationDeadlineReminders');
        $subject->expects(self::never())->method('automaticallyChangeEventStatuses');

        $subject->execute();
    }

    /**
     * @test
     */
    public function executeWithPageConfigurationExecutesRegistrationDigest(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/MailNotifierConfiguration.xml');
        $subject = $this->createPartialMock(
            MailNotifier::class,
            ['sendEventTakesPlaceReminders', 'sendCancellationDeadlineReminders', 'automaticallyChangeEventStatuses']
        );
        $subject->setConfigurationPageUid(1);
        $this->registrationDigestMock->expects(self::once())->method('execute');

        $subject->execute();
    }
}
