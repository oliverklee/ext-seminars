<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Model;

use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Model\Event;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * This test case holds all tests specific to event dates.
 *
 * @covers \OliverKlee\Seminars\Model\AbstractTimeSpan
 * @covers \OliverKlee\Seminars\Model\Event
 */
final class EventDateTest extends UnitTestCase
{
    private Event $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Event();
    }

    // Tests concerning "price on request"

    /**
     * @test
     */
    public function getPriceOnRequestReturnsPriceOnRequestFromDopic(): void
    {
        $topic = new Event();
        $topic->setData(['price_on_request' => true]);
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_EVENT_DATE,
                'topic' => $topic,
            ]
        );

        self::assertTrue($this->subject->getPriceOnRequest());
    }
}
