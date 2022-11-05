<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Domain\Repository;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Domain\Model\Registration;
use OliverKlee\Seminars\Domain\Repository\RegistrationRepository;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\Registration
 * @covers \OliverKlee\Seminars\Domain\Repository\RegistrationRepository
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
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationRepository/RegistrationWithAllFields.xml');

        $result = $this->subject->findByUid(1);

        self::assertInstanceOf(Registration::class, $result);
        self::assertSame('some new registration', $result->getTitle());
    }

    /**
     * @test
     */
    public function findsRecordOnPages(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationRepository/RegistrationOnPage.xml');

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
}
