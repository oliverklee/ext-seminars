<?php

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_Model_SpeakerTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Seminars_Model_Speaker
     */
    private $fixture;

    protected function setUp()
    {
        $this->fixture = new Tx_Seminars_Model_Speaker();
    }

    ///////////////////////////////
    // Tests regarding the name.
    ///////////////////////////////

    /**
     * @test
     */
    public function setNameWithEmptyNameThrowsException()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'The parameter $name must not be empty.'
        );

        $this->fixture->setName('');
    }

    /**
     * @test
     */
    public function setNameSetsName()
    {
        $this->fixture->setName('John Doe');

        self::assertEquals(
            'John Doe',
            $this->fixture->getName()
        );
    }

    /**
     * @test
     */
    public function getNameWithNonEmptyNameReturnsName()
    {
        $this->fixture->setData(['title' => 'John Doe']);

        self::assertEquals(
            'John Doe',
            $this->fixture->getName()
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
        $this->fixture->setData([]);

        self::assertEquals(
            '',
            $this->fixture->getOrganization()
        );
    }

    /**
     * @test
     */
    public function getOrganizationWithNonEmptyOrganizationReturnsOrganization()
    {
        $this->fixture->setData(['organization' => 'Happy organization']);

        self::assertEquals(
            'Happy organization',
            $this->fixture->getOrganization()
        );
    }

    /**
     * @test
     */
    public function setOrganizationSetsOrganization()
    {
        $this->fixture->setOrganization('Happy organization');

        self::assertEquals(
            'Happy organization',
            $this->fixture->getOrganization()
        );
    }

    /**
     * @test
     */
    public function hasOrganizationWithoutOrganizationReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->hasOrganization()
        );
    }

    /**
     * @test
     */
    public function hasOrganizationWithNonEmptyOrganizationReturnsTrue()
    {
        $this->fixture->setOrganization('Happy organization');

        self::assertTrue(
            $this->fixture->hasOrganization()
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
        $this->fixture->setData([]);

        self::assertEquals(
            '',
            $this->fixture->getHomepage()
        );
    }

    /**
     * @test
     */
    public function getHomepageWithNonEmptyHomepageReturnsHomepage()
    {
        $this->fixture->setData(['homepage' => 'http://example.com']);

        self::assertEquals(
            'http://example.com',
            $this->fixture->getHomepage()
        );
    }

    /**
     * @test
     */
    public function setHomepageSetsHomepage()
    {
        $this->fixture->setHomepage('http://example.com');

        self::assertEquals(
            'http://example.com',
            $this->fixture->getHomepage()
        );
    }

    /**
     * @test
     */
    public function hasHomepageWithoutHomepageReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->hasHomepage()
        );
    }

    /**
     * @test
     */
    public function hasHomepageWithNonEmptyHomepageReturnsTrue()
    {
        $this->fixture->setHomepage('http://example.com');

        self::assertTrue(
            $this->fixture->hasHomepage()
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
        $this->fixture->setData([]);

        self::assertEquals(
            '',
            $this->fixture->getDescription()
        );
    }

    /**
     * @test
     */
    public function getDescriptionWithDescriptionReturnsDescription()
    {
        $this->fixture->setData(['description' => 'This is a good speaker.']);

        self::assertEquals(
            'This is a good speaker.',
            $this->fixture->getDescription()
        );
    }

    /**
     * @test
     */
    public function setDescriptionSetsDescription()
    {
        $this->fixture->setDescription('This is a good speaker.');

        self::assertEquals(
            'This is a good speaker.',
            $this->fixture->getDescription()
        );
    }

    /**
     * @test
     */
    public function hasDescriptionWithoutDescriptionReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->hasDescription()
        );
    }

    /**
     * @test
     */
    public function hasDescriptionWithDescriptionReturnsTrue()
    {
        $this->fixture->setDescription('This is a good speaker.');

        self::assertTrue(
            $this->fixture->hasDescription()
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
        $this->fixture->setData([]);

        self::assertEquals(
            '',
            $this->fixture->getAddress()
        );
    }

    /**
     * @test
     */
    public function getAddressWithNonEmptyAddressReturnsAddress()
    {
        $this->fixture->setData(['address' => 'Backstreet 42']);

        self::assertEquals(
            'Backstreet 42',
            $this->fixture->getAddress()
        );
    }

    /**
     * @test
     */
    public function setAddressSetsAddress()
    {
        $this->fixture->setAddress('Backstreet 42');

        self::assertEquals(
            'Backstreet 42',
            $this->fixture->getAddress()
        );
    }

    /**
     * @test
     */
    public function hasAddressWithoutAddressReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->hasAddress()
        );
    }

    /**
     * @test
     */
    public function hasAddressWithNonEmptyAddressReturnsTrue()
    {
        $this->fixture->setAddress('Backstreet 42');

        self::assertTrue(
            $this->fixture->hasAddress()
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
        $this->fixture->setData([]);

        self::assertEquals(
            '',
            $this->fixture->getPhoneWork()
        );
    }

    /**
     * @test
     */
    public function getPhoneWorkWithPhoneWorkReturnsPhoneWork()
    {
        $this->fixture->setData(['phone_work' => '12345']);

        self::assertEquals(
            '12345',
            $this->fixture->getPhoneWork()
        );
    }

    /**
     * @test
     */
    public function setPhoneWorkSetsPhoneWork()
    {
        $this->fixture->setPhoneWork('12345');

        self::assertEquals(
            '12345',
            $this->fixture->getPhoneWork()
        );
    }

    /**
     * @test
     */
    public function hasPhoneWorkWithoutPhoneWorkReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->hasPhoneWork()
        );
    }

    /**
     * @test
     */
    public function hasPhoneWorkWithPhoneWorkReturnsTrue()
    {
        $this->fixture->setPhoneWork('12345');

        self::assertTrue(
            $this->fixture->hasPhoneWork()
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
        $this->fixture->setData([]);

        self::assertEquals(
            '',
            $this->fixture->getPhoneHome()
        );
    }

    /**
     * @test
     */
    public function getPhoneHomeWithPhoneHomeReturnsPhoneHome()
    {
        $this->fixture->setData(['phone_home' => '12345']);

        self::assertEquals(
            '12345',
            $this->fixture->getPhoneHome()
        );
    }

    /**
     * @test
     */
    public function setPhoneHomeSetsPhoneHome()
    {
        $this->fixture->setPhoneHome('12345');

        self::assertEquals(
            '12345',
            $this->fixture->getPhoneHome()
        );
    }

    /**
     * @test
     */
    public function hasPhoneHomeWithoutPhoneHomeReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->hasPhoneHome()
        );
    }

    /**
     * @test
     */
    public function hasPhoneHomeWithPhoneHomeReturnsTrue()
    {
        $this->fixture->setPhoneHome('12345');

        self::assertTrue(
            $this->fixture->hasPhoneHome()
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
        $this->fixture->setData([]);

        self::assertEquals(
            '',
            $this->fixture->getPhoneMobile()
        );
    }

    /**
     * @test
     */
    public function getPhoneMobileWithPhoneMobileReturnsPhoneMobile()
    {
        $this->fixture->setData(['phone_mobile' => '12345']);

        self::assertEquals(
            '12345',
            $this->fixture->getPhoneMobile()
        );
    }

    /**
     * @test
     */
    public function setPhoneMobileSetsPhoneMobile()
    {
        $this->fixture->setPhoneMobile('12345');

        self::assertEquals(
            '12345',
            $this->fixture->getPhoneMobile()
        );
    }

    /**
     * @test
     */
    public function hasPhoneMobileWithoutPhoneMobileReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->hasPhoneMobile()
        );
    }

    /**
     * @test
     */
    public function hasPhoneMobileWithPhoneMobileReturnsTrue()
    {
        $this->fixture->setPhoneMobile('12345');

        self::assertTrue(
            $this->fixture->hasPhoneMobile()
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
        $this->fixture->setData([]);

        self::assertEquals(
            '',
            $this->fixture->getFax()
        );
    }

    /**
     * @test
     */
    public function getFaxWithFaxReturnsFax()
    {
        $this->fixture->setData(['fax' => '12345']);

        self::assertEquals(
            '12345',
            $this->fixture->getFax()
        );
    }

    /**
     * @test
     */
    public function setFaxSetsFax()
    {
        $this->fixture->setFax('12345');

        self::assertEquals(
            '12345',
            $this->fixture->getFax()
        );
    }

    /**
     * @test
     */
    public function hasFaxWithoutFaxReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->hasFax()
        );
    }

    /**
     * @test
     */
    public function hasFaxWithFaxReturnsTrue()
    {
        $this->fixture->setFax('12345');

        self::assertTrue(
            $this->fixture->hasFax()
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
        $this->fixture->setData([]);

        self::assertEquals(
            '',
            $this->fixture->getEMailAddress()
        );
    }

    /**
     * @test
     */
    public function getEMailAddressWithNonEmptyEMailAddressReturnsEMailAddress()
    {
        $this->fixture->setData(['email' => 'mail@example.com']);

        self::assertEquals(
            'mail@example.com',
            $this->fixture->getEMailAddress()
        );
    }

    /**
     * @test
     */
    public function setEMailAddressSetsEMailAddress()
    {
        $this->fixture->setEMailAddress('mail@example.com');

        self::assertEquals(
            'mail@example.com',
            $this->fixture->getEMailAddress()
        );
    }

    /**
     * @test
     */
    public function hasEMailAddressWithoutEMailAddressReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->hasEMailAddress()
        );
    }

    /**
     * @test
     */
    public function hasEMailAddressWithEMailAddressReturnsTrue()
    {
        $this->fixture->setEMailAddress('mail@example.com');

        self::assertTrue(
            $this->fixture->hasEMailAddress()
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
        $this->fixture->setData([]);

        self::assertEquals(
            Tx_Seminars_Model_Speaker::GENDER_UNKNOWN,
            $this->fixture->getGender()
        );
    }

    /**
     * @test
     */
    public function getGenderWithGenderMaleReturnsMaleGender()
    {
        $this->fixture->setData(
            ['gender' => Tx_Seminars_Model_Speaker::GENDER_MALE]
        );

        self::assertEquals(
            Tx_Seminars_Model_Speaker::GENDER_MALE,
            $this->fixture->getGender()
        );
    }

    /**
     * @test
     */
    public function getGenderWithGenderFemaleReturnsFemaleGender()
    {
        $this->fixture->setData(
            ['gender' => Tx_Seminars_Model_Speaker::GENDER_FEMALE]
        );

        self::assertEquals(
            Tx_Seminars_Model_Speaker::GENDER_FEMALE,
            $this->fixture->getGender()
        );
    }

    /**
     * @test
     */
    public function setGenderSetsGender()
    {
        $this->fixture->setGender(Tx_Seminars_Model_Speaker::GENDER_MALE);

        self::assertEquals(
            Tx_Seminars_Model_Speaker::GENDER_MALE,
            $this->fixture->getGender()
        );
    }

    /**
     * @test
     */
    public function hasGenderWithoutGenderReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->hasGender()
        );
    }

    /**
     * @test
     */
    public function hasGenderWithGenderMaleReturnsTrue()
    {
        $this->fixture->setGender(Tx_Seminars_Model_Speaker::GENDER_MALE);

        self::assertTrue(
            $this->fixture->hasGender()
        );
    }

    /**
     * @test
     */
    public function hasGenderWithGenderFemaleReturnsTrue()
    {
        $this->fixture->setGender(Tx_Seminars_Model_Speaker::GENDER_FEMALE);

        self::assertTrue(
            $this->fixture->hasGender()
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
        $this->fixture->setData([]);

        self::assertEquals(
            '',
            $this->fixture->getNotes()
        );
    }

    /**
     * @test
     */
    public function getNotesWithNonEmptyNotesReturnsNotes()
    {
        $this->fixture->setData(['notes' => 'Nothing of interest.']);

        self::assertEquals(
            'Nothing of interest.',
            $this->fixture->getNotes()
        );
    }

    /**
     * @test
     */
    public function setNotesSetsNotes()
    {
        $this->fixture->setNotes('Nothing of interest.');

        self::assertEquals(
            'Nothing of interest.',
            $this->fixture->getNotes()
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
        $skills = new Tx_Oelib_List();
        $this->fixture->setSkills($skills);

        self::assertSame(
            $skills,
            $this->fixture->getSkills()
        );
    }
}
