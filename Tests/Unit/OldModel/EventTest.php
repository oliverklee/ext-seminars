<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\OldModel;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Email\SystemEmailFromBuilder;
use OliverKlee\Seminars\OldModel\AbstractModel;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\OldModel\TestingEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Plugin\AbstractPlugin;

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
        $this->subject = TestingEvent::fromData(
            [
                'title' => 'A nice event',
                'begin_date' => mktime(10, 0, 0, 4, 8, 2020),
                'end_date' => mktime(18, 30, 0, 4, 20, 2020),
            ]
        );
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

    /**
     * @test
     */
    public function getEmailSenderReturnsSystemEmailMailRole()
    {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'system-foo@example.com';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = 'Mr. Default';
        $systemEmailFromBuilder = GeneralUtility::makeInstance(SystemEmailFromBuilder::class);

        self::assertEquals(
            $systemEmailFromBuilder->build(),
            $this->subject->getEmailSender()
        );
    }

    /**
     * @test
     */
    public function getEmailSenderReturnsFirstOrganizerMailRole()
    {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = '';

        $organizer = \Tx_Seminars_OldModel_Organizer::fromData(
            [
                'title' => 'Brain Gourmets',
                'email' => 'organizer@example.com',
                'email_footer' => 'Best workshops in town!',
            ]
        );

        $organizerBagMock = $this->createMock(\Tx_Seminars_Bag_Organizer::class);
        $organizerBagMock->method('current')->willReturn($organizer);

        GeneralUtility::addInstance(\Tx_Seminars_Bag_Organizer::class, $organizerBagMock);
        $this->subject->setEventData(['uid' => 1, 'organizers' => 1]);

        self::assertSame(
            $organizer,
            $this->subject->getEmailSender()
        );
    }

    /**
     * @test
     */
    public function hasAttachedFilesInitiallyReturnsFalse()
    {
        self::assertFalse($this->subject->hasAttachedFiles());
    }

    /**
     * @test
     */
    public function hasAttachedFilesWithOneAttachedFileReturnsTrue()
    {
        $this->subject->setAttachedFiles('test.file');

        self::assertTrue($this->subject->hasAttachedFiles());
    }

    /**
     * @test
     */
    public function hasAttachedFilesWithTwoAttachedFilesReturnsTrue()
    {
        $this->subject->setAttachedFiles('test.file,test_02.file');

        self::assertTrue($this->subject->hasAttachedFiles());
    }

    /**
     * @test
     */
    public function hasAttachedFilesForDateWithoutFilesAndTopicWithOneFileReturnsTrue()
    {
        $topic = \Tx_Seminars_OldModel_Event::fromData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'attached_files' => 'test.file',
            ]
        );
        $date = \Tx_Seminars_OldModel_Event::fromData(['object_type' => \Tx_Seminars_Model_Event::TYPE_DATE]);
        $date->setTopic($topic);

        self::assertTrue($date->hasAttachedFiles());
    }

    /**
     * @test
     */
    public function hasAttachedFilesForDateAndTopicWithoutFilesReturnsFalse()
    {
        $topic = \Tx_Seminars_OldModel_Event::fromData(['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]);
        $date = \Tx_Seminars_OldModel_Event::fromData(['object_type' => \Tx_Seminars_Model_Event::TYPE_DATE]);
        $date->setTopic($topic);

        self::assertFalse($date->hasAttachedFiles());
    }

    /**
     * @test
     */
    public function getAttachedFilesForNoAttachedFilesReturnsAnEmptyArray()
    {
        $plugin = new AbstractPlugin();

        self::assertSame([], $this->subject->getAttachedFiles($plugin));
    }
}
