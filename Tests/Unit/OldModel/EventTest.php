<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\OldModel;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\OldModel\AbstractModel;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class EventTest extends UnitTestCase
{
    /**
     * @var \Tx_Seminars_OldModel_Event
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new \Tx_Seminars_OldModel_Event();
    }

    /**
     * @test
     */
    public function isAbstractModel()
    {
        self::assertInstanceOf(AbstractModel::class, $this->subject);
    }

    /**
     * @test
     */
    public function fromDataCreatesInstanceOfSubclass()
    {
        $result = \Tx_Seminars_OldModel_Event::fromData([]);

        self::assertInstanceOf(\Tx_Seminars_OldModel_Event::class, $result);
    }

    /**
     * @test
     */
    public function getTopicByDefaultReturnsNull()
    {
        self::assertNull($this->subject->getTopic());
    }

    /**
     * @test
     */
    public function setTopicSetsTopic()
    {
        $topic = new \Tx_Seminars_OldModel_Event();

        $this->subject->setTopic($topic);

        self::assertSame($topic, $this->subject->getTopic());
    }

    /**
     * @test
     */
    public function getAttendancesMinByDefaultReturnsZero()
    {
        self::assertSame(0, $this->subject->getAttendancesMin());
    }

    /**
     * @test
     */
    public function getAttendancesMinReturnsAttendancesMin()
    {
        $number = 4;
        $subject = \Tx_Seminars_OldModel_Event::fromData(['attendees_min' => $number]);

        self::assertSame($number, $subject->getAttendancesMin());
    }

    /**
     * @test
     */
    public function getAttendancesMaxByDefaultReturnsZero()
    {
        self::assertSame(0, $this->subject->getAttendancesMax());
    }

    /**
     * @test
     */
    public function getAttendancesMaxReturnsAttendancesMax()
    {
        $number = 4;
        $subject = \Tx_Seminars_OldModel_Event::fromData(['attendees_max' => $number]);

        self::assertSame($number, $subject->getAttendancesMax());
    }

    /**
     * @test
     */
    public function getOfflineRegistrationsByDefaultReturnsZero()
    {
        self::assertSame(0, $this->subject->getOfflineRegistrations());
    }

    /**
     * @test
     */
    public function getOfflineRegistrationsReturnsOfflineRegistrations()
    {
        $number = 4;
        $subject = \Tx_Seminars_OldModel_Event::fromData(['offline_attendees' => $number]);

        self::assertSame($number, $subject->getOfflineRegistrations());
    }

    /**
     * @test
     */
    public function hasOfflineRegistrationsInitiallyReturnsFalse()
    {
        self::assertFalse($this->subject->hasOfflineRegistrations());
    }

    /**
     * @test
     */
    public function hasOfflineRegistrationsForOfflineRegistrationsReturnsTrue()
    {
        $subject = \Tx_Seminars_OldModel_Event::fromData(['offline_attendees' => 4]);

        self::assertTrue($subject->hasOfflineRegistrations());
    }

    /**
     * @test
     */
    public function hasCheckboxesForSingleEventWithNoCheckboxesReturnsFalse()
    {
        $subject = \Tx_Seminars_OldModel_Event::fromData(['checkboxes' => 0]);

        self::assertFalse($subject->hasCheckboxes());
    }

    /**
     * @test
     */
    public function hasCheckboxesForSingleEventWithOneCheckboxReturnsTrue()
    {
        $subject = \Tx_Seminars_OldModel_Event::fromData(['checkboxes' => 1]);

        self::assertTrue($subject->hasCheckboxes());
    }

    /**
     * @test
     */
    public function hasCheckboxesForDateWithOneCheckboxReturnsTrue()
    {
        $data = [
            'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
            'checkboxes' => 1,
        ];
        $subject = \Tx_Seminars_OldModel_Event::fromData($data);
        $topic = \Tx_Seminars_OldModel_Event::fromData(['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]);
        $subject->setTopic($topic);

        self::assertTrue($subject->hasCheckboxes());
    }
}
