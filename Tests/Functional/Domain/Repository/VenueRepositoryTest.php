<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Domain\Repository;

use OliverKlee\Seminars\Domain\Model\Venue;
use OliverKlee\Seminars\Domain\Repository\VenueRepository;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\Venue
 * @covers \OliverKlee\Seminars\Domain\Repository\VenueRepository
 */
final class VenueRepositoryTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var VenueRepository
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->get(VenueRepository::class);
    }

    /**
     * @test
     */
    public function mapsAllModelFields(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/VenueRepository/VenueWithAllFields.xml');

        $result = $this->subject->findByUid(1);

        self::assertInstanceOf(Venue::class, $result);
        self::assertSame('JH Köln-Deutz', $result->getTitle());
        self::assertSame('Alex', $result->getContactPerson());
        self::assertSame('alex@example.com', $result->getEmailAddress());
        self::assertSame('+49 1234 56789', $result->getPhoneNumber());
        self::assertSame('Markplatz 1, 12345 Bonn', $result->getFullAddress());
    }

    /**
     * @test
     */
    public function findsRecordOnPages(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/VenueRepository/VenueOnPage.xml');

        $result = $this->subject->findAll();

        self::assertCount(1, $result);
    }

    /**
     * @test
     */
    public function sortRecordsByTitleInAscendingOrder(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/VenueRepository/TwoVenuesInReverseOrder.xml');

        $result = $this->subject->findAll();

        self::assertCount(2, $result);
        $first = $result->getFirst();
        self::assertInstanceOf(Venue::class, $first);
        self::assertSame('Earlier', $first->getTitle());
    }
}
