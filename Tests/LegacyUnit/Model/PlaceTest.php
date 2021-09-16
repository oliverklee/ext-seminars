<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Oelib\Mapper\CountryMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\Country;
use OliverKlee\PhpUnit\TestCase;

/**
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class PlaceTest extends TestCase
{
    /**
     * @var \Tx_Seminars_Model_Place
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new \Tx_Seminars_Model_Place();
    }

    ///////////////////////////////
    // Tests regarding the title.
    ///////////////////////////////

    /**
     * @test
     */
    public function setTitleWithEmptyTitleThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $title must not be empty.'
        );

        $this->subject->setTitle('');
    }

    /**
     * @test
     */
    public function setTitleSetsTitle()
    {
        $this->subject->setTitle('Nice place');

        self::assertEquals(
            'Nice place',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function getTitleWithNonEmptyTitleReturnsTitle()
    {
        $this->subject->setData(['title' => 'Nice place']);

        self::assertEquals(
            'Nice place',
            $this->subject->getTitle()
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

    //////////////////////////////////
    // Tests regarding the ZIP code.
    //////////////////////////////////

    /**
     * @test
     */
    public function getZipWithNonEmptyZipReturnsZip()
    {
        $this->subject->setData(['zip' => '13373']);

        self::assertEquals(
            '13373',
            $this->subject->getZip()
        );
    }

    /**
     * @test
     */
    public function setZipSetsZip()
    {
        $this->subject->setZip('13373');

        self::assertEquals(
            '13373',
            $this->subject->getZip()
        );
    }

    /**
     * @test
     */
    public function hasZipWithNonEmptyZipReturnsTrue()
    {
        $this->subject->setData(['zip' => '13373']);

        self::assertTrue(
            $this->subject->hasZip()
        );
    }

    /**
     * @test
     */
    public function hasZipWithEmptyZipReturnsFalse()
    {
        $this->subject->setData(['zip' => '']);

        self::assertFalse(
            $this->subject->hasZip()
        );
    }

    //////////////////////////////
    // Tests regarding the city.
    //////////////////////////////

    /**
     * @test
     */
    public function setCityWithEmptyCityThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $city must not be empty.'
        );

        $this->subject->setCity('');
    }

    /**
     * @test
     */
    public function setCitySetsCity()
    {
        $this->subject->setCity('Hicksville');

        self::assertEquals(
            'Hicksville',
            $this->subject->getCity()
        );
    }

    /**
     * @test
     */
    public function getCityWithNonEmptyCityReturnsCity()
    {
        $this->subject->setData(['city' => 'Hicksville']);

        self::assertEquals(
            'Hicksville',
            $this->subject->getCity()
        );
    }

    /////////////////////////////////
    // Tests regarding the country.
    /////////////////////////////////

    /**
     * @test
     */
    public function getCountryWithoutCountryReturnsNull()
    {
        $this->subject->setData([]);

        self::assertNull(
            $this->subject->getCountry()
        );
    }

    /**
     * @test
     */
    public function getCountryWithInvalidCountryCodeReturnsNull()
    {
        $this->subject->setData(['country' => '0']);

        self::assertNull(
            $this->subject->getCountry()
        );
    }

    /**
     * @test
     */
    public function getCountryWithCountryReturnsCountryInstance()
    {
        $mapper = MapperRegistry::get(CountryMapper::class);
        $country = $mapper->find(54);
        $this->subject->setData(['country' => $country->getIsoAlpha2Code()]);

        self::assertInstanceOf(
            Country::class,
            $this->subject->getCountry()
        );
    }

    /**
     * @test
     */
    public function getCountryWithCountryReturnsCountryAsModel()
    {
        $mapper = MapperRegistry::get(CountryMapper::class);
        $country = $mapper->find(54);
        $this->subject->setData(['country' => $country->getIsoAlpha2Code()]);

        self::assertSame(
            $country,
            $this->subject->getCountry()
        );
    }

    /**
     * @test
     */
    public function setCountrySetsCountry()
    {
        $mapper = MapperRegistry::get(CountryMapper::class);
        $country = $mapper->find(54);
        $this->subject->setCountry($country);

        self::assertSame(
            $country,
            $this->subject->getCountry()
        );
    }

    /**
     * @test
     */
    public function countryCanBeSetToNull()
    {
        $this->subject->setCountry();

        self::assertNull(
            $this->subject->getCountry()
        );
    }

    /**
     * @test
     */
    public function hasCountryWithoutCountryReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasCountry()
        );
    }

    /**
     * @test
     */
    public function hasCountryWithInvalidCountryReturnsFalse()
    {
        $this->subject->setData(['country' => '0']);

        self::assertFalse(
            $this->subject->hasCountry()
        );
    }

    /**
     * @test
     */
    public function hasCountryWithCountryReturnsTrue()
    {
        $mapper = MapperRegistry::get(CountryMapper::class);
        $country = $mapper->find(54);
        $this->subject->setCountry($country);

        self::assertTrue(
            $this->subject->hasCountry()
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

    //////////////////////////////////
    // Tests regarding the directions.
    //////////////////////////////////

    /**
     * @test
     */
    public function getDirectionsWithoutDirectionsReturnsAnEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getDirections()
        );
    }

    /**
     * @test
     */
    public function getDirectionsWithNonEmptyDirectionsReturnsDirections()
    {
        $this->subject->setData(['directions' => 'left, right, straight']);

        self::assertEquals(
            'left, right, straight',
            $this->subject->getDirections()
        );
    }

    /**
     * @test
     */
    public function setDirectionsSetsDirections()
    {
        $this->subject->setDirections('left, right, straight');

        self::assertEquals(
            'left, right, straight',
            $this->subject->getDirections()
        );
    }

    /**
     * @test
     */
    public function hasDirectionsWithoutDirectionsReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasDirections()
        );
    }

    /**
     * @test
     */
    public function hasDirectionsWithNonEmptyDirectionsReturnsTrue()
    {
        $this->subject->setDirections('left, right, straight');

        self::assertTrue(
            $this->subject->hasDirections()
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
}
