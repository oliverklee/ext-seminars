<?php

declare(strict_types=1);

use OliverKlee\Oelib\Templating\TemplateHelper;
use OliverKlee\Seminars\OldModel\AbstractModel;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Plugin\AbstractPlugin;

/**
 * This class represents a speaker.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_OldModel_Speaker extends AbstractModel
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
     * @var string the name of the SQL table this class corresponds to
     */
    protected static $tableName = 'tx_seminars_speakers';

    /**
     * Gets our organization.
     *
     * @return string our organization (or '' if there is an error)
     */
    public function getOrganization(): string
    {
        return $this->getRecordPropertyString('organization');
    }

    /**
     * Returns TRUE if this speaker has an organization, FALSE otherwise.
     *
     * @return bool TRUE if this speaker has an organization, FALSE otherwise
     */
    public function hasOrganization(): bool
    {
        return $this->hasRecordPropertyString('organization');
    }

    /**
     * Gets our homepage.
     *
     * @return string our homepage (or '' if there is an error)
     */
    public function getHomepage(): string
    {
        return $this->getRecordPropertyString('homepage');
    }

    /**
     * Returns TRUE if this speaker has a homepage, FALSE otherwise.
     *
     * @return bool TRUE if this speaker has a homepage, FALSE otherwise
     */
    public function hasHomepage(): bool
    {
        return $this->hasRecordPropertyString('homepage');
    }

    /**
     * Gets our description.
     *
     * @param AbstractPlugin $plugin
     *
     * @return string our description (or '' if there is an error)
     */
    public function getDescription(AbstractPlugin $plugin): string
    {
        return $plugin->pi_RTEcssText(
            $this->getRecordPropertyString('description')
        );
    }

    /**
     * Gets our description without RTE processing.
     *
     * @return string our description (or '' if there is an error)
     */
    public function getDescriptionRaw(): string
    {
        return $this->getRecordPropertyString('description');
    }

    /**
     * Returns TRUE if this speaker has a description, FALSE otherwise.
     *
     * @return bool TRUE if this speaker has a description, FALSE otherwise
     */
    public function hasDescription(): bool
    {
        return $this->hasRecordPropertyString('description');
    }

    /**
     * Checks whether we have any skills set.
     *
     * @return bool TRUE if we have any skills related to this speaker,
     *                 FALSE otherwise
     */
    public function hasSkills(): bool
    {
        return $this->hasRecordPropertyInteger('skills');
    }

    /**
     * Gets our skills as a plain text list (just the skill names).
     *
     * @return string our skills list (or an empty string if there are no skills for this speaker)
     */
    public function getSkillsShort(): string
    {
        if (!$this->hasSkills()) {
            return '';
        }

        return \implode(', ', $this->getMmRecordTitles('tx_seminars_skills', 'tx_seminars_speakers_skills_mm'));
    }

    /**
     * Gets the number of skills associated with this speaker.
     *
     * @return int the number of skills associated with this speaker,
     *                 will be >= 0
     */
    public function getNumberOfSkills(): int
    {
        return $this->getRecordPropertyInteger('skills');
    }

    /**
     * Gets our internal notes.
     *
     * @return string our internal notes (or '' if there is an error)
     */
    public function getNotes(): string
    {
        return $this->getRecordPropertyString('notes');
    }

    /**
     * Gets our address.
     *
     * @return string our address (or '' if there is an error)
     */
    public function getAddress(): string
    {
        return $this->getRecordPropertyString('address');
    }

    /**
     * Gets our work phone number.
     *
     * @return string our work phone number (or '' if there is an error)
     */
    public function getPhoneWork(): string
    {
        return $this->getRecordPropertyString('phone_work');
    }

    /**
     * Gets our home phone number.
     *
     * @return string our home phone number (or '' if there is an error)
     */
    public function getPhoneHome(): string
    {
        return $this->getRecordPropertyString('phone_home');
    }

    /**
     * Gets our mobile phone number.
     *
     * @return string our mobile phone number (or '' if there is an error)
     */
    public function getPhoneMobile(): string
    {
        return $this->getRecordPropertyString('phone_mobile');
    }

    /**
     * Gets our fax number.
     *
     * @return string our fax number (or '' if there is an error)
     */
    public function getFax(): string
    {
        return $this->getRecordPropertyString('fax');
    }

    /**
     * Gets our e-mail address.
     *
     * @return string our e-mail address (or '' if there is an error)
     */
    public function getEmail(): string
    {
        return $this->getRecordPropertyString('email');
    }

    /**
     * Creates a link to this speaker's homepage, with the title as link text.
     *
     * @param TemplateHelper $plugin object with current configuration values
     *
     * @return string this speaker's title wrapped in an link tag, or if the
     *                speaker has no homepage just the speaker name, will not
     *                be empty
     */
    public function getLinkedTitle(TemplateHelper $plugin): string
    {
        $safeTitle = \htmlspecialchars($this->getTitle(), ENT_QUOTES | ENT_HTML5);

        if ($this->hasHomepage()) {
            $result = $plugin->cObj->getTypoLink(
                $safeTitle,
                $this->getHomepage(),
                [],
                $plugin->getConfValueString('externalLinkTarget')
            );
        } else {
            $result = $safeTitle;
        }

        return $result;
    }

    /**
     * Returns the gender of this speaker.
     *
     * @return int the gender of the speaker, will be either
     *                 GENDER_MALE,
     *                 GENDER_FEMALE or
     *                 GENDER_UNKNOWN if the speaker has no gender
     */
    public function getGender(): int
    {
        return $this->getRecordPropertyInteger('gender');
    }

    /**
     * Sets the gender of this speaker.
     *
     * @param int $gender
     *        the gender of the speaker, must be one of \Tx_Seminars_OldModel_Speaker::GENDER_FEMALE, \Tx_Seminars_OldModel_Speaker::GENDER_MALE
     *        or \Tx_Seminars_OldModel_Speaker::GENDER_UNKNOWN
     *
     * @return void
     */
    public function setGender(int $gender)
    {
        $this->setRecordPropertyInteger('gender', $gender);
    }

    /**
     * Returns TRUE if this speaker has a cancelation period.
     *
     * @return bool TRUE if the speaker has a cancelation period, FALSE
     *                 otherwise
     */
    public function hasCancelationPeriod(): bool
    {
        return $this->hasRecordPropertyInteger('cancelation_period');
    }

    /**
     * Returns the cancelation period of this speaker in days.
     *
     * @return int the cancelation period in days, will be >= 0
     */
    public function getCancelationPeriodInDays(): int
    {
        return $this->getRecordPropertyInteger('cancelation_period');
    }

    /**
     * Sets the gender cancelation period of this speaker
     *
     * @param int $cancelationPeriod the cancelation period of this speaker in days, must be > 0
     *
     * @return void
     */
    public function setCancelationPeriod(int $cancelationPeriod)
    {
        $this->setRecordPropertyInteger('cancelation_period', $cancelationPeriod);
    }

    /**
     * Returns our owner.
     *
     * @return \Tx_Seminars_Model_FrontEndUser|null
     */
    public function getOwner()
    {
        if (!$this->hasRecordPropertyInteger('owner')) {
            return null;
        }

        /** @var \Tx_Seminars_Mapper_FrontEndUser $mapper */
        $mapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_FrontEndUser::class);
        /** @var \Tx_Seminars_Model_FrontEndUser|null $owner */
        $owner = $mapper->find($this->getRecordPropertyInteger('owner'));

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
        $this->setRecordPropertyInteger('owner', $frontEndUser->getUid());
    }

    /**
     * Returns TRUE if the speaker is hidden, otherwise FALSE.
     *
     * @return bool TRUE if the speaker is hidden, FALSE otherwise
     */
    public function isHidden(): bool
    {
        return $this->getRecordPropertyBoolean('hidden');
    }

    /**
     * @return bool
     */
    public function hasImage(): bool
    {
        return $this->getRecordPropertyInteger('image') > 0;
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
