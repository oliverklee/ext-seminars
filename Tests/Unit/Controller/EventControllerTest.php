<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Controller;

use OliverKlee\Seminars\Controller\EventController;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use OliverKlee\Seminars\Service\EventStatisticsCalculator;
use OliverKlee\Seminars\Service\RegistrationGuard;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Controller\EventController
 */
final class EventControllerTest extends UnitTestCase
{
    private EventController $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $eventRepositoryStub = $this->createStub(EventRepository::class);
        $eventStatisticsCalculatorStub = $this->createStub(EventStatisticsCalculator::class);
        $registrationGuardStub = $this->createStub(RegistrationGuard::class);

        $this->subject = new EventController(
            $eventRepositoryStub,
            $eventStatisticsCalculatorStub,
            $registrationGuardStub,
        );
    }

    /**
     * @test
     */
    public function isActionController(): void
    {
        self::assertInstanceOf(ActionController::class, $this->subject);
    }
}
