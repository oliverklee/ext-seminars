<?php

/**
 * This class represents a payment method.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Model_PaymentMethod extends Tx_Oelib_Model implements Tx_Seminars_Interface_Titled
{
    /**
     * Returns our title.
     *
     * @return string our title, will not be empty
     */
    public function getTitle()
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
    public function setTitle($title)
    {
        if ($title == '') {
            throw new InvalidArgumentException('The parameter $title must not be empty.', 1333296882);
        }

        $this->setAsString('title', $title);
    }

    /**
     * Returns our description.
     *
     * @return string our description, might be empty
     */
    public function getDescription()
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
    public function setDescription($description)
    {
        $this->setAsString('description', $description);
    }

    /**
     * Returns whether this payment method has a description.
     *
     * @return bool TRUE if this payment method has a description, FALSE
     *                 otherwise
     */
    public function hasDescription()
    {
        return $this->hasString('description');
    }
}
