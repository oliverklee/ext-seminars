<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\PhpUnit\TestCase;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class SpeakerTest extends TestCase
{
    /**
     * @var \Tx_Seminars_Model_Speaker
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new \Tx_Seminars_Model_Speaker();
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
        $this->subject->setName('John Doe');

        self::assertEquals(
            'John Doe',
            $this->subject->getName()
        );
    }

    /**
     * @test
     */
    public function getNameWithNonEmptyNameReturnsName()
    {
        $this->subject->setData(['title' => 'John Doe']);

        self::assertEquals(
            'John Doe',
            $this->subject->getName()
        );
    }

    //////////////////////////////////////
    // Tests regarding the organization.
    //////////////////////////////////////

    /**
     * @test
     */
    public function getOrganizationWithoutOrganizationReturnsAnEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getOrganization()
        );
    }

    /**
     * @test
     */
    public function getOrganizationWithNonEmptyOrganizationReturnsOrganization()
    {
        $this->subject->setData(['organization' => 'Happy organization']);

        self::assertEquals(
            'Happy organization',
            $this->subject->getOrganization()
        );
    }

    /**
     * @test
     */
    public function setOrganizationSetsOrganization()
    {
        $this->subject->setOrganization('Happy organization');

        self::assertEquals(
            'Happy organization',
            $this->subject->getOrganization()
        );
    }

    /**
     * @test
     */
    public function hasOrganizationWithoutOrganizationReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasOrganization()
        );
    }

    /**
     * @test
     */
    public function hasOrganizationWithNonEmptyOrganizationReturnsTrue()
    {
        $this->subject->setOrganization('Happy organization');

        self::assertTrue(
            $this->subject->hasOrganization()
        );
    }

    //////////////////////////////////
    // Tests regarding the homepage.
    //////////////////////////////////

    /**
     * @test
     */
    public function getHomepageWithoutHomepageReturnsAnEmptyString()
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
    public function hasHomepageWithoutHomepageReturnsFalse()
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

    /////////////////////////////////////
    // Tests regarding the description.
    /////////////////////////////////////

    /**
     * @test
     */
    public function getDescriptionWithoutDescriptionReturnsAnEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getDescription()
        );
    }

    /**
     * @test
     */
    public function getDescriptionWithDescriptionReturnsDescription()
    {
        $this->subject->setData(['description' => 'This is a good speaker.']);

        self::assertEquals(
            'This is a good speaker.',
            $this->subject->getDescription()
        );
    }

    /**
     * @test
     */
    public function setDescriptionSetsDescription()
    {
        $this->subject->setDescription('This is a good speaker.');

        self::assertEquals(
            'This is a good speaker.',
            $this->subject->getDescription()
        );
    }

    /**
     * @test
     */
    public function hasDescriptionWithoutDescriptionReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasDescription()
        );
    }

    /**
     * @test
     */
    public function hasDescriptionWithDescriptionReturnsTrue()
    {
        $this->subject->setDescription('This is a good speaker.');

        self::assertTrue(
            $this->subject->hasDescription()
        );
    }

    //////////////////////////////////
    // Tests regarding the address.
    //////////////////////////////////

    /**
     * @test
     */
    public function getAddressWithoutAddressReturnsAnEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getAddress()
        );
    }

    /**
     * @test
     */
    public function getAddressWithNonEmptyAddressReturnsAddress()
    {
        $this->subject->setData(['address' => 'Backstreet 42']);

        self::assertEquals(
            'Backstreet 42',
            $this->subject->getAddress()
        );
    }

    /**
     * @test
     */
    public function setAddressSetsAddress()
    {
        $this->subject->setAddress('Backstreet 42');

        self::assertEquals(
            'Backstreet 42',
            $this->subject->getAddress()
        );
    }

    /**
     * @test
     */
    public function hasAddressWithoutAddressReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasAddress()
        );
    }

    /**
     * @test
     */
    public function hasAddressWithNonEmptyAddressReturnsTrue()
    {
        $this->subject->setAddress('Backstreet 42');

        self::assertTrue(
            $this->subject->hasAddress()
        );
    }

    ///////////////////////////////////////////////
    // Tests regarding the work telephone number.
    ///////////////////////////////////////////////

    /**
     * @test
     */
    public function getPhoneWorkWithoutPhoneWorkReturnsAnEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getPhoneWork()
        );
    }

    /**
     * @test
     */
    public function getPhoneWorkWithPhoneWorkReturnsPhoneWork()
    {
        $this->subject->setData(['phone_work' => '12345']);

        self::assertEquals(
            '12345',
            $this->subject->getPhoneWork()
        );
    }

    /**
     * @test
     */
    public function setPhoneWorkSetsPhoneWork()
    {
        $this->subject->setPhoneWork('12345');

        self::assertEquals(
            '12345',
            $this->subject->getPhoneWork()
        );
    }

    /**
     * @test
     */
    public function hasPhoneWorkWithoutPhoneWorkReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasPhoneWork()
        );
    }

    /**
     * @test
     */
    public function hasPhoneWorkWithPhoneWorkReturnsTrue()
    {
        $this->subject->setPhoneWork('12345');

        self::assertTrue(
            $this->subject->hasPhoneWork()
        );
    }

    ///////////////////////////////////////////////
    // Tests regarding the home telephone number.
    ///////////////////////////////////////////////

    /**
     * @test
     */
    public function getPhoneHomeWithoutPhoneHomeReturnsAnEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getPhoneHome()
        );
    }

    /**
     * @test
     */
    public function getPhoneHomeWithPhoneHomeReturnsPhoneHome()
    {
        $this->subject->setData(['phone_home' => '12345']);

        self::assertEquals(
            '12345',
            $this->subject->getPhoneHome()
        );
    }

    /**
     * @test
     */
    public function setPhoneHomeSetsPhoneHome()
    {
        $this->subject->setPhoneHome('12345');

        self::assertEquals(
            '12345',
            $this->subject->getPhoneHome()
        );
    }

    /**
     * @test
     */
    public function hasPhoneHomeWithoutPhoneHomeReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasPhoneHome()
        );
    }

    /**
     * @test
     */
    public function hasPhoneHomeWithPhoneHomeReturnsTrue()
    {
        $this->subject->setPhoneHome('12345');

        self::assertTrue(
            $this->subject->hasPhoneHome()
        );
    }

    /////////////////////////////////////////////////
    // Tests regarding the mobile telephone number.
    /////////////////////////////////////////////////

    /**
     * @test
     */
    public function getPhoneMobileWithoutPhoneMobileReturnsAnEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getPhoneMobile()
        );
    }

    /**
     * @test
     */
    public function getPhoneMobileWithPhoneMobileReturnsPhoneMobile()
    {
        $this->subject->setData(['phone_mobile' => '12345']);

        self::assertEquals(
            '12345',
            $this->subject->getPhoneMobile()
        );
    }

    /**
     * @test
     */
    public function setPhoneMobileSetsPhoneMobile()
    {
        $this->subject->setPhoneMobile('12345');

        self::assertEquals(
            '12345',
            $this->subject->getPhoneMobile()
        );
    }

    /**
     * @test
     */
    public function hasPhoneMobileWithoutPhoneMobileReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasPhoneMobile()
        );
    }

    /**
     * @test
     */
    public function hasPhoneMobileWithPhoneMobileReturnsTrue()
    {
        $this->subject->setPhoneMobile('12345');

        self::assertTrue(
            $this->subject->hasPhoneMobile()
        );
    }

    ////////////////////////////////////
    // Tests regarding the fax number.
    ////////////////////////////////////

    /**
     * @test
     */
    public function getFaxWithoutFaxReturnsAnEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getFax()
        );
    }

    /**
     * @test
     */
    public function getFaxWithFaxReturnsFax()
    {
        $this->subject->setData(['fax' => '12345']);

        self::assertEquals(
            '12345',
            $this->subject->getFax()
        );
    }

    /**
     * @test
     */
    public function setFaxSetsFax()
    {
        $this->subject->setFax('12345');

        self::assertEquals(
            '12345',
            $this->subject->getFax()
        );
    }

    /**
     * @test
     */
    public function hasFaxWithoutFaxReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasFax()
        );
    }

    /**
     * @test
     */
    public function hasFaxWithFaxReturnsTrue()
    {
        $this->subject->setFax('12345');

        self::assertTrue(
            $this->subject->hasFax()
        );
    }

    ////////////////////////////////////////
    // Tests regarding the e-mail address.
    ////////////////////////////////////////

    /**
     * @test
     */
    public function getEMailAddressWithoutEMailAddressReturnsAnEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
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
    public function hasEMailAddressWithoutEMailAddressReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasEMailAddress()
        );
    }

    /**
     * @test
     */
    public function hasEMailAddressWithEMailAddressReturnsTrue()
    {
        $this->subject->setEMailAddress('mail@example.com');

        self::assertTrue(
            $this->subject->hasEMailAddress()
        );
    }

    ////////////////////////////////
    // Tests regarding the gender.
    ////////////////////////////////

    /**
     * @test
     */
    public function getGenderWithoutGenderReturnsUnknownGender()
    {
        $this->subject->setData([]);

        self::assertEquals(
            \Tx_Seminars_Model_Speaker::GENDER_UNKNOWN,
            $this->subject->getGender()
        );
    }

    /**
     * @test
     */
    public function getGenderWithGenderMaleReturnsMaleGender()
    {
        $this->subject->setData(
            ['gender' => \Tx_Seminars_Model_Speaker::GENDER_MALE]
        );

        self::assertEquals(
            \Tx_Seminars_Model_Speaker::GENDER_MALE,
            $this->subject->getGender()
        );
    }

    /**
     * @test
     */
    public function getGenderWithGenderFemaleReturnsFemaleGender()
    {
        $this->subject->setData(
            ['gender' => \Tx_Seminars_Model_Speaker::GENDER_FEMALE]
        );

        self::assertEquals(
            \Tx_Seminars_Model_Speaker::GENDER_FEMALE,
            $this->subject->getGender()
        );
    }

    /**
     * @test
     */
    public function setGenderSetsGender()
    {
        $this->subject->setGender(\Tx_Seminars_Model_Speaker::GENDER_MALE);

        self::assertEquals(
            \Tx_Seminars_Model_Speaker::GENDER_MALE,
            $this->subject->getGender()
        );
    }

    /**
     * @test
     */
    public function hasGenderWithoutGenderReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasGender()
        );
    }

    /**
     * @test
     */
    public function hasGenderWithGenderMaleReturnsTrue()
    {
        $this->subject->setGender(\Tx_Seminars_Model_Speaker::GENDER_MALE);

        self::assertTrue(
            $this->subject->hasGender()
        );
    }

    /**
     * @test
     */
    public function hasGenderWithGenderFemaleReturnsTrue()
    {
        $this->subject->setGender(\Tx_Seminars_Model_Speaker::GENDER_FEMALE);

        self::assertTrue(
            $this->subject->hasGender()
        );
    }

    //////////////////////////////
    // Tests regarding the notes
    //////////////////////////////

    /**
     * @test
     */
    public function getNotesWithoutNotesReturnsAnEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getNotes()
        );
    }

    /**
     * @test
     */
    public function getNotesWithNonEmptyNotesReturnsNotes()
    {
        $this->subject->setData(['notes' => 'Nothing of interest.']);

        self::assertEquals(
            'Nothing of interest.',
            $this->subject->getNotes()
        );
    }

    /**
     * @test
     */
    public function setNotesSetsNotes()
    {
        $this->subject->setNotes('Nothing of interest.');

        self::assertEquals(
            'Nothing of interest.',
            $this->subject->getNotes()
        );
    }

    ///////////////////////////////
    // Test regarding the skills.
    ///////////////////////////////

    /**
     * @test
     */
    public function setSkillsSetsSkills()
    {
        $skills = new \Tx_Oelib_List();
        $this->subject->setSkills($skills);

        self::assertSame(
            $skills,
            $this->subject->getSkills()
        );
    }
}
