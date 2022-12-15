<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Seminars\Model\Organizer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OliverKlee\Seminars\Model\Organizer
 */
final class OrganizerTest extends TestCase
{
    /**
     * @var Organizer
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new Organizer();
    }

    ///////////////////////////////
    // Tests regarding the name.
    ///////////////////////////////

    /**
     * @test
     */
    public function setNameWithEmptyNameThrowsException(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $name must not be empty.'
        );

        $this->subject->setName('');
    }

    /**
     * @test
     */
    public function setNameSetsName(): void
    {
        $this->subject->setName('Fabulous organizer');

        self::assertEquals(
            'Fabulous organizer',
            $this->subject->getName()
        );
    }

    /**
     * @test
     */
    public function getNameWithNonEmptyNameReturnsName(): void
    {
        $this->subject->setData(['title' => 'Fabulous organizer']);

        self::assertEquals(
            'Fabulous organizer',
            $this->subject->getName()
        );
    }

    /**
     * @test
     */
    public function getTitleWithNonEmptyNameReturnsName(): void
    {
        $this->subject->setData(['title' => 'Fabulous organizer']);

        self::assertEquals(
            'Fabulous organizer',
            $this->subject->getTitle()
        );
    }

    //////////////////////////////////
    // Tests regarding the homepage.
    //////////////////////////////////

    /**
     * @test
     */
    public function getHomepageInitiallyReturnsAnEmptyString(): void
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getHomepage()
        );
    }

    /**
     * @test
     */
    public function getHomepageWithNonEmptyHomepageReturnsHomepage(): void
    {
        $this->subject->setData(['homepage' => 'https://example.com']);

        self::assertEquals(
            'https://example.com',
            $this->subject->getHomepage()
        );
    }

    /**
     * @test
     */
    public function setHomepageSetsHomepage(): void
    {
        $this->subject->setHomepage('https://example.com');

        self::assertEquals(
            'https://example.com',
            $this->subject->getHomepage()
        );
    }

    /**
     * @test
     */
    public function hasHomepageInitiallyReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasHomepage()
        );
    }

    /**
     * @test
     */
    public function hasHomepageWithNonEmptyHomepageReturnsTrue(): void
    {
        $this->subject->setHomepage('https://example.com');

        self::assertTrue(
            $this->subject->hasHomepage()
        );
    }

    ////////////////////////////////////////
    // Tests regarding the e-mail address.
    ////////////////////////////////////////

    /**
     * @test
     */
    public function setEmailAddressWithEmptyEmailAddressThrowsException(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $eMailAddress must not be empty.'
        );

        $this->subject->setEmailAddress('');
    }

    /**
     * @test
     */
    public function setEmailAddressSetsEmailAddress(): void
    {
        $this->subject->setEmailAddress('mail@example.com');

        self::assertEquals(
            'mail@example.com',
            $this->subject->getEmailAddress()
        );
    }

    /**
     * @test
     */
    public function getEmailAddressWithNonEmptyEmailAddressReturnsEmailAddress(): void
    {
        $this->subject->setData(['email' => 'mail@example.com']);

        self::assertEquals(
            'mail@example.com',
            $this->subject->getEmailAddress()
        );
    }

    ///////////////////////////////////////
    // Tests regarding the e-mail footer.
    ///////////////////////////////////////

    /**
     * @test
     */
    public function getEmailFooterInitiallyReturnsAnEmptyString(): void
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getEmailFooter()
        );
    }

    /**
     * @test
     */
    public function getEmailFooterWithNonEmptyEmailFooterReturnsEmailFooter(): void
    {
        $this->subject->setData(['email_footer' => 'Example Inc.']);

        self::assertEquals(
            'Example Inc.',
            $this->subject->getEmailFooter()
        );
    }

    /**
     * @test
     */
    public function setEmailFooterSetsEmailFooter(): void
    {
        $this->subject->setEmailFooter('Example Inc.');

        self::assertEquals(
            'Example Inc.',
            $this->subject->getEmailFooter()
        );
    }

    /**
     * @test
     */
    public function hasEmailFooterInitiallyReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasEmailFooter()
        );
    }

    /**
     * @test
     */
    public function hasEmailFooterWithNonEmptyEmailFooterReturnsTrue(): void
    {
        $this->subject->setEmailFooter('Example Inc.');

        self::assertTrue(
            $this->subject->hasEmailFooter()
        );
    }

    /////////////////////////////////////////
    // Tests regarding the attendances PID.
    /////////////////////////////////////////

    /**
     * @test
     */
    public function getAttendancesPIDInitiallyReturnsZero(): void
    {
        $this->subject->setData([]);

        self::assertEquals(
            0,
            $this->subject->getAttendancesPID()
        );
    }

    /**
     * @test
     */
    public function getAttendancesPIDWithAttendancesPIDReturnsAttendancesPID(): void
    {
        $this->subject->setData(['attendances_pid' => 42]);

        self::assertEquals(
            42,
            $this->subject->getAttendancesPID()
        );
    }

    /**
     * @test
     */
    public function setAttendancesPIDWithPositiveAttendancesPIDSetsAttendancesPID(): void
    {
        $this->subject->setAttendancesPID(42);

        self::assertEquals(
            42,
            $this->subject->getAttendancesPID()
        );
    }

    /**
     * @test
     */
    public function setAttendancesPIDWithZeroAttendancesPIDSetsAttendancesPID(): void
    {
        $this->subject->setAttendancesPID(0);

        self::assertEquals(
            0,
            $this->subject->getAttendancesPID()
        );
    }

    /**
     * @test
     */
    public function setAttendancesPIDWithNegativeAttendancesPIDThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->setAttendancesPID(-1);
    }

    /**
     * @test
     */
    public function hasAttendancesPIDInitiallyReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasAttendancesPID()
        );
    }

    /**
     * @test
     */
    public function hasAttendancesPIDWithAttendancesPIDReturnsTrue(): void
    {
        $this->subject->setAttendancesPID(42);

        self::assertTrue(
            $this->subject->hasAttendancesPID()
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
        $this->subject->setData(['description' => '']);

        self::assertFalse(
            $this->subject->hasDescription()
        );
    }

    /**
     * @test
     */
    public function hasDescriptionForOrganizerWithDescriptionReturnsTrue(): void
    {
        $this->subject->setData(['description' => 'foo']);

        self::assertTrue(
            $this->subject->hasDescription()
        );
    }

    /**
     * @test
     */
    public function getDescriptionForOrganizerWithoutDescriptionReturnsEmptyString(): void
    {
        $this->subject->setData(['description' => '']);

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
        $this->subject->setData(['description' => 'foo']);

        self::assertEquals(
            'foo',
            $this->subject->getDescription()
        );
    }
}
