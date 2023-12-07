<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Model;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Model\Event;
use OliverKlee\Seminars\Model\PaymentMethod;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * This test case holds all tests specific to event dates.
 *
 * @covers \OliverKlee\Seminars\Model\AbstractTimeSpan
 * @covers \OliverKlee\Seminars\Model\Event
 */
final class EventDateTest extends UnitTestCase
{
    /**
     * @var Event
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Event();
    }

    // Tests concerning the payment methods

    /**
     * @test
     */
    public function getPaymentMethodsReturnsPaymentMethodsFromTopic(): void
    {
        /** @var Collection<PaymentMethod> $paymentMethods */
        $paymentMethods = new Collection();
        $topic = new Event();
        $topic->setData(['payment_methods' => $paymentMethods]);
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_EVENT_DATE,
                'topic' => $topic,
            ]
        );

        self::assertSame(
            $paymentMethods,
            $this->subject->getPaymentMethods()
        );
    }

    /**
     * @test
     */
    public function setPaymentMethodsThrowsException(): void
    {
        $this->expectException(
            \BadMethodCallException::class
        );
        $this->expectExceptionMessage(
            'setPaymentMethods may only be called on single events and event ' .
            'topics, but not on event dates.'
        );

        $topic = new Event();
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_EVENT_DATE,
                'topic' => $topic,
            ]
        );

        /** @var Collection<PaymentMethod> $paymentMethods */
        $paymentMethods = new Collection();
        $this->subject->setPaymentMethods($paymentMethods);
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
