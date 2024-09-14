<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Registration;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use TYPO3\CMS\Extbase\Annotation\Validate;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Provides attendees-related fields to `Registration`.
 *
 * @phpstan-require-extends Registration
 */
trait AttendeesTrait
{
    /**
     * @var FrontendUser|null
     * @phpstan-var FrontendUser|LazyLoadingProxy|null
     * @Lazy
     */
    protected $user;

    /**
     * @var int
     * @Validate("NumberRange", options={"minimum": 1, "maximum": 999})
     */
    protected $seats = 1;

    /**
     * @var bool
     */
    protected $registeredThemselves = false;

    /**
     * @var string
     * @Validate("StringLength", options={"maximum": 16383})
     */
    protected $attendeesNames = '';

    /**
     * @var string
     * @Validate("StringLength", options={"maximum": 1024})
     * @Transient
     */
    protected $jsonEncodedAdditionAttendees = '{}';

    /**
     * @var ObjectStorage<FrontendUser>
     * @Lazy
     */
    protected $additionalPersons;

    public function getUser(): ?FrontendUser
    {
        $user = $this->user;
        if ($user instanceof LazyLoadingProxy) {
            $user = $user->_loadRealInstance();
            \assert($user instanceof FrontendUser);
            $this->user = $user;
        }

        return $user;
    }

    public function setUser(FrontendUser $user): void
    {
        $this->user = $user;
    }

    public function getSeats(): int
    {
        return $this->seats;
    }

    public function setSeats(int $seats): void
    {
        $this->seats = $seats;
    }

    public function hasRegisteredThemselves(): bool
    {
        return $this->registeredThemselves;
    }

    public function setRegisteredThemselves(bool $registeredThemselves): void
    {
        $this->registeredThemselves = $registeredThemselves;
    }

    public function getAttendeesNames(): string
    {
        return $this->attendeesNames;
    }

    public function setAttendeesNames(string $attendeesNames): void
    {
        $this->attendeesNames = $attendeesNames;
    }

    public function getJsonEncodedAdditionAttendees(): string
    {
        return $this->jsonEncodedAdditionAttendees;
    }

    public function setJsonEncodedAdditionAttendees(string $json): void
    {
        $this->jsonEncodedAdditionAttendees = $json;
    }

    /**
     * @return ObjectStorage<FrontendUser>
     */
    public function getAdditionalPersons(): ObjectStorage
    {
        return $this->additionalPersons;
    }

    /**
     * @param ObjectStorage<FrontendUser> $persons
     */
    public function setAdditionalPersons(ObjectStorage $persons): void
    {
        $this->additionalPersons = $persons;
    }

    public function addAdditionalPerson(FrontendUser $person): void
    {
        $this->additionalPersons->attach($person);
    }
}
