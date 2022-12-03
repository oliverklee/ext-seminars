<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Controller\BackEnd;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Controller\BackEnd\EventController;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * @covers \OliverKlee\Seminars\Controller\BackEnd\EventController
 */
final class EventControllerTest extends UnitTestCase
{
    /**
     * @var EventController
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new EventController();
    }

    /**
     * @test
     */
    public function isActionController(): void
    {
        self::assertInstanceOf(ActionController::class, $this->subject);
    }
}
