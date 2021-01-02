<?php

declare(strict_types=1);

use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Seminars\Model\Interfaces\Titled;

/**
 * This class represents a checkbox.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Model_Checkbox extends AbstractModel implements Titled
{
    /**
     * Returns our title.
     *
     * @return string our title, will not be empty
     */
    public function getTitle(): string
    {
        return $this->getAsString('title');
    }

    /**
     * Sets our title.
     *
     * @param string $title our title to set, must not be empty
     *
     * @return void
     */
    public function setTitle(string $title)
    {
        if ($title == '') {
            throw new \InvalidArgumentException('The parameter $title must not be empty.', 1333296129);
        }

        $this->setAsString('title', $title);
    }

    /**
     * Returns our description.
     *
     * @return string our description, might be empty
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
     * Returns whether this payment method has a description.
     *
     * @return bool TRUE if this payment method has a description, FALSE
     *                 otherwise
     */
    public function hasDescription(): bool
    {
        return $this->hasString('description');
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
}
