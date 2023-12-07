<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Domain\Repository;

use OliverKlee\Seminars\Domain\Model\EventType;
use OliverKlee\Seminars\Domain\Model\NullEventType;
use OliverKlee\Seminars\Domain\Repository\EventTypeRepository;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\EventType
 * @covers \OliverKlee\Seminars\Domain\Repository\EventTypeRepository
 */
final class EventTypeRepositoryTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var EventTypeRepository
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        if ((new Typo3Version())->getMajorVersion() >= 11) {
            $this->subject = GeneralUtility::makeInstance(EventTypeRepository::class);
        } else {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $this->subject = $objectManager->get(EventTypeRepository::class);
        }
    }

    /**
     * @test
     */
    public function mapsAllModelFields(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventTypeRepository/EventTypeWithAllFields.xml');

        $result = $this->subject->findByUid(1);

        self::assertInstanceOf(EventType::class, $result);
        self::assertSame('Hands-on session', $result->getTitle());
    }

    /**
     * @test
     */
    public function findsRecordOnPages(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventTypeRepository/EventTypeOnPage.xml');

        $result = $this->subject->findAll();

        self::assertCount(1, $result);
    }

    /**
     * @test
     */
    public function sortRecordsByTitleInAscendingOrder(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventTypeRepository/TwoEventTypesInReverseOrder.xml');

        $result = $this->subject->findAll();

        self::assertCount(2, $result);
        $first = $result->getFirst();
        self::assertInstanceOf(EventType::class, $first);
        self::assertSame('Earlier', $first->getTitle());
    }

    /**
     * @test
     */
    public function findAllPlusNullEventTypeForNoDataReturnsNullEventType(): void
    {
        $result = $this->subject->findAllPlusNullEventType();

        self::assertCount(1, $result);
        self::assertInstanceOf(NullEventType::class, $result[0]);
    }

    /**
     * @test
     */
    public function findAllPlusNullEventTypeReturnsNullEventTypeAndRecords(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventTypeRepository/EventTypeWithAllFields.xml');

        $result = $this->subject->findAllPlusNullEventType();

        self::assertCount(2, $result);
        self::assertInstanceOf(NullEventType::class, $result[0]);
        self::assertInstanceOf(EventType::class, $result[1]);
    }

    /**
     * @test
     */
    public function findAllPlusNullEventTypeAlsoFindsRecordsOnPages(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventTypeRepository/EventTypeOnPage.xml');

        $result = $this->subject->findAllPlusNullEventType();

        self::assertCount(2, $result);
        self::assertInstanceOf(EventType::class, $result[1]);
    }
}
