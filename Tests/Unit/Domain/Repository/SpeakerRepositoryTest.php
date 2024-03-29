<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Repository;

use OliverKlee\Seminars\Domain\Repository\SpeakerRepository;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Domain\Repository\SpeakerRepository
 */
final class SpeakerRepositoryTest extends UnitTestCase
{
    /**
     * @var SpeakerRepository
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        if (\interface_exists(ObjectManagerInterface::class)) {
            $objectManagerStub = $this->createStub(ObjectManagerInterface::class);
            $this->subject = new SpeakerRepository($objectManagerStub);
        } else {
            $this->subject = new SpeakerRepository();
        }
    }

    /**
     * @test
     */
    public function isRepository(): void
    {
        self::assertInstanceOf(Repository::class, $this->subject);
    }
}
