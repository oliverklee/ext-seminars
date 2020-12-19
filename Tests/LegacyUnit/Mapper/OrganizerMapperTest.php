<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Mapper;

use OliverKlee\PhpUnit\TestCase;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class OrganizerMapperTest extends TestCase
{
    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var \Tx_Seminars_Mapper_Organizer
     */
    private $subject = null;

    protected function setUp()
    {
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');

        $this->subject = new \Tx_Seminars_Mapper_Organizer();
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
    public function findWithUidReturnsOrganizerInstance()
    {
        self::assertInstanceOf(\Tx_Seminars_Model_Organizer::class, $this->subject->find(1));
    }

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsRecordAsModel()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            ['title' => 'Fabulous organizer']
        );

        /** @var \Tx_Seminars_Model_Organizer $model */
        $model = $this->subject->find($uid);
        self::assertEquals(
            'Fabulous organizer',
            $model->getName()
        );
    }
}
