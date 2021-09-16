<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Mapper;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;

/**
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class OrganizerMapperTest extends TestCase
{
    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var \Tx_Seminars_Mapper_Organizer
     */
    private $subject = null;

    protected function setUp()
    {
        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = new \Tx_Seminars_Mapper_Organizer();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    // Tests concerning find

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

        $model = $this->subject->find($uid);
        self::assertEquals(
            'Fabulous organizer',
            $model->getName()
        );
    }
}
