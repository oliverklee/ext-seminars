<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\PhpUnit\TestCase;

final class SpeakerTest extends TestCase
{
    /**
     * @var \Tx_Seminars_Model_Speaker
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new \Tx_Seminars_Model_Speaker();
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
        $this->subject->setName('John Doe');

        self::assertEquals(
            'John Doe',
            $this->subject->getName()
        );
    }

    /**
     * @test
     */
    public function getNameWithNonEmptyNameReturnsName(): void
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
    public function getOrganizationWithoutOrganizationReturnsAnEmptyString(): void
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
    public function getOrganizationWithNonEmptyOrganizationReturnsOrganization(): void
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
    public function setOrganizationSetsOrganization(): void
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
    public function hasOrganizationWithoutOrganizationReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasOrganization()
        );
    }

    /**
     * @test
     */
    public function hasOrganizationWithNonEmptyOrganizationReturnsTrue(): void
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
    public function getHomepageWithoutHomepageReturnsAnEmptyString(): void
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
        $this->subject->setData(['homepage' => 'http://example.com']);

        self::assertEquals(
            'http://example.com',
            $this->subject->getHomepage()
        );
    }

    /**
     * @test
     */
    public function setHomepageSetsHomepage(): void
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
    public function hasHomepageWithoutHomepageReturnsFalse(): void
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
    public function getDescriptionWithoutDescriptionReturnsAnEmptyString(): void
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
    public function getDescriptionWithDescriptionReturnsDescription(): void
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
    public function setDescriptionSetsDescription(): void
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
    public function hasDescriptionWithoutDescriptionReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasDescription()
        );
    }

    /**
     * @test
     */
    public function hasDescriptionWithDescriptionReturnsTrue(): void
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
    public function getAddressWithoutAddressReturnsAnEmptyString(): void
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
    public function getAddressWithNonEmptyAddressReturnsAddress(): void
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
    public function setAddressSetsAddress(): void
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
    public function hasAddressWithoutAddressReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasAddress()
        );
    }

    /**
     * @test
     */
    public function hasAddressWithNonEmptyAddressReturnsTrue(): void
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
    public function getPhoneWorkWithoutPhoneWorkReturnsAnEmptyString(): void
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
    public function getPhoneWorkWithPhoneWorkReturnsPhoneWork(): void
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
    public function setPhoneWorkSetsPhoneWork(): void
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
    public function hasPhoneWorkWithoutPhoneWorkReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasPhoneWork()
        );
    }

    /**
     * @test
     */
    public function hasPhoneWorkWithPhoneWorkReturnsTrue(): void
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
    public function getPhoneHomeWithoutPhoneHomeReturnsAnEmptyString(): void
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
    public function getPhoneHomeWithPhoneHomeReturnsPhoneHome(): void
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
    public function setPhoneHomeSetsPhoneHome(): void
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
    public function hasPhoneHomeWithoutPhoneHomeReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasPhoneHome()
        );
    }

    /**
     * @test
     */
    public function hasPhoneHomeWithPhoneHomeReturnsTrue(): void
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
    public function getPhoneMobileWithoutPhoneMobileReturnsAnEmptyString(): void
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
    public function getPhoneMobileWithPhoneMobileReturnsPhoneMobile(): void
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
    public function setPhoneMobileSetsPhoneMobile(): void
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
    public function hasPhoneMobileWithoutPhoneMobileReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasPhoneMobile()
        );
    }

    /**
     * @test
     */
    public function hasPhoneMobileWithPhoneMobileReturnsTrue(): void
    {
        $this->subject->setPhoneMobile('12345');

        self::assertTrue(
            $this->subject->hasPhoneMobile()
        );
    }

    ////////////////////////////////////////
    // Tests regarding the e-mail address.
    ////////////////////////////////////////

    /**
     * @test
     */
    public function getEmailAddressWithoutEmailAddressReturnsAnEmptyString(): void
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
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
    public function hasEmailAddressWithoutEmailAddressReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasEmailAddress()
        );
    }

    /**
     * @test
     */
    public function hasEmailAddressWithEmailAddressReturnsTrue(): void
    {
        $this->subject->setEmailAddress('mail@example.com');

        self::assertTrue(
            $this->subject->hasEmailAddress()
        );
    }

    ////////////////////////////////
    // Tests regarding the gender.
    ////////////////////////////////

    /**
     * @test
     */
    public function getGenderWithoutGenderReturnsUnknownGender(): void
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
    public function getGenderWithGenderMaleReturnsMaleGender(): void
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
    public function getGenderWithGenderFemaleReturnsFemaleGender(): void
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
    public function setGenderSetsGender(): void
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
    public function hasGenderWithoutGenderReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasGender()
        );
    }

    /**
     * @test
     */
    public function hasGenderWithGenderMaleReturnsTrue(): void
    {
        $this->subject->setGender(\Tx_Seminars_Model_Speaker::GENDER_MALE);

        self::assertTrue(
            $this->subject->hasGender()
        );
    }

    /**
     * @test
     */
    public function hasGenderWithGenderFemaleReturnsTrue(): void
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
    public function getNotesWithoutNotesReturnsAnEmptyString(): void
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
    public function getNotesWithNonEmptyNotesReturnsNotes(): void
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
    public function setNotesSetsNotes(): void
    {
        $this->subject->setNotes('Nothing of interest.');

        self::assertEquals(
            'Nothing of interest.',
            $this->subject->getNotes()
        );
    }

    // Test regarding the skills.

    /**
     * @test
     */
    public function setSkillsSetsSkills(): void
    {
        /** @var Collection<\Tx_Seminars_Model_Skill> $skills */
        $skills = new Collection();
        $this->subject->setSkills($skills);

        self::assertSame(
            $skills,
            $this->subject->getSkills()
        );
    }
}
