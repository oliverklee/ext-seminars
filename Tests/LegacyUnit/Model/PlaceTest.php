<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Oelib\Mapper\CountryMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\Country;
use OliverKlee\Seminars\Model\Place;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Model\Place
 */
final class PlaceTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var Place
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Place();
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
    public function getCityWithNonEmptyCityReturnsCity(): void
    {
        $this->subject->setData(['city' => 'Hicksville']);

        self::assertEquals(
            'Hicksville',
            $this->subject->getCity()
        );
    }

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
        $country = MapperRegistry::get(CountryMapper::class)->find(54);
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
        $country = MapperRegistry::get(CountryMapper::class)->find(54);
        $this->subject->setData(['country' => $country->getIsoAlpha2Code()]);

        self::assertSame(
            $country,
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
        $country = MapperRegistry::get(CountryMapper::class)->find(54);
        $this->subject->setData(['country' => $country->getIsoAlpha2Code()]);

        self::assertTrue(
            $this->subject->hasCountry()
        );
    }
}
