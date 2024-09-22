<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Repository\Registration;

use OliverKlee\Oelib\Domain\Repository\Interfaces\DirectPersist;
use OliverKlee\Seminars\Domain\Repository\AbstractRawDataCapableRepository;
use OliverKlee\Seminars\Domain\Repository\Registration\RegistrationRepository;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Domain\Repository\Registration\RegistrationRepository
 */
final class RegistrationRepositoryTest extends UnitTestCase
{
    private RegistrationRepository $subject;

    protected function setUp(): void
    {
        parent::setUp();

        if (\interface_exists(ObjectManagerInterface::class)) {
            $objectManagerStub = $this->createStub(ObjectManagerInterface::class);
            $this->subject = new RegistrationRepository($objectManagerStub);
        } else {
            $this->subject = new RegistrationRepository();
        }
    }

    /**
     * @test
     */
    public function isRepository(): void
    {
        self::assertInstanceOf(Repository::class, $this->subject);
    }

    /**
     * @test
     */
    public function isRawDataCapableRepository(): void
    {
        self::assertInstanceOf(AbstractRawDataCapableRepository::class, $this->subject);
    }

    /**
     * @test
     */
    public function implementsDirectPersist(): void
    {
        self::assertInstanceOf(DirectPersist::class, $this->subject);
    }
}
