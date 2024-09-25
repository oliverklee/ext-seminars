<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\OldModel;

use OliverKlee\Oelib\Interfaces\MailRole;

/**
 * This class represents an organizer.
 */
class LegacyOrganizer extends AbstractModel implements MailRole
{
    /**
     * @var string the name of the SQL table this class corresponds to
     */
    protected static string $tableName = 'tx_seminars_organizers';

    /**
     * Gets the organizer's real name.
     *
     * @return string the organizer's real name, will not be empty for valid records
     */
    public function getName(): string
    {
        return $this->getTitle();
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
     * Returns TRUE if this organizer has a homepage set, FALSE otherwise.
     *
     * @return bool TRUE if this organizer has a homepage set, FALSE
     *                 otherwise
     */
    public function hasHomepage(): bool
    {
        return $this->hasRecordPropertyString('homepage');
    }

    /**
     * Gets the organizer's email address.
     *
     * @return string the organizer's email address, will only be empty if
     *                there is an error
     */
    public function getEmailAddress(): string
    {
        return $this->getRecordPropertyString('email');
    }

    /**
     * Gets our email footer.
     *
     * @return string our email footer (or '' if there is an error)
     */
    public function getEmailFooter(): string
    {
        return $this->getRecordPropertyString('email_footer');
    }

    /**
     * Checks whether this organizer has a description.
     *
     * @return bool TRUE if this organizer has a description, FALSE otherwise
     */
    public function hasDescription(): bool
    {
        return $this->hasRecordPropertyString('description');
    }

    /**
     * Returns the description of the organizer.
     *
     * @return string the description of the organizer in raw format, will be
     *                empty if organizer has no description
     */
    public function getDescription(): string
    {
        return $this->getRecordPropertyString('description');
    }
}
