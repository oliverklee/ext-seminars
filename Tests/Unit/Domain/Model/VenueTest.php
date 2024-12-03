<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Model;

use OliverKlee\Seminars\Domain\Model\Venue;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\Venue
 */
final class VenueTest extends UnitTestCase
{
    private Venue $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Venue();
    }

    /**
     * @test
     */
    public function isAbstractEntity(): void
    {
        self::assertInstanceOf(AbstractEntity::class, $this->subject);
    }

    /**
     * @test
     */
    public function getTitleInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getTitle());
    }

    /**
     * @test
     */
    public function setTitleSetsTitle(): void
    {
        $value = 'JH Bonn';
        $this->subject->setTitle($value);

        self::assertSame($value, $this->subject->getTitle());
    }

    /**
     * @test
     */
    public function getContactPersonInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getContactPerson());
    }

    /**
     * @test
     */
    public function setContactPersonSetsContactPerson(): void
    {
        $value = 'Riso';
        $this->subject->setContactPerson($value);

        self::assertSame($value, $this->subject->getContactPerson());
    }

    /**
     * @test
     */
    public function getEmailAddressInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getEmailAddress());
    }

    /**
     * @test
     */
    public function setEmailAddressSetsEmailAddress(): void
    {
        $value = 'riso@example.com';
        $this->subject->setEmailAddress($value);

        self::assertSame($value, $this->subject->getEmailAddress());
    }

    /**
     * @test
     */
    public function getPhoneNumberInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getPhoneNumber());
    }

    /**
     * @test
     */
    public function setPhoneNumberSetsPhoneNumber(): void
    {
        $value = '+49 123 456789';
        $this->subject->setPhoneNumber($value);

        self::assertSame($value, $this->subject->getPhoneNumber());
    }

    /**
     * @test
     */
    public function getFullAddressInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getFullAddress());
    }

    /**
     * @test
     */
    public function setFullAddressSetsFullAddress(): void
    {
        $value = 'Club-Mate';
        $this->subject->setFullAddress($value);

        self::assertSame($value, $this->subject->getFullAddress());
    }

    /**
     * @test
     */
    public function getCityInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getCity());
    }

    /**
     * @test
     */
    public function setCitySetsCity(): void
    {
        $value = 'Berlin';
        $this->subject->setCity($value);

        self::assertSame($value, $this->subject->getCity());
    }
}
