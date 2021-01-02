<?php

declare(strict_types=1);

use OliverKlee\Oelib\Model\AbstractModel;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class represents an speaker.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Model_Speaker extends AbstractModel implements \Tx_Oelib_Interface_MailRole
{
    /**
     * @var int the gender type for speakers without gender
     */
    const GENDER_UNKNOWN = 0;

    /**
     * @var int the gender type male for a speaker
     */
    const GENDER_MALE = 1;

    /**
     * @var int the gender type female for a speaker
     */
    const GENDER_FEMALE = 2;

    /**
     * Returns our name.
     *
     * @return string our name, will not be empty
     */
    public function getName(): string
    {
        return $this->getAsString('title');
    }

    /**
     * Sets our name.
     *
     * @param string $name our name to set, must not be empty
     *
     * @return void
     */
    public function setName(string $name)
    {
        if ($name == '') {
            throw new \InvalidArgumentException('The parameter $name must not be empty.', 1333297036);
        }

        $this->setAsString('title', $name);
    }

    /**
     * Returns our organization.
     *
     * @return string our organization, will be empty if there's no organization
     *                set
     */
    public function getOrganization(): string
    {
        return $this->getAsString('organization');
    }

    /**
     * Sets our organization.
     *
     * @param string $organization our organization, may be empty
     *
     * @return void
     */
    public function setOrganization(string $organization)
    {
        $this->setAsString('organization', $organization);
    }

    /**
     * Returns whether this speaker has an organization.
     *
     * @return bool TRUE if this speaker has an organization, FALSE otherwise
     */
    public function hasOrganization(): bool
    {
        return $this->hasString('organization');
    }

    /**
     * Returns our homepage.
     *
     * @return string our homepage, will be empty if there's no homepage set
     */
    public function getHomepage(): string
    {
        return $this->getAsString('homepage');
    }

    /**
     * Sets our homepage.
     *
     * @param string $homepage our homepage, may be empty
     *
     * @return void
     */
    public function setHomepage(string $homepage)
    {
        $this->setAsString('homepage', $homepage);
    }

    /**
     * Returns whether this speaker has a homepage.
     *
     * @return bool TRUE if this speaker has a homepage, FALSE otherwise
     */
    public function hasHomepage(): bool
    {
        return $this->hasString('homepage');
    }

    /**
     * Returns our description.
     *
     * @return string our description, will be empty if there's no description
     *                set
     */
    public function getDescription(): string
    {
        return $this->getAsString('description');
    }

    /**
     * Sets our description.
     *
     * @param string $description our description to set, may be empty
     *
     * @return void
     */
    public function setDescription(string $description)
    {
        $this->setAsString('description', $description);
    }

    /**
     * Returns whether this speaker has a description.
     *
     * @return bool TRUE if this speaker has a description, FALSE otherwise
     */
    public function hasDescription(): bool
    {
        return $this->hasString('description');
    }

    /**
     * Returns our skills.
     *
     * @return \Tx_Oelib_List our skills, will be empty if there are no skills
     *                       related to this speaker
     */
    public function getSkills(): \Tx_Oelib_List
    {
        return $this->getAsList('skills');
    }

    /**
     * Sets this speaker's skills.
     *
     * @param \Tx_Oelib_List $skills this speaker's skills, may be empty
     *
     * @return void
     */
    public function setSkills(\Tx_Oelib_List $skills)
    {
        $this->set('skills', $skills);
    }

    /**
     * Returns our address.
     *
     * @return string our address, will be empty if there's no address set
     */
    public function getAddress(): string
    {
        return $this->getAsString('address');
    }

    /**
     * Sets our address.
     *
     * @param string $address our address to set, may be empty
     *
     * @return void
     */
    public function setAddress(string $address)
    {
        $this->setAsString('address', $address);
    }

    /**
     * Returns whether this place has an address.
     *
     * @return bool TRUE if this address has an address, FALSE otherwise
     */
    public function hasAddress(): bool
    {
        return $this->hasString('address');
    }

    /**
     * Returns our work telephone number.
     *
     * @return string our work telephone number, will be empty if there's no
     *                work telephone number set
     */
    public function getPhoneWork(): string
    {
        return $this->getAsString('phone_work');
    }

    /**
     * Sets our work telephone number.
     *
     * @param string $phoneWork our work telephone number to set, may be empty
     *
     * @return void
     */
    public function setPhoneWork(string $phoneWork)
    {
        $this->setAsString('phone_work', $phoneWork);
    }

    /**
     * Returns whether this speaker has a work telephone number.
     *
     * @return bool TRUE if this speaker has a work telephone number, FALSE
     *                 otherwise
     */
    public function hasPhoneWork(): bool
    {
        return $this->hasString('phone_work');
    }

    /**
     * Returns our home telephone number.
     *
     * @return string our home telephone number, will be empty if there's no
     *                home telephone number set
     */
    public function getPhoneHome(): string
    {
        return $this->getAsString('phone_home');
    }

    /**
     * Sets our home telephone number.
     *
     * @param string $phoneHome our home telephone number to set, may be empty
     *
     * @return void
     */
    public function setPhoneHome(string $phoneHome)
    {
        $this->setAsString('phone_home', $phoneHome);
    }

    /**
     * Returns whether this speaker has a home telephone number.
     *
     * @return bool TRUE if this speaker has a home telephone number, FALSE
     *                 otherwise
     */
    public function hasPhoneHome(): bool
    {
        return $this->hasString('phone_home');
    }

    /**
     * Returns our mobile telephone number.
     *
     * @return string our mobile telephone number, will be empty if there's no
     *                mobile telephone number set
     */
    public function getPhoneMobile(): string
    {
        return $this->getAsString('phone_mobile');
    }

    /**
     * Sets our mobile telephone number.
     *
     * @param string $phoneMobile our mobile telephone number to set, may be empty
     *
     * @return void
     */
    public function setPhoneMobile(string $phoneMobile)
    {
        $this->setAsString('phone_mobile', $phoneMobile);
    }

    /**
     * Returns whether this speaker has a mobile telephone number.
     *
     * @return bool TRUE if this speaker has a mobile telephone number, FALSE
     *                 otherwise
     */
    public function hasPhoneMobile(): bool
    {
        return $this->hasString('phone_mobile');
    }

    /**
     * Returns our fax number.
     *
     * @return string our fax number, will be empty if there's no fax number set
     */
    public function getFax(): string
    {
        return $this->getAsString('fax');
    }

    /**
     * Sets our fax number.
     *
     * @param string $fax our fax number to set, may be empty
     *
     * @return void
     */
    public function setFax(string $fax)
    {
        $this->setAsString('fax', $fax);
    }

    /**
     * Returns whether this speaker has a fax number.
     *
     * @return bool TRUE if this speaker has a fax number, FALSE otherwise
     */
    public function hasFax(): bool
    {
        return $this->hasString('fax');
    }

    /**
     * Returns our e-mail address.
     *
     * @return string our e-mail address, will not be empty
     */
    public function getEMailAddress(): string
    {
        return $this->getAsString('email');
    }

    /**
     * Sets out e-mail address.
     *
     * @param string $eMailAddress our e-mail address, may be empty
     *
     * @return void
     */
    public function setEMailAddress(string $eMailAddress)
    {
        $this->setAsString('email', $eMailAddress);
    }

    /**
     * Returns whether this speaker has an e-mail address.
     *
     * @return bool TRUE if this speaker has an e-mail address, FALSE
     *                 otherwise
     */
    public function hasEMailAddress(): bool
    {
        return $this->hasString('email');
    }

    /**
     * Returns our gender.
     *
     * @return int our gender, will be either GENDER_MALE, GENDER_FEMALE or
     *                 GENDER_UNKNOWN if the speaker has no gender
     */
    public function getGender(): int
    {
        return $this->getAsInteger('gender');
    }

    /**
     * Sets our gender.
     *
     * @param int $gender
     *        our gender to set, must be one of \Tx_Seminars_Model_Speaker::GENDER_FEMALE, \Tx_Seminars_Model_Speaker::GENDER_MALE
     *        or \Tx_Seminars_Model_Speaker::GENDER_UNKNOWN
     *
     * @return void
     */
    public function setGender(int $gender)
    {
        $this->setAsInteger('gender', $gender);
    }

    /**
     * Returns whether this speaker has a gender.
     *
     * @return bool TRUE if this speaker has a gender, FALSE otherwise
     */
    public function hasGender(): bool
    {
        return $this->hasInteger('gender');
    }

    /**
     * Returns our cancelation period in days.
     *
     * @return int our cancelation period in days, will be >= 0
     */
    public function getCancelationPeriod(): int
    {
        return $this->getAsInteger('cancelation_period');
    }

    /**
     * Sets our cancelation period in days.
     *
     * @param int $cancelationPeriod our cancelation period in days to set, must be >= 0
     *
     * @return void
     */
    public function setCancelationPeriod(int $cancelationPeriod)
    {
        if ($cancelationPeriod < 0) {
            throw new \InvalidArgumentException('The parameter $cancelationPeriod must be >= 0.', 1333297044);
        }

        $this->setAsInteger('cancelation_period', $cancelationPeriod);
    }

    /**
     * Returns whether this speaker has a cancelation period set.
     *
     * @return bool TRUE if this speaker has a cancelation period set, FALSE
     *                 otherwise
     */
    public function hasCancelationPeriod(): bool
    {
        return $this->hasInteger('cancelation_period');
    }

    /**
     * Returns our owner.
     *
     * @return \Tx_Seminars_Model_FrontEndUser|null
     */
    public function getOwner()
    {
        /** @var \Tx_Seminars_Model_FrontEndUser|null $owner */
        $owner = $this->getAsModel('owner');

        return $owner;
    }

    /**
     * Sets our owner.
     *
     * @param \Tx_Seminars_Model_FrontEndUser $frontEndUser the owner of this model to set
     *
     * @return void
     */
    public function setOwner(\Tx_Seminars_Model_FrontEndUser $frontEndUser)
    {
        $this->set('owner', $frontEndUser);
    }

    /**
     * Returns our notes.
     *
     * @return string our notes, may be empty
     */
    public function getNotes(): string
    {
        return $this->getAsString('notes');
    }

    /**
     * Sets our notes.
     *
     * @param string $notes our notes to set, might be empty
     *
     * @return void
     */
    public function setNotes(string $notes)
    {
        $this->setAsString('notes', $notes);
    }

    /**
     * @return bool
     */
    public function hasImage(): bool
    {
        return $this->getAsInteger('image') > 0;
    }

    /**
     * @return FileReference|null
     */
    public function getImage()
    {
        if (!$this->hasImage()) {
            return null;
        }

        $images = $this->getFileRepository()->findByRelation('tx_seminars_speakers', 'image', $this->getUid());

        return \array_shift($images);
    }

    /**
     * @return FileRepository
     */
    private function getFileRepository(): FileRepository
    {
        /** @var FileRepository $fileRepository */
        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);

        return $fileRepository;
    }
}
