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
 * @covers \Tx_Seminars_OldModel_Event
 */
final class EventTest extends UnitTestCase
{
    /**
     * @var TestingEvent
     */
    private $subject = null;

    protected function setUp(): void
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
    public function isAbstractModel(): void
    {
        self::assertInstanceOf(AbstractModel::class, $this->subject);
    }

    /**
     * @test
     */
    public function fromDataCreatesInstanceOfSubclass(): void
    {
        $result = \Tx_Seminars_OldModel_Event::fromData([]);

        self::assertInstanceOf(\Tx_Seminars_OldModel_Event::class, $result);
    }

    /**
     * @test
     */
    public function getTopicByDefaultReturnsNull(): void
    {
        self::assertNull($this->subject->getTopic());
    }

    /**
     * @test
     */
    public function setTopicSetsTopic(): void
    {
        $topic = new \Tx_Seminars_OldModel_Event();

        $this->subject->setTopic($topic);

        self::assertSame($topic, $this->subject->getTopic());
    }

    /**
     * @test
     */
    public function getAttendancesMinByDefaultReturnsZero(): void
    {
        self::assertSame(0, $this->subject->getAttendancesMin());
    }

    /**
     * @test
     */
    public function getAttendancesMinReturnsAttendancesMin(): void
    {
        $number = 4;
        $subject = \Tx_Seminars_OldModel_Event::fromData(['attendees_min' => $number]);

        self::assertSame($number, $subject->getAttendancesMin());
    }

    /**
     * @test
     */
    public function getAttendancesMaxByDefaultReturnsZero(): void
    {
        self::assertSame(0, $this->subject->getAttendancesMax());
    }

    /**
     * @test
     */
    public function getAttendancesMaxReturnsAttendancesMax(): void
    {
        $number = 4;
        $subject = \Tx_Seminars_OldModel_Event::fromData(['attendees_max' => $number]);

        self::assertSame($number, $subject->getAttendancesMax());
    }

    /**
     * @test
     */
    public function getOfflineRegistrationsByDefaultReturnsZero(): void
    {
        self::assertSame(0, $this->subject->getOfflineRegistrations());
    }

    /**
     * @test
     */
    public function getOfflineRegistrationsReturnsOfflineRegistrations(): void
    {
        $number = 4;
        $subject = \Tx_Seminars_OldModel_Event::fromData(['offline_attendees' => $number]);

        self::assertSame($number, $subject->getOfflineRegistrations());
    }

    /**
     * @test
     */
    public function hasOfflineRegistrationsInitiallyReturnsFalse(): void
    {
        self::assertFalse($this->subject->hasOfflineRegistrations());
    }

    /**
     * @test
     */
    public function hasOfflineRegistrationsForOfflineRegistrationsReturnsTrue(): void
    {
        $subject = \Tx_Seminars_OldModel_Event::fromData(['offline_attendees' => 4]);

        self::assertTrue($subject->hasOfflineRegistrations());
    }

    /**
     * @test
     */
    public function hasCheckboxesForSingleEventWithNoCheckboxesReturnsFalse(): void
    {
        $subject = \Tx_Seminars_OldModel_Event::fromData(['checkboxes' => 0]);

        self::assertFalse($subject->hasCheckboxes());
    }

    /**
     * @test
     */
    public function hasCheckboxesForSingleEventWithOneCheckboxReturnsTrue(): void
    {
        $subject = \Tx_Seminars_OldModel_Event::fromData(['checkboxes' => 1]);

        self::assertTrue($subject->hasCheckboxes());
    }

    /**
     * @test
     */
    public function hasCheckboxesForDateWithOneCheckboxReturnsTrue(): void
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
    public function getEmailSenderReturnsSystemEmailMailRole(): void
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
    public function getEmailSenderReturnsFirstOrganizerMailRole(): void
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
    public function hasAttachedFilesInitiallyReturnsFalse(): void
    {
        self::assertFalse($this->subject->hasAttachedFiles());
    }

    /**
     * @test
     */
    public function hasAttachedFilesWithOneAttachedFileReturnsTrue(): void
    {
        $this->subject->setAttachedFiles('test.file');

        self::assertTrue($this->subject->hasAttachedFiles());
    }

    /**
     * @test
     */
    public function hasAttachedFilesWithTwoAttachedFilesReturnsTrue(): void
    {
        $this->subject->setAttachedFiles('test.file,test_02.file');

        self::assertTrue($this->subject->hasAttachedFiles());
    }

    /**
     * @test
     */
    public function hasAttachedFilesForDateWithoutFilesAndTopicWithOneFileReturnsTrue(): void
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
    public function hasAttachedFilesForDateAndTopicWithoutFilesReturnsFalse(): void
    {
        $topic = \Tx_Seminars_OldModel_Event::fromData(['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]);
        $date = \Tx_Seminars_OldModel_Event::fromData(['object_type' => \Tx_Seminars_Model_Event::TYPE_DATE]);
        $date->setTopic($topic);

        self::assertFalse($date->hasAttachedFiles());
    }

    /**
     * @test
     */
    public function getAttachedFilesForNoAttachedFilesReturnsAnEmptyArray(): void
    {
        $plugin = new AbstractPlugin();

        self::assertSame([], $this->subject->getAttachedFiles($plugin));
    }
}
