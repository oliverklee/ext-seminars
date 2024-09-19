<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Domain\Repository;

use OliverKlee\Seminars\Domain\Model\Speaker;
use OliverKlee\Seminars\Domain\Repository\SpeakerRepository;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\Speaker
 * @covers \OliverKlee\Seminars\Domain\Repository\SpeakerRepository
 */
final class SpeakerRepositoryTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var SpeakerRepository
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->get(SpeakerRepository::class);
    }

    /**
     * @test
     */
    public function mapsAllModelFields(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/SpeakerRepository/SpeakerWithAllFields.xml');

        $result = $this->subject->findByUid(1);

        self::assertInstanceOf(Speaker::class, $result);
        self::assertSame('Dan Chase', $result->getName());
        self::assertSame('dan@example.com', $result->getEmailAddress());
    }

    /**
     * @test
     */
    public function findsRecordOnPages(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/SpeakerRepository/SpeakerOnPage.xml');

        $result = $this->subject->findAll();

        self::assertCount(1, $result);
    }

    /**
     * @test
     */
    public function sortRecordsByTitleInAscendingOrder(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/SpeakerRepository/TwoSpeakersInReverseOrder.xml');

        $result = $this->subject->findAll();

        self::assertCount(2, $result);
        $first = $result->getFirst();
        self::assertInstanceOf(Speaker::class, $first);
        self::assertSame('Earlier', $first->getName());
    }
}
