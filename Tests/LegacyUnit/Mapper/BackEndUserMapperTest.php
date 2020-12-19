<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Mapper;

use OliverKlee\PhpUnit\TestCase;

/**
 * Test case.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class BackEndUserMapperTest extends TestCase
{
    /**
     * @var \Tx_Oelib_TestingFramework for creating dummy records
     */
    private $testingFramework = null;

    /**
     * @var \Tx_Seminars_Mapper_BackEndUser the object to test
     */
    private $subject = null;

    protected function setUp()
    {
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');

        $this->subject = new \Tx_Seminars_Mapper_BackEndUser();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    //////////////////////////
    // Tests concerning find
    //////////////////////////

    public function testFindWithUidOfExistingRecordReturnsBackEndUserInstance()
    {
        self::assertInstanceOf(
            \Tx_Seminars_Model_BackEndUser::class,
            $this->subject->find($this->testingFramework->createBackEndUser())
        );
    }
}
