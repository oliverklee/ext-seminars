<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Domain\Repository;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Domain\Model\Event;
use OliverKlee\Seminars\Domain\Repository\EventRepository;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\Event
 * @covers \OliverKlee\Seminars\Domain\Repository\EventRepository
 */
final class EventRepositoryTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var EventRepository
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        if ((new Typo3Version())->getMajorVersion() >= 11) {
            $this->subject = GeneralUtility::makeInstance(EventRepository::class);
        } else {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $this->subject = $objectManager->get(EventRepository::class);
        }
    }

    /**
     * @test
     */
    public function mapsAllModelFields(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventRepository/SingleEventWithAllFields.xml');

        $result = $this->subject->findByUid(1);

        self::assertInstanceOf(Event::class, $result);
        self::assertSame('Jousting', $result->getInternalTitle());
    }
}
