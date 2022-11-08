<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Model\Registration;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Model\Event\EventDate;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Domain\Model\Event\EventTopic;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Model\Registration\Registration;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\Registration\AttendeesTrait
 * @covers \OliverKlee\Seminars\Domain\Model\Registration\BillingAddressTrait
 * @covers \OliverKlee\Seminars\Domain\Model\Registration\PaymentTrait
 * @covers \OliverKlee\Seminars\Domain\Model\Registration\Registration
 */
final class RegistrationTest extends UnitTestCase
{
    /**
     * @var Registration
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Registration();
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
        $value = 'the latest registration';
        $this->subject->setTitle($value);

        self::assertSame($value, $this->subject->getTitle());
    }

    /**
     * @test
     */
    public function getEventInitiallyReturnsNull(): void
    {
        self::assertNull($this->subject->getEvent());
    }

    /**
     * @test
     */
    public function setEventCanSetEventToSingleEvent(): void
    {
        $model = new SingleEvent();
        $this->subject->setEvent($model);

        self::assertSame($model, $this->subject->getEvent());
    }

    /**
     * @test
     */
    public function setEventCanSetEventToEventDate(): void
    {
        $model = new EventDate();
        $this->subject->setEvent($model);

        self::assertSame($model, $this->subject->getEvent());
    }

    /**
     * @test
     */
    public function hasValidEventTypeWithoutEventReturnsFalse(): void
    {
        self::assertFalse($this->subject->hasValidEventType());
    }

    /**
     * @test
     */
    public function hasValidEventTypeWithEventTopicReturnsFalse(): void
    {
        $this->subject->setEvent(new EventTopic());

        self::assertFalse($this->subject->hasValidEventType());
    }

    /**
     * @return array<string, array{0: Event}>
     */
    public function validEventTypesDataProvider(): array
    {
        return [
            'single event' => [new SingleEvent()],
            'event date' => [new EventDate()],
        ];
    }

    /**
     * @test
     *
     * @dataProvider validEventTypesDataProvider
     */
    public function hasValidEventTypeWithValidEventTypeReturnsTrue(Event $event): void
    {
        $this->subject->setEvent($event);

        self::assertTrue($this->subject->hasValidEventType());
    }

    /**
     * @test
     */
    public function getUserInitiallyReturnsNull(): void
    {
        self::assertNull($this->subject->getUser());
    }

    /**
     * @test
     */
    public function setUserSetsUser(): void
    {
        $model = new FrontendUser();
        $this->subject->setUser($model);

        self::assertSame($model, $this->subject->getUser());
    }

    /**
     * @test
     *
     * @dataProvider validEventTypesDataProvider
     */
    public function hasNecessaryAssociationsWithUserAndValidEventTypeReturnsTrue(Event $event): void
    {
        $this->subject->setUser(new FrontendUser());
        $this->subject->setEvent($event);

        self::assertTrue($this->subject->hasNecessaryAssociations());
    }

    /**
     * @test
     *
     * @dataProvider validEventTypesDataProvider
     */
    public function hasNecessaryAssociationsWithoutUserAndWithValidEventTypeReturnsFalse(Event $event): void
    {
        $this->subject->setEvent($event);

        self::assertFalse($this->subject->hasNecessaryAssociations());
    }

    /**
     * @test
     */
    public function hasNecessaryAssociationsWithUserAndEventTopicReturnsFalse(): void
    {
        $this->subject->setUser(new FrontendUser());
        $this->subject->setEvent(new EventTopic());

        self::assertFalse($this->subject->hasNecessaryAssociations());
    }

    /**
     * @test
     */
    public function hasNecessaryAssociationsWithUserAndWithoutEventReturnsFalse(): void
    {
        $this->subject->setUser(new FrontendUser());

        self::assertFalse($this->subject->hasNecessaryAssociations());
    }

    /**
     * @test
     */
    public function hasNecessaryAssociationsWithNeitherUserNorEventReturnsFalse(): void
    {
        self::assertFalse($this->subject->hasNecessaryAssociations());
    }

    /**
     * @test
     */
    public function isOnWaitingListInitiallyReturnsFalse(): void
    {
        self::assertFalse($this->subject->isOnWaitingList());
    }

    /**
     * @test
     */
    public function setOnWaitingListSetsOnWaitingList(): void
    {
        $this->subject->setOnWaitingList(true);

        self::assertTrue($this->subject->isOnWaitingList());
    }

    /**
     * @test
     */
    public function getPriceCodeInitiallyReturnsEmptyNull(): void
    {
        self::assertNull($this->subject->getPriceCode());
    }

    /**
     * @return array<string, array{0: EventInterface::PRICE_*}>
     */
    public function validPriceCodeDataProvider(): array
    {
        return [
            'standard' => [EventInterface::PRICE_STANDARD],
            'early bird' => [EventInterface::PRICE_EARLY_BIRD],
            'special' => [EventInterface::PRICE_SPECIAL],
            'special early bird' => [EventInterface::PRICE_SPECIAL_EARLY_BIRD],
        ];
    }

    /**
     * @test
     * @param EventInterface::PRICE_* $priceCode
     * @dataProvider validPriceCodeDataProvider
     */
    public function setPriceCodeSetsPriceCode(string $priceCode): void
    {
        $this->subject->setPriceCode($priceCode);

        self::assertSame($priceCode, $this->subject->getPriceCode());
    }

    /**
     * @test
     */
    public function getSeatsInitiallyReturnsZero(): void
    {
        self::assertSame(0, $this->subject->getSeats());
    }

    /**
     * @test
     */
    public function setSeatsSetsSeats(): void
    {
        $value = 123456;
        $this->subject->setSeats($value);

        self::assertSame($value, $this->subject->getSeats());
    }

    /**
     * @test
     */
    public function hasRegisteredThemselvesInitiallyReturnsFalse(): void
    {
        self::assertFalse($this->subject->hasRegisteredThemselves());
    }

    /**
     * @test
     */
    public function setRegisteredThemselvesSetsRegisteredThemselves(): void
    {
        $this->subject->setRegisteredThemselves(true);

        self::assertTrue($this->subject->hasRegisteredThemselves());
    }

    /**
     * @test
     */
    public function getTotalPriceInitiallyReturnsZero(): void
    {
        self::assertSame(0.0, $this->subject->getTotalPrice());
    }

    /**
     * @test
     */
    public function setTotalPriceSetsTotalPrice(): void
    {
        $value = 1234.56;
        $this->subject->setTotalPrice($value);

        self::assertSame($value, $this->subject->getTotalPrice());
    }

    /**
     * @test
     */
    public function getAttendeesNamesInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getAttendeesNames());
    }

    /**
     * @test
     */
    public function setAttendeesNamesSetsAttendeesNames(): void
    {
        $value = 'Club-Mate';
        $this->subject->setAttendeesNames($value);

        self::assertSame($value, $this->subject->getAttendeesNames());
    }

    /**
     * @test
     */
    public function getBillingCompanyInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getBillingCompany());
    }

    /**
     * @test
     */
    public function setBillingCompanySetsBillingCompany(): void
    {
        $value = 'Club-Mate';
        $this->subject->setBillingCompany($value);

        self::assertSame($value, $this->subject->getBillingCompany());
    }

    /**
     * @test
     */
    public function getBillingFullNameInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getBillingFullName());
    }

    /**
     * @test
     */
    public function setBillingFullNameSetsBillingFullName(): void
    {
        $value = 'Club-Mate';
        $this->subject->setBillingFullName($value);

        self::assertSame($value, $this->subject->getBillingFullName());
    }

    /**
     * @test
     */
    public function getBillingStreetAddressInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getBillingStreetAddress());
    }

    /**
     * @test
     */
    public function setBillingStreetAddressSetsBillingStreetAddress(): void
    {
        $value = 'Club-Mate';
        $this->subject->setBillingStreetAddress($value);

        self::assertSame($value, $this->subject->getBillingStreetAddress());
    }

    /**
     * @test
     */
    public function getBillingZipCodeInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getBillingZipCode());
    }

    /**
     * @test
     */
    public function setBillingZipCodeSetsBillingZipCode(): void
    {
        $value = 'Club-Mate';
        $this->subject->setBillingZipCode($value);

        self::assertSame($value, $this->subject->getBillingZipCode());
    }

    /**
     * @test
     */
    public function getBillingCityInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getBillingCity());
    }

    /**
     * @test
     */
    public function setBillingCitySetsBillingCity(): void
    {
        $value = 'Club-Mate';
        $this->subject->setBillingCity($value);

        self::assertSame($value, $this->subject->getBillingCity());
    }

    /**
     * @test
     */
    public function getBillingCountryInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getBillingCountry());
    }

    /**
     * @test
     */
    public function setBillingCountrySetsBillingCountry(): void
    {
        $value = 'Club-Mate';
        $this->subject->setBillingCountry($value);

        self::assertSame($value, $this->subject->getBillingCountry());
    }

    /**
     * @test
     */
    public function getBillingPhoneNumberInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getBillingPhoneNumber());
    }

    /**
     * @test
     */
    public function setBillingPhoneNumberSetsBillingPhoneNumber(): void
    {
        $value = 'Club-Mate';
        $this->subject->setBillingPhoneNumber($value);

        self::assertSame($value, $this->subject->getBillingPhoneNumber());
    }

    /**
     * @test
     */
    public function getBillingEmailAddressInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getBillingEmailAddress());
    }

    /**
     * @test
     */
    public function setBillingEmailAddressSetsBillingEmailAddress(): void
    {
        $value = 'Club-Mate';
        $this->subject->setBillingEmailAddress($value);

        self::assertSame($value, $this->subject->getBillingEmailAddress());
    }

    /**
     * @test
     */
    public function getInterestsInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getInterests());
    }

    /**
     * @test
     */
    public function setInterestsSetsInterests(): void
    {
        $value = 'Club-Mate';
        $this->subject->setInterests($value);

        self::assertSame($value, $this->subject->getInterests());
    }

    /**
     * @test
     */
    public function getExpectationsInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getExpectations());
    }

    /**
     * @test
     */
    public function setExpectationsSetsExpectations(): void
    {
        $value = 'Club-Mate';
        $this->subject->setExpectations($value);

        self::assertSame($value, $this->subject->getExpectations());
    }

    /**
     * @test
     */
    public function getCommentsInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getComments());
    }

    /**
     * @test
     */
    public function setCommentsSetsComments(): void
    {
        $value = 'Club-Mate';
        $this->subject->setComments($value);

        self::assertSame($value, $this->subject->getComments());
    }

    /**
     * @test
     */
    public function getKnownFromInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getKnownFrom());
    }

    /**
     * @test
     */
    public function setKnownFromSetsKnownFrom(): void
    {
        $value = 'Club-Mate';
        $this->subject->setKnownFrom($value);

        self::assertSame($value, $this->subject->getKnownFrom());
    }
}
