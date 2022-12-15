<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Mapper;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Mapper\PaymentMethodMapper;
use OliverKlee\Seminars\Model\PaymentMethod;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OliverKlee\Seminars\Mapper\PaymentMethodMapper
 */
final class PaymentMethodMapperTest extends TestCase
{
    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * @var PaymentMethodMapper
     */
    private $subject;

    protected function setUp(): void
    {
        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = new PaymentMethodMapper();
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();
    }

    // Tests concerning find

    /**
     * @test
     */
    public function findWithUidReturnsPaymentMethodInstance(): void
    {
        self::assertInstanceOf(PaymentMethod::class, $this->subject->find(1));
    }

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsRecordAsModel(): void
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_payment_methods',
            ['title' => 'Cash']
        );

        $model = $this->subject->find($uid);
        self::assertEquals(
            'Cash',
            $model->getTitle()
        );
    }
}
