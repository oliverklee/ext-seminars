<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\OldModel;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Email\SystemEmailFromBuilder;
use OliverKlee\Seminars\Bag\OrganizerBag;
use OliverKlee\Seminars\Model\Event;
use OliverKlee\Seminars\OldModel\AbstractModel;
use OliverKlee\Seminars\OldModel\LegacyEvent;
use OliverKlee\Seminars\OldModel\LegacyOrganizer;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\OldModel\TestingLegacyEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Plugin\AbstractPlugin;

/**
 * @covers \OliverKlee\Seminars\OldModel\LegacyEvent
 */
final class LegacyEventTest extends UnitTestCase
{
    /**
     * @var TestingLegacyEvent
     */
    private $subject = null;

    protected function setUp(): void
    {
        $this->subject = TestingLegacyEvent::fromData(
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
        $result = LegacyEvent::fromData([]);

        self::assertInstanceOf(LegacyEvent::class, $result);
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
        $topic = new LegacyEvent();

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
        $subject = LegacyEvent::fromData(['attendees_min' => $number]);

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
        $subject = LegacyEvent::fromData(['attendees_max' => $number]);

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
        $subject = LegacyEvent::fromData(['offline_attendees' => $number]);

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
        $subject = LegacyEvent::fromData(['offline_attendees' => 4]);

        self::assertTrue($subject->hasOfflineRegistrations());
    }

    /**
     * @test
     */
    public function hasCheckboxesForSingleEventWithNoCheckboxesReturnsFalse(): void
    {
        $subject = LegacyEvent::fromData(['checkboxes' => 0]);

        self::assertFalse($subject->hasCheckboxes());
    }

    /**
     * @test
     */
    public function hasCheckboxesForSingleEventWithOneCheckboxReturnsTrue(): void
    {
        $subject = LegacyEvent::fromData(['checkboxes' => 1]);

        self::assertTrue($subject->hasCheckboxes());
    }

    /**
     * @test
     */
    public function hasCheckboxesForDateWithOneCheckboxReturnsTrue(): void
    {
        $data = [
            'object_type' => Event::TYPE_DATE,
            'checkboxes' => 1,
        ];
        $subject = LegacyEvent::fromData($data);
        $topic = LegacyEvent::fromData(['object_type' => Event::TYPE_TOPIC]);
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

        $organizer = LegacyOrganizer::fromData(
            [
                'title' => 'Brain Gourmets',
                'email' => 'organizer@example.com',
                'email_footer' => 'Best workshops in town!',
            ]
        );

        $organizerBagMock = $this->createMock(OrganizerBag::class);
        $organizerBagMock->method('current')->willReturn($organizer);

        GeneralUtility::addInstance(OrganizerBag::class, $organizerBagMock);
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
        $topic = LegacyEvent::fromData(
            [
                'object_type' => Event::TYPE_TOPIC,
                'attached_files' => 'test.file',
            ]
        );
        $date = LegacyEvent::fromData(['object_type' => Event::TYPE_DATE]);
        $date->setTopic($topic);

        self::assertTrue($date->hasAttachedFiles());
    }

    /**
     * @test
     */
    public function hasAttachedFilesForDateAndTopicWithoutFilesReturnsFalse(): void
    {
        $topic = LegacyEvent::fromData(['object_type' => Event::TYPE_TOPIC]);
        $date = LegacyEvent::fromData(['object_type' => Event::TYPE_DATE]);
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
