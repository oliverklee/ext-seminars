<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\OldModel;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;

/**
 * @covers \Tx_Seminars_OldModel_Organizer
 */
final class OrganizerTest extends TestCase
{
    /**
     * @var \Tx_Seminars_OldModel_Organizer
     */
    private $subject;

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * a maximal filled organizer
     *
     * @var \Tx_Seminars_OldModel_Organizer
     */
    private $maximalFixture;

    protected function setUp()
    {
        $this->testingFramework = new TestingFramework('tx_seminars');
        $subjectUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            [
                'title' => 'Test organizer',
                'email' => 'foo@test.com',
            ]
        );
        $this->subject = new \Tx_Seminars_OldModel_Organizer($subjectUid);

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
        $this->maximalFixture = new \Tx_Seminars_OldModel_Organizer($maximalFixtureUid);
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    //////////////////////////////////////////
    // Tests for creating organizer objects.
    //////////////////////////////////////////

    /**
     * @test
     */
    public function createFromUid()
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
    public function getNameWithNameReturnsName()
    {
        self::assertEquals(
            'Test organizer',
            $this->subject->getName()
        );
    }

    /**
     * @test
     */
    public function hasHomepageWithEmptyHomepageReturnsFalse()
    {
        self::assertFalse(
            $this->subject->hasHomepage()
        );
    }

    /**
     * @test
     */
    public function hasHomepageWithHomepageReturnsTrue()
    {
        self::assertTrue(
            $this->maximalFixture->hasHomepage()
        );
    }

    /**
     * @test
     */
    public function getHomepage()
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
    public function getEmailFooterForEmptyFooterReturnsEmptyString()
    {
        self::assertEquals(
            '',
            $this->subject->getEmailFooter()
        );
    }

    /**
     * @test
     */
    public function getEmailFooterForNonEmptyFooterReturnsThisFooter()
    {
        self::assertEquals(
            "line 1\nline 2",
            $this->maximalFixture->getEmailFooter()
        );
    }

    /**
     * @test
     */
    public function getEmailAddressWithEmailAddressReturnsEmailAddress()
    {
        self::assertEquals(
            'foo@test.com',
            $this->subject->getEmailAddress()
        );
    }

    /**
     * @test
     */
    public function getAttendancesPidWithNoAttendancesPidReturnsZero()
    {
        self::assertEquals(
            0,
            $this->subject->getAttendancesPid()
        );
    }

    /**
     * @test
     */
    public function getAttendancesPidWithAttendancesPidReturnsAttendancesPid()
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
    public function hasDescriptionForOrganizerWithoutDescriptionReturnsFalse()
    {
        self::assertFalse(
            $this->subject->hasDescription()
        );
    }

    /**
     * @test
     */
    public function hasDescriptionForOrganizerWithDescriptionReturnsTrue()
    {
        self::assertTrue(
            $this->maximalFixture->hasDescription()
        );
    }

    /**
     * @test
     */
    public function getDescriptionForOrganizerWithoutDescriptionReturnsEmptyString()
    {
        self::assertEquals(
            '',
            $this->subject->getDescription()
        );
    }

    /**
     * @test
     */
    public function getDescriptionForOrganizerWithDescriptionReturnsDescription()
    {
        self::assertEquals(
            'foo',
            $this->maximalFixture->getDescription()
        );
    }
}
