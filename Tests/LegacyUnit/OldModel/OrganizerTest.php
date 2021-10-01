<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\OldModel;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\OldModel\LegacyOrganizer;

/**
 * @covers \OliverKlee\Seminars\OldModel\LegacyOrganizer
 */
final class OrganizerTest extends TestCase
{
    /**
     * @var LegacyOrganizer
     */
    private $subject;

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * a maximal filled organizer
     *
     * @var LegacyOrganizer
     */
    private $maximalFixture;

    protected function setUp(): void
    {
        $this->testingFramework = new TestingFramework('tx_seminars');
        $subjectUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            [
                'title' => 'Test organizer',
                'email' => 'foo@test.com',
            ]
        );
        $this->subject = new LegacyOrganizer($subjectUid);

        $maximalFixtureUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            [
                'title' => 'Test organizer',
                'homepage' => 'http://www.test.com/',
                'email' => 'maximal-foo@test.com',
                'email_footer' => "line 1\nline 2",
                'attendances_pid' => 99,
                'description' => 'foo',
            ]
        );
        $this->maximalFixture = new LegacyOrganizer($maximalFixtureUid);
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();
    }

    //////////////////////////////////////////
    // Tests for creating organizer objects.
    //////////////////////////////////////////

    /**
     * @test
     */
    public function createFromUid(): void
    {
        self::assertTrue(
            $this->subject->isOk()
        );
    }

    ////////////////////////////////////////////////
    // Tests for getting the organizer attributes.
    ////////////////////////////////////////////////

    /**
     * @test
     */
    public function getNameWithNameReturnsName(): void
    {
        self::assertEquals(
            'Test organizer',
            $this->subject->getName()
        );
    }

    /**
     * @test
     */
    public function hasHomepageWithEmptyHomepageReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->hasHomepage()
        );
    }

    /**
     * @test
     */
    public function hasHomepageWithHomepageReturnsTrue(): void
    {
        self::assertTrue(
            $this->maximalFixture->hasHomepage()
        );
    }

    /**
     * @test
     */
    public function getHomepage(): void
    {
        self::assertEquals(
            '',
            $this->subject->getHomepage()
        );
        self::assertEquals(
            'http://www.test.com/',
            $this->maximalFixture->getHomepage()
        );
    }

    /**
     * @test
     */
    public function getEmailFooterForEmptyFooterReturnsEmptyString(): void
    {
        self::assertEquals(
            '',
            $this->subject->getEmailFooter()
        );
    }

    /**
     * @test
     */
    public function getEmailFooterForNonEmptyFooterReturnsThisFooter(): void
    {
        self::assertEquals(
            "line 1\nline 2",
            $this->maximalFixture->getEmailFooter()
        );
    }

    /**
     * @test
     */
    public function getEmailAddressWithEmailAddressReturnsEmailAddress(): void
    {
        self::assertEquals(
            'foo@test.com',
            $this->subject->getEmailAddress()
        );
    }

    /**
     * @test
     */
    public function getAttendancesPidWithNoAttendancesPidReturnsZero(): void
    {
        self::assertEquals(
            0,
            $this->subject->getAttendancesPid()
        );
    }

    /**
     * @test
     */
    public function getAttendancesPidWithAttendancesPidReturnsAttendancesPid(): void
    {
        self::assertEquals(
            99,
            $this->maximalFixture->getAttendancesPid()
        );
    }

    /////////////////////////////////////
    // Tests concerning the description
    /////////////////////////////////////

    /**
     * @test
     */
    public function hasDescriptionForOrganizerWithoutDescriptionReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->hasDescription()
        );
    }

    /**
     * @test
     */
    public function hasDescriptionForOrganizerWithDescriptionReturnsTrue(): void
    {
        self::assertTrue(
            $this->maximalFixture->hasDescription()
        );
    }

    /**
     * @test
     */
    public function getDescriptionForOrganizerWithoutDescriptionReturnsEmptyString(): void
    {
        self::assertEquals(
            '',
            $this->subject->getDescription()
        );
    }

    /**
     * @test
     */
    public function getDescriptionForOrganizerWithDescriptionReturnsDescription(): void
    {
        self::assertEquals(
            'foo',
            $this->maximalFixture->getDescription()
        );
    }
}
