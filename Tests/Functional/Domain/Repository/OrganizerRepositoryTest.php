<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Domain\Repository;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Domain\Model\Organizer;
use OliverKlee\Seminars\Domain\Repository\OrganizerRepository;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\Organizer
 * @covers \OliverKlee\Seminars\Domain\Repository\OrganizerRepository
 */
final class OrganizerRepositoryTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var OrganizerRepository
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        if ((new Typo3Version())->getMajorVersion() >= 11) {
            $this->subject = GeneralUtility::makeInstance(OrganizerRepository::class);
        } else {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $this->subject = $objectManager->get(OrganizerRepository::class);
        }
    }

    /**
     * @test
     */
    public function mapsAllModelFields(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/OrganizerRepository/OrganizerWithAllFields.xml');

        $result = $this->subject->findByUid(1);

        self::assertInstanceOf(Organizer::class, $result);
        self::assertSame('Dan Chase', $result->getName());
        self::assertSame('dan@example.com', $result->getEmailAddress());
    }
}
