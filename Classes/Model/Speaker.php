<?php

declare(strict_types=1);

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Interfaces\MailRole;
use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Seminars\Model\FrontEndUser;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class represents an speaker.
 */
class Tx_Seminars_Model_Speaker extends AbstractModel implements MailRole
{
    /**
     * @var int the gender type for speakers without gender
     */
    public const GENDER_UNKNOWN = 0;

    /**
     * @var int the gender type male for a speaker
     */
    public const GENDER_MALE = 1;

    /**
     * @var int the gender type female for a speaker
     */
    public const GENDER_FEMALE = 2;

    /**
     * @return string our name, will not be empty
     */
    public function getName(): string
    {
        return $this->getAsString('title');
    }

    /**
     * @param string $name our name to set, must not be empty
     */
    public function setName(string $name): void
    {
        if ($name == '') {
            throw new \InvalidArgumentException('The parameter $name must not be empty.', 1333297036);
        }

        $this->setAsString('title', $name);
    }

    /**
     * @return string our organization, will be empty if there's no organization set
     */
    public function getOrganization(): string
    {
        return $this->getAsString('organization');
    }

    /**
     * @param string $organization our organization, may be empty
     */
    public function setOrganization(string $organization): void
    {
        $this->setAsString('organization', $organization);
    }

    public function hasOrganization(): bool
    {
        return $this->hasString('organization');
    }

    /**
     * @return string our homepage, will be empty if there's no homepage set
     */
    public function getHomepage(): string
    {
        return $this->getAsString('homepage');
    }

    /**
     * @param string $homepage our homepage, may be empty
     */
    public function setHomepage(string $homepage): void
    {
        $this->setAsString('homepage', $homepage);
    }

    public function hasHomepage(): bool
    {
        return $this->hasString('homepage');
    }

    /**
     * @return string our description, will be empty if there's no description set
     */
    public function getDescription(): string
    {
        return $this->getAsString('description');
    }

    /**
     * @param string $description our description to set, may be empty
     */
    public function setDescription(string $description): void
    {
        $this->setAsString('description', $description);
    }

    public function hasDescription(): bool
    {
        return $this->hasString('description');
    }

    /**
     * @return Collection<\Tx_Seminars_Model_Skill>
     */
    public function getSkills(): Collection
    {
        /** @var Collection<\Tx_Seminars_Model_Skill> $skills */
        $skills = $this->getAsCollection('skills');

        return $skills;
    }

    /**
     * @param Collection<\Tx_Seminars_Model_Skill> $skills
     */
    public function setSkills(Collection $skills): void
    {
        $this->set('skills', $skills);
    }

    /**
     * @return string our address, will be empty if there's no address set
     */
    public function getAddress(): string
    {
        return $this->getAsString('address');
    }

    /**
     * @param string $address our address to set, may be empty
     */
    public function setAddress(string $address): void
    {
        $this->setAsString('address', $address);
    }

    public function hasAddress(): bool
    {
        return $this->hasString('address');
    }

    /**
     * @return string our work telephone number, will be empty if there's no work telephone number set
     */
    public function getPhoneWork(): string
    {
        return $this->getAsString('phone_work');
    }

    /**
     * @param string $phoneWork our work telephone number to set, may be empty
     */
    public function setPhoneWork(string $phoneWork): void
    {
        $this->setAsString('phone_work', $phoneWork);
    }

    public function hasPhoneWork(): bool
    {
        return $this->hasString('phone_work');
    }

    /**
     * @return string our home telephone number, will be empty if there's no home telephone number set
     */
    public function getPhoneHome(): string
    {
        return $this->getAsString('phone_home');
    }

    /**
     * @param string $phoneHome our home telephone number to set, may be empty
     */
    public function setPhoneHome(string $phoneHome): void
    {
        $this->setAsString('phone_home', $phoneHome);
    }

    public function hasPhoneHome(): bool
    {
        return $this->hasString('phone_home');
    }

    /**
     * @return string our mobile telephone number, will be empty if there's no mobile telephone number set
     */
    public function getPhoneMobile(): string
    {
        return $this->getAsString('phone_mobile');
    }

    /**
     * @param string $phoneMobile our mobile telephone number to set, may be empty
     */
    public function setPhoneMobile(string $phoneMobile): void
    {
        $this->setAsString('phone_mobile', $phoneMobile);
    }

    public function hasPhoneMobile(): bool
    {
        return $this->hasString('phone_mobile');
    }

    /**
     * @return string our e-mail address, will not be empty
     */
    public function getEmailAddress(): string
    {
        return $this->getAsString('email');
    }

    /**
     * @param string $eMailAddress our e-mail address, may be empty
     */
    public function setEmailAddress(string $eMailAddress): void
    {
        $this->setAsString('email', $eMailAddress);
    }

    public function hasEmailAddress(): bool
    {
        return $this->hasString('email');
    }

    /**
     * @return int our gender, will be either GENDER_MALE, GENDER_FEMALE or GENDER_UNKNOWN if the speaker has no gender
     */
    public function getGender(): int
    {
        return $this->getAsInteger('gender');
    }

    /**
     * @param int $gender our gender to set, must be one of GENDER_MALE, GENDER_FEMALE or GENDER_UNKNOWN
     */
    public function setGender(int $gender): void
    {
        $this->setAsInteger('gender', $gender);
    }

    public function hasGender(): bool
    {
        return $this->hasInteger('gender');
    }

    /**
     * @return int our cancelation period in days, will be >= 0
     */
    public function getCancelationPeriod(): int
    {
        return $this->getAsInteger('cancelation_period');
    }

    /**
     * @param int $cancelationPeriod our cancelation period in days to set, must be >= 0
     */
    public function setCancelationPeriod(int $cancelationPeriod): void
    {
        if ($cancelationPeriod < 0) {
            throw new \InvalidArgumentException('The parameter $cancelationPeriod must be >= 0.', 1333297044);
        }

        $this->setAsInteger('cancelation_period', $cancelationPeriod);
    }

    public function hasCancelationPeriod(): bool
    {
        return $this->hasInteger('cancelation_period');
    }

    public function getOwner(): ?FrontEndUser
    {
        /** @var FrontEndUser|null $owner */
        $owner = $this->getAsModel('owner');

        return $owner;
    }

    public function setOwner(FrontEndUser $frontEndUser): void
    {
        $this->set('owner', $frontEndUser);
    }

    /**
     * @return string our notes, may be empty
     */
    public function getNotes(): string
    {
        return $this->getAsString('notes');
    }

    /**
     * @param string $notes our notes to set, might be empty
     */
    public function setNotes(string $notes): void
    {
        $this->setAsString('notes', $notes);
    }

    public function hasImage(): bool
    {
        return $this->getAsInteger('image') > 0;
    }

    public function getImage(): ?FileReference
    {
        if (!$this->hasImage()) {
            return null;
        }

        $images = $this->getFileRepository()->findByRelation('tx_seminars_speakers', 'image', $this->getUid());

        return \array_shift($images);
    }

    private function getFileRepository(): FileRepository
    {
        /** @var FileRepository $fileRepository */
        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);

        return $fileRepository;
    }
}
