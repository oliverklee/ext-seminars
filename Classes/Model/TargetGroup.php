<?php

declare(strict_types=1);

use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Seminars\Model\Interfaces\Titled;

/**
 * This class represents a target group.
 */
class Tx_Seminars_Model_TargetGroup extends AbstractModel implements Titled
{
    /**
     * @return string our title, will not be empty
     */
    public function getTitle(): string
    {
        return $this->getAsString('title');
    }

    /**
     * @param string $title our title to set, must not be empty
     */
    public function setTitle(string $title): void
    {
        if ($title == '') {
            throw new \InvalidArgumentException('The parameter $title must not be empty.', 1333297060);
        }

        $this->setAsString('title', $title);
    }

    public function getOwner(): ?\Tx_Seminars_Model_FrontEndUser
    {
        /** @var \Tx_Seminars_Model_FrontEndUser|null $owner */
        $owner = $this->getAsModel('owner');

        return $owner;
    }

    public function setOwner(\Tx_Seminars_Model_FrontEndUser $frontEndUser): void
    {
        $this->set('owner', $frontEndUser);
    }

    /**
     * @return int this target group's minimum age, will be >= 0; will be 0 if no minimum age has been set
     */
    public function getMinimumAge(): int
    {
        return $this->getAsInteger('minimum_age');
    }

    /**
     * @param int $minimumAge this target group's minimum age, must be >= 0; set to 0 to unset the minimum age
     */
    public function setMinimumAge(int $minimumAge): void
    {
        $this->setAsInteger('minimum_age', $minimumAge);
    }

    /**
     * @return int this target group's maximum age, will be >= 0; will be 0 if no maximum age has been set
     */
    public function getMaximumAge(): int
    {
        return $this->getAsInteger('maximum_age');
    }

    /**
     * @param int $maximumAge this target group's maximum age, must be >= 0; set to 0 to unset the maximum age
     */
    public function setMaximumAge(int $maximumAge): void
    {
        $this->setAsInteger('maximum_age', $maximumAge);
    }
}
