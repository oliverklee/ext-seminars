<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Oelib\Mapper\CountryMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\Country;
use OliverKlee\Seminars\Model\Place;
use PHPUnit\Framework\TestCase;

final class PlaceTest extends TestCase
{
    /**
     * @var Place
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new Place();
    }

    ///////////////////////////////
    // Tests regarding the title.
    ///////////////////////////////

    /**
     * @test
     */
    public function setTitleWithEmptyTitleThrowsException(): void
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
    public function setTitleSetsTitle(): void
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
    public function getTitleWithNonEmptyTitleReturnsTitle(): void
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

    //////////////////////////////////
    // Tests regarding the ZIP code.
    //////////////////////////////////

    /**
     * @test
     */
    public function getZipWithNonEmptyZipReturnsZip(): void
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
    public function setZipSetsZip(): void
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
    public function hasZipWithNonEmptyZipReturnsTrue(): void
    {
        $this->subject->setData(['zip' => '13373']);

        self::assertTrue(
            $this->subject->hasZip()
        );
    }

    /**
     * @test
     */
    public function hasZipWithEmptyZipReturnsFalse(): void
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
    public function setCityWithEmptyCityThrowsException(): void
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
    public function setCitySetsCity(): void
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
    public function getCityWithNonEmptyCityReturnsCity(): void
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
    public function getCountryWithoutCountryReturnsNull(): void
    {
        $this->subject->setData([]);

        self::assertNull(
            $this->subject->getCountry()
        );
    }

    /**
     * @test
     */
    public function getCountryWithInvalidCountryCodeReturnsNull(): void
    {
        $this->subject->setData(['country' => '0']);

        self::assertNull(
            $this->subject->getCountry()
        );
    }

    /**
     * @test
     */
    public function getCountryWithCountryReturnsCountryInstance(): void
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
    public function getCountryWithCountryReturnsCountryAsModel(): void
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
    public function setCountrySetsCountry(): void
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
    public function countryCanBeSetToNull(): void
    {
        $this->subject->setCountry();

        self::assertNull(
            $this->subject->getCountry()
        );
    }

    /**
     * @test
     */
    public function hasCountryWithoutCountryReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasCountry()
        );
    }

    /**
     * @test
     */
    public function hasCountryWithInvalidCountryReturnsFalse(): void
    {
        $this->subject->setData(['country' => '0']);

        self::assertFalse(
            $this->subject->hasCountry()
        );
    }

    /**
     * @test
     */
    public function hasCountryWithCountryReturnsTrue(): void
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
        $this->subject->setHomepage('https://example.com');

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
    public function getDirectionsWithoutDirectionsReturnsAnEmptyString(): void
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
    public function getDirectionsWithNonEmptyDirectionsReturnsDirections(): void
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
    public function setDirectionsSetsDirections(): void
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
    public function hasDirectionsWithoutDirectionsReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasDirections()
        );
    }

    /**
     * @test
     */
    public function hasDirectionsWithNonEmptyDirectionsReturnsTrue(): void
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
}
