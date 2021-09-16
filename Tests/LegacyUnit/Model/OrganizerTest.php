<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\PhpUnit\TestCase;

/**
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class OrganizerTest extends TestCase
{
    /**
     * @var \Tx_Seminars_Model_Organizer
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new \Tx_Seminars_Model_Organizer();
    }

    ///////////////////////////////
    // Tests regarding the name.
    ///////////////////////////////

    /**
     * @test
     */
    public function setNameWithEmptyNameThrowsException()
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
    public function setNameSetsName()
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
    public function getNameWithNonEmptyNameReturnsName()
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
    public function getTitleWithNonEmptyNameReturnsName()
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
    public function getHomepageInitiallyReturnsAnEmptyString()
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
    public function getHomepageWithNonEmptyHomepageReturnsHomepage()
    {
        $this->subject->setData(['homepage' => 'http://example.com']);

        self::assertEquals(
            'http://example.com',
            $this->subject->getHomepage()
        );
    }

    /**
     * @test
     */
    public function setHomepageSetsHomepage()
    {
        $this->subject->setHomepage('http://example.com');

        self::assertEquals(
            'http://example.com',
            $this->subject->getHomepage()
        );
    }

    /**
     * @test
     */
    public function hasHomepageInitiallyReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasHomepage()
        );
    }

    /**
     * @test
     */
    public function hasHomepageWithNonEmptyHomepageReturnsTrue()
    {
        $this->subject->setHomepage('http://example.com');

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
    public function setEMailAddressWithEmptyEMailAddressThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $eMailAddress must not be empty.'
        );

        $this->subject->setEMailAddress('');
    }

    /**
     * @test
     */
    public function setEMailAddressSetsEMailAddress()
    {
        $this->subject->setEMailAddress('mail@example.com');

        self::assertEquals(
            'mail@example.com',
            $this->subject->getEMailAddress()
        );
    }

    /**
     * @test
     */
    public function getEMailAddressWithNonEmptyEMailAddressReturnsEMailAddress()
    {
        $this->subject->setData(['email' => 'mail@example.com']);

        self::assertEquals(
            'mail@example.com',
            $this->subject->getEMailAddress()
        );
    }

    ///////////////////////////////////////
    // Tests regarding the e-mail footer.
    ///////////////////////////////////////

    /**
     * @test
     */
    public function getEMailFooterInitiallyReturnsAnEmptyString()
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
    public function getEMailFooterWithNonEmptyEMailFooterReturnsEMailFooter()
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
    public function setEMailFooterSetsEMailFooter()
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
    public function hasEMailFooterInitiallyReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasEmailFooter()
        );
    }

    /**
     * @test
     */
    public function hasEMailFooterWithNonEmptyEMailFooterReturnsTrue()
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
    public function getAttendancesPIDInitiallyReturnsZero()
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
    public function getAttendancesPIDWithAttendancesPIDReturnsAttendancesPID()
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
    public function setAttendancesPIDWithPositiveAttendancesPIDSetsAttendancesPID()
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
    public function setAttendancesPIDWithZeroAttendancesPIDSetsAttendancesPID()
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
    public function setAttendancesPIDWithNegativeAttendancesPIDThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->setAttendancesPID(-1);
    }

    /**
     * @test
     */
    public function hasAttendancesPIDInitiallyReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasAttendancesPID()
        );
    }

    /**
     * @test
     */
    public function hasAttendancesPIDWithAttendancesPIDReturnsTrue()
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
    public function hasDescriptionForOrganizerWithoutDescriptionReturnsFalse()
    {
        $this->subject->setData(['description' => '']);

        self::assertFalse(
            $this->subject->hasDescription()
        );
    }

    /**
     * @test
     */
    public function hasDescriptionForOrganizerWithDescriptionReturnsTrue()
    {
        $this->subject->setData(['description' => 'foo']);

        self::assertTrue(
            $this->subject->hasDescription()
        );
    }

    /**
     * @test
     */
    public function getDescriptionForOrganizerWithoutDescriptionReturnsEmptyString()
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
    public function getDescriptionForOrganizerWithDescriptionReturnsDescription()
    {
        $this->subject->setData(['description' => 'foo']);

        self::assertEquals(
            'foo',
            $this->subject->getDescription()
        );
    }
}
