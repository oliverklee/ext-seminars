<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Model;

use OliverKlee\Oelib\Interfaces\MailRole;
use OliverKlee\Oelib\Model\AbstractModel;

/**
 * This class represents a speaker.
 */
class Speaker extends AbstractModel implements MailRole
{
    /**
     * @return string our name, will not be empty
     */
    public function getName(): string
    {
        return $this->getAsString('title');
    }

    /**
     * @return string our e-mail address, will not be empty
     */
    public function getEmailAddress(): string
    {
        return $this->getAsString('email');
    }
}
