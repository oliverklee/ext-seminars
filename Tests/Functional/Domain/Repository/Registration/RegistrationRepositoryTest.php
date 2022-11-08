<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Domain\Repository\Registration;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\Seminars\Domain\Model\Event\EventDate;
use OliverKlee\Seminars\Domain\Model\Event\EventTopic;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Model\Registration\Registration;
use OliverKlee\Seminars\Domain\Repository\Registration\RegistrationRepository;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\Registration\Registration
 * @covers \OliverKlee\Seminars\Domain\Repository\Registration\RegistrationRepository
 */
final class RegistrationRepositoryTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var RegistrationRepository
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        if ((new Typo3Version())->getMajorVersion() >= 11) {
            $this->subject = GeneralUtility::makeInstance(RegistrationRepository::class);
        } else {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $this->subject = $objectManager->get(RegistrationRepository::class);
        }
    }

    /**
     * @test
     */
    public function mapsAllModelFields(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithAllFields.xml');

        $result = $this->subject->findByUid(1);

        self::assertInstanceOf(Registration::class, $result);
        self::assertSame('some new registration', $result->getTitle());
        self::assertNull($result->getEvent());
        self::assertNull($result->getUser());
    }

    /**
     * @test
     */
    public function findsRecordOnPages(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationOnPage.xml');

        $result = $this->subject->findAll();

        self::assertCount(1, $result);
    }

    /**
     * @test
     */
    public function persistAllPersistsAddedModels(): void
    {
        $registration = new Registration();

        $this->subject->add($registration);
        $this->subject->persistAll();

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_seminars_seminars');
        $query = 'SELECT * FROM tx_seminars_attendances WHERE uid = :uid';
        $result = $connection->executeQuery($query, ['uid' => $registration->getUid()]);
        if (\method_exists($result, 'fetchAssociative')) {
            $databaseRow = $result->fetchAssociative();
        } else {
            $databaseRow = $result->fetch();
        }

        self::assertIsArray($databaseRow);
    }

    /**
     * @test
     */
    public function mapsEventAssociationWithSingleEvent(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithSingleEvent.xml');

        $result = $this->subject->findByUid(1);

        self::assertInstanceOf(Registration::class, $result);
        self::assertInstanceOf(SingleEvent::class, $result->getEvent());
    }

    /**
     * @test
     */
    public function mapsEventAssociationWithEventDate(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithEventDate.xml');

        $result = $this->subject->findByUid(1);

        self::assertInstanceOf(Registration::class, $result);
        self::assertInstanceOf(EventDate::class, $result->getEvent());
    }

    /**
     * @test
     *
     * Note: This case usually should not happen. It is only possible if there are already registrations for a single
     * event of event topic, and the event then gets changed to an event topic.
     */
    public function mapsEventAssociationWithEventTopic(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithEventTopic.xml');

        $result = $this->subject->findByUid(1);

        self::assertInstanceOf(Registration::class, $result);
        self::assertInstanceOf(EventTopic::class, $result->getEvent());
    }

    /**
     * @test
     */
    public function mapsUserAssociation(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithUser.xml');

        $result = $this->subject->findByUid(1);

        self::assertInstanceOf(Registration::class, $result);
        self::assertInstanceOf(FrontendUser::class, $result->getUser());
    }
}
