<?php

declare(strict_types=1);

use OliverKlee\Oelib\Interfaces\MailRole;
use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Seminars\Model\Interfaces\Titled;

/**
 * This class represents an organizer.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Model_Organizer extends AbstractModel implements MailRole, Titled
{
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
            throw new \InvalidArgumentException('The parameter $name must not be empty.', 1333296852);
        }

        $this->setAsString('title', $name);
    }

    /**
     * Returns our homepage.
     *
     * @return string our homepage, may be empty
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
     * Returns whether this organizer has a homepage.
     *
     * @return bool TRUE if this organizer has a homepage, FALSE otherwise
     */
    public function hasHomepage(): bool
    {
        return $this->hasString('homepage');
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
     * @param string $eMailAddress our e-mail address, must not be empty
     *
     * @return void
     */
    public function setEMailAddress(string $eMailAddress)
    {
        if ($eMailAddress == '') {
            throw new \InvalidArgumentException('The parameter $eMailAddress must not be empty.', 1333296861);
        }

        $this->setAsString('email', $eMailAddress);
    }

    /**
     * Returns our e-mail footer.
     *
     * @return string our e-mail footer, may be empty
     */
    public function getEMailFooter(): string
    {
        return $this->getAsString('email_footer');
    }

    /**
     * Sets our e-mail footer.
     *
     * @param string $eMailFooter our e-mail footer, may be empty
     *
     * @return void
     */
    public function setEMailFooter(string $eMailFooter)
    {
        $this->setAsString('email_footer', $eMailFooter);
    }

    /**
     * Returns whether this organizer has an e-mail footer.
     *
     * @return bool TRUE if this organizer has an e-mail footer, FALSE otherwise
     */
    public function hasEMailFooter(): bool
    {
        return $this->hasString('email_footer');
    }

    /**
     * Returns our attendances PID.
     *
     * @return int our attendances PID, will be >= 0
     */
    public function getAttendancesPID(): int
    {
        return $this->getAsInteger('attendances_pid');
    }

    /**
     * Sets our attendances PID.
     *
     * @param int $attendancesPID our attendances PID, must be >= 0
     *
     * @return void
     */
    public function setAttendancesPID(int $attendancesPID)
    {
        if ($attendancesPID < 0) {
            throw new \InvalidArgumentException('The parameter $attendancesPID must not be < 0.', 1333296869);
        }

        $this->setAsInteger('attendances_pid', $attendancesPID);
    }

    /**
     * Returns whether this organizer has an attendances PID.
     *
     * @return bool TRUE if this organizer has an attendances PID, FALSE otherwise
     */
    public function hasAttendancesPID(): bool
    {
        return $this->hasInteger('attendances_pid');
    }

    /**
     * Checks whether this organizer has a description.
     *
     * @return bool TRUE if this organizer has a description, FALSE otherwise
     */
    public function hasDescription(): bool
    {
        return $this->hasString('description');
    }

    /**
     * Returns the description of the organizer.
     *
     * @return string the description of the organizer in raw format, will be
     *                empty if organizer has no description
     */
    public function getDescription(): string
    {
        return $this->getAsString('description');
    }

    /**
     * Returns our name.
     *
     * @return string our name, will not be empty
     */
    public function getTitle(): string
    {
        return $this->getName();
    }
}
