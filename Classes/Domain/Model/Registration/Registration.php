<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Registration;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\Seminars\Domain\Model\AccommodationOption;
use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Model\Event\EventDate;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Model\FoodOption;
use OliverKlee\Seminars\Domain\Model\RawDataInterface;
use OliverKlee\Seminars\Domain\Model\RawDataTrait;
use OliverKlee\Seminars\Domain\Model\RegistrationCheckbox;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * This class represents a registration (or a waiting list entry) for an event.
 */
class Registration extends AbstractEntity implements RawDataInterface
{
    use RawDataTrait;
    use AttendeesTrait;
    use BillingAddressTrait;
    use PaymentTrait;

    /**
     * @var string
     * @Extbase\Validate("StringLength", options={"maximum": 255})
     */
    protected $title = '';

    /**
     * @var \OliverKlee\Seminars\Domain\Model\Event\Event|null
     * @phpstan-var Event|LazyLoadingProxy|null
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $event;

    /**
     * @var bool
     */
    protected $onWaitingList = false;

    /**
     * @var string
     * @Extbase\Validate("StringLength", options={"maximum": 16383})
     */
    protected $interests = '';

    /**
     * @var string
     * @Extbase\Validate("StringLength", options={"maximum": 16383})
     */
    protected $expectations = '';

    /**
     * @var string
     * @Extbase\Validate("StringLength", options={"maximum": 16383})
     */
    protected $backgroundKnowledge = '';

    /**
     * @var string
     * @Extbase\Validate("StringLength", options={"maximum": 16383})
     */
    protected $comments = '';

    /**
     * @var string
     * @Extbase\Validate("StringLength", options={"maximum": 16383})
     */
    protected $knownFrom = '';

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OliverKlee\Seminars\Domain\Model\AccommodationOption>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $accommodationOptions;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OliverKlee\Seminars\Domain\Model\FoodOption>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $foodOptions;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OliverKlee\Seminars\Domain\Model\RegistrationCheckbox>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $registrationCheckboxes;

    /**
     * @var bool
     * @TYPO3\CMS\Extbase\Annotation\ORM\Transient
     * @Extbase\Validate(validator="Boolean", options={"is": "1"})
     */
    protected $consentedToTermsAndConditions = false;

    /**
     * @var bool
     * @TYPO3\CMS\Extbase\Annotation\ORM\Transient
     * @Extbase\Validate(validator="Boolean", options={"is": "1"})
     */
    protected $consentedToAdditionalTerms = false;

    public function __construct()
    {
        $this->additionalPersons = new ObjectStorage();
        $this->accommodationOptions = new ObjectStorage();
        $this->foodOptions = new ObjectStorage();
        $this->registrationCheckboxes = new ObjectStorage();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $name): void
    {
        $this->title = $name;
    }

    public function getEvent(): ?Event
    {
        $event = $this->event;
        if ($event instanceof LazyLoadingProxy) {
            $event = $event->_loadRealInstance();
            if ($event instanceof Event) {
                $this->event = $event;
            }
        }

        return $event;
    }

    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }

    /**
     * Checks whether the associated event is set and of a type to which someone actually can register
     * (a single event or an event date, but not an event topic).
     */
    public function hasValidEventType(): bool
    {
        $event = $this->getEvent();

        return $event instanceof SingleEvent || $event instanceof EventDate;
    }

    /**
     * Checks whether all associations are set in a way that this registration can be used.
     *
     * This safeguards against cases where an event or a user is deleted, but the registration is not.
     */
    public function hasNecessaryAssociations(): bool
    {
        return $this->hasValidEventType() && $this->getUser() instanceof FrontendUser;
    }

    public function isOnWaitingList(): bool
    {
        return $this->onWaitingList;
    }

    public function setOnWaitingList(bool $onWaitingList): void
    {
        $this->onWaitingList = $onWaitingList;
    }

    public function getInterests(): string
    {
        return $this->interests;
    }

    public function setInterests(string $interests): void
    {
        $this->interests = $interests;
    }

    public function getExpectations(): string
    {
        return $this->expectations;
    }

    public function setExpectations(string $expectations): void
    {
        $this->expectations = $expectations;
    }

    public function getBackgroundKnowledge(): string
    {
        return $this->backgroundKnowledge;
    }

    public function setBackgroundKnowledge(string $backgroundKnowledge): void
    {
        $this->backgroundKnowledge = $backgroundKnowledge;
    }

    public function getComments(): string
    {
        return $this->comments;
    }

    public function setComments(string $comments): void
    {
        $this->comments = $comments;
    }

    public function getKnownFrom(): string
    {
        return $this->knownFrom;
    }

    public function setKnownFrom(string $knownFrom): void
    {
        $this->knownFrom = $knownFrom;
    }

    /**
     * @return ObjectStorage<AccommodationOption>
     */
    public function getAccommodationOptions(): ObjectStorage
    {
        return $this->accommodationOptions;
    }

    /**
     * @param ObjectStorage<AccommodationOption> $accommodationOptions
     */
    public function setAccommodationOptions(ObjectStorage $accommodationOptions): void
    {
        $this->accommodationOptions = $accommodationOptions;
    }

    /**
     * This method can be used to work around that iterating over the accommodation options on the registration
     * confirmation page only provides the first selected one.
     *
     * @return list<string>
     */
    public function getAccommodationOptionTitles(): array
    {
        $titles = [];

        foreach ($this->getAccommodationOptions() as $option) {
            $titles[] = $option->getTitle();
        }

        return $titles;
    }

    /**
     * @return ObjectStorage<FoodOption>
     */
    public function getFoodOptions(): ObjectStorage
    {
        return $this->foodOptions;
    }

    /**
     * @param ObjectStorage<FoodOption> $foodOptions
     */
    public function setFoodOptions(ObjectStorage $foodOptions): void
    {
        $this->foodOptions = $foodOptions;
    }

    /**
     * This method can be used to work around that iterating over the food options on the registration
     * confirmation page only provides the first selected one.
     *
     * @return list<string>
     */
    public function getFoodOptionTitles(): array
    {
        $titles = [];

        foreach ($this->getFoodOptions() as $option) {
            $titles[] = $option->getTitle();
        }

        return $titles;
    }

    /**
     * @return ObjectStorage<RegistrationCheckbox>
     */
    public function getRegistrationCheckboxes(): ObjectStorage
    {
        return $this->registrationCheckboxes;
    }

    /**
     * @param ObjectStorage<RegistrationCheckbox> $registrationCheckboxes
     */
    public function setRegistrationCheckboxes(ObjectStorage $registrationCheckboxes): void
    {
        $this->registrationCheckboxes = $registrationCheckboxes;
    }

    /**
     * This method can be used to work around that iterating over the registration checkboxes on the registration
     * confirmation page only provides the first selected one.
     *
     * @return list<string>
     */
    public function getRegistrationCheckboxTitles(): array
    {
        $titles = [];

        foreach ($this->getRegistrationCheckboxes() as $checkbox) {
            $titles[] = $checkbox->getTitle();
        }

        return $titles;
    }

    public function hasConsentedToTermsAndConditions(): bool
    {
        return $this->consentedToTermsAndConditions;
    }

    public function setConsentedToTermsAndConditions(bool $consent): void
    {
        $this->consentedToTermsAndConditions = $consent;
    }

    public function hasConsentedToAdditionalTerms(): bool
    {
        return $this->consentedToAdditionalTerms;
    }

    public function setConsentedToAdditionalTerms(bool $consent): void
    {
        $this->consentedToAdditionalTerms = $consent;
    }
}
