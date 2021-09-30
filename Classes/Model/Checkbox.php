<?php

declare(strict_types=1);

use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Seminars\Model\FrontEndUser;
use OliverKlee\Seminars\Model\Interfaces\Titled;

/**
 * This class represents a checkbox.
 */
class Tx_Seminars_Model_Checkbox extends AbstractModel implements Titled
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
            throw new \InvalidArgumentException('The parameter $title must not be empty.', 1333296129);
        }

        $this->setAsString('title', $title);
    }

    /**
     * @return string our description, might be empty
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
}
