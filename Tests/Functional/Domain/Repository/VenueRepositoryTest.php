<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Domain\Repository;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Domain\Model\Venue;
use OliverKlee\Seminars\Domain\Repository\VenueRepository;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\Venue
 * @covers \OliverKlee\Seminars\Domain\Repository\VenueRepository
 */
final class VenueRepositoryTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var VenueRepository
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        if ((new Typo3Version())->getMajorVersion() >= 11) {
            $this->subject = GeneralUtility::makeInstance(VenueRepository::class);
        } else {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $this->subject = $objectManager->get(VenueRepository::class);
        }
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
}
