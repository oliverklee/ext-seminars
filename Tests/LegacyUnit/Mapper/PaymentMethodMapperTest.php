<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Mapper;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class PaymentMethodMapperTest extends TestCase
{
    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var \Tx_Seminars_Mapper_PaymentMethod
     */
    private $subject = null;

    protected function setUp()
    {
        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = new \Tx_Seminars_Mapper_PaymentMethod();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    //////////////////////////
    // Tests concerning find
    //////////////////////////

    /**
     * @test
     */
    public function findWithUidReturnsPaymentMethodInstance()
    {
        self::assertInstanceOf(\Tx_Seminars_Model_PaymentMethod::class, $this->subject->find(1));
    }

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsRecordAsModel()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_payment_methods',
            ['title' => 'Cash']
        );

        /** @var \Tx_Seminars_Model_PaymentMethod $model */
        $model = $this->subject->find($uid);
        self::assertEquals(
            'Cash',
            $model->getTitle()
        );
    }
}
