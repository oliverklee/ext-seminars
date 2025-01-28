<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Model;

use OliverKlee\Seminars\Domain\Model\Registration\Registration as ExtbaseRegistration;
use OliverKlee\Seminars\Model\Registration;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Model\Registration
 */
final class RegistrationTest extends UnitTestCase
{
    private Registration $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Registration();
    }

    /**
     * @test
     */
    public function isOnRegistrationQueueByDefaultReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse($this->subject->isOnRegistrationQueue());
    }

    /**
     * @test
     */
    public function isOnRegistrationQueueForRegularRegistrationReturnsFalse(): void
    {
        $this->subject->setData(['registration_queue' => ExtbaseRegistration::STATUS_REGULAR]);

        self::assertFalse($this->subject->isOnRegistrationQueue());
    }

    /**
     * @test
     */
    public function isOnRegistrationQueueForWaitingListRegistrationReturnsTrue(): void
    {
        $this->subject->setData(['registration_queue' => ExtbaseRegistration::STATUS_WAITING_LIST]);

        self::assertTrue($this->subject->isOnRegistrationQueue());
    }

    /**
     * @test
     */
    public function isOnRegistrationQueueForNonbindingReservationReturnsFalse(): void
    {
        $this->subject->setData(['registration_queue' => ExtbaseRegistration::STATUS_NONBINDING_RESERVATION]);

        self::assertFalse($this->subject->isOnRegistrationQueue());
    }
}
