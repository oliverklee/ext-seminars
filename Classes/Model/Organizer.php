<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Model;

use OliverKlee\Oelib\Interfaces\MailRole;
use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Seminars\Model\Interfaces\Titled;

/**
 * This class represents an organizer.
 */
class Organizer extends AbstractModel implements MailRole, Titled
{
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
            throw new \InvalidArgumentException('The parameter $name must not be empty.', 1333296852);
        }

        $this->setAsString('title', $name);
    }

    /**
     * @return string our homepage, may be empty
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
     * @return string our e-mail address, will not be empty
     */
    public function getEmailAddress(): string
    {
        return $this->getAsString('email');
    }

    /**
     * @param string $eMailAddress our e-mail address, must not be empty
     */
    public function setEmailAddress(string $eMailAddress): void
    {
        if ($eMailAddress == '') {
            throw new \InvalidArgumentException('The parameter $eMailAddress must not be empty.', 1333296861);
        }

        $this->setAsString('email', $eMailAddress);
    }

    /**
     * @return string our e-mail footer, may be empty
     */
    public function getEmailFooter(): string
    {
        return $this->getAsString('email_footer');
    }

    /**
     * @param string $eMailFooter our e-mail footer, may be empty
     */
    public function setEmailFooter(string $eMailFooter): void
    {
        $this->setAsString('email_footer', $eMailFooter);
    }

    public function hasEmailFooter(): bool
    {
        return $this->hasString('email_footer');
    }

    /**
     * @return int our attendances PID, will be >= 0
     */
    public function getAttendancesPID(): int
    {
        return $this->getAsInteger('attendances_pid');
    }

    /**
     * @param int $attendancesPID our attendances PID, must be >= 0
     */
    public function setAttendancesPID(int $attendancesPID): void
    {
        if ($attendancesPID < 0) {
            throw new \InvalidArgumentException('The parameter $attendancesPID must not be < 0.', 1333296869);
        }

        $this->setAsInteger('attendances_pid', $attendancesPID);
    }

    public function hasAttendancesPID(): bool
    {
        return $this->hasInteger('attendances_pid');
    }

    public function hasDescription(): bool
    {
        return $this->hasString('description');
    }

    /**
     * @return string the description of the organizer in raw format, will be empty if organizer has no description
     */
    public function getDescription(): string
    {
        return $this->getAsString('description');
    }

    /**
     * @return string our name, will not be empty
     */
    public function getTitle(): string
    {
        return $this->getName();
    }
}
