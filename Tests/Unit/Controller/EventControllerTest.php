<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Controller;

use OliverKlee\Seminars\Controller\EventController;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
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

        $this->subject = new EventController($eventRepositoryStub);
    }

    /**
     * @test
     */
    public function isActionController(): void
    {
        self::assertInstanceOf(ActionController::class, $this->subject);
    }
}
