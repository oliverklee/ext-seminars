<?php

/**
 * This class represents a target group.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Model_TargetGroup extends Tx_Oelib_Model implements Tx_Seminars_Interface_Titled
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
            throw new InvalidArgumentException('The parameter $title must not be empty.', 1333297060);
        }

        $this->setAsString('title', $title);
    }

    /**
     * Returns our owner.
     *
     * @return Tx_Seminars_Model_FrontEndUser the owner of this model, will be null
     *                                     if this model has no owner
     */
    public function getOwner()
    {
        return $this->getAsModel('owner');
    }

    /**
     * Sets our owner.
     *
     * @param Tx_Seminars_Model_FrontEndUser $frontEndUser the owner of this model to set
     *
     * @return void
     */
    public function setOwner(Tx_Seminars_Model_FrontEndUser $frontEndUser)
    {
        $this->set('owner', $frontEndUser);
    }

    /**
     * Returns this target group's minimum age.
     *
     * @return int this target group's minimum age, will be >= 0; will be 0
     *                 if no minimum age has been set
     */
    public function getMinimumAge()
    {
        return $this->getAsInteger('minimum_age');
    }

    /**
     * Sets this target group's minimum age.
     *
     * @param int $minimumAge
     *        this target group's minimum age, must be >= 0; set to 0 to unset the minimum age
     *
     * @return void
     */
    public function setMinimumAge($minimumAge)
    {
        $this->setAsInteger('minimum_age', $minimumAge);
    }

    /**
     * Returns this target group's maximum age.
     *
     * @return int this target group's maximum age, will be >= 0; will be 0
     *                 if no maximum age has been set
     */
    public function getMaximumAge()
    {
        return $this->getAsInteger('maximum_age');
    }

    /**
     * Sets this target group's maximum age.
     *
     * @param int $maximumAge
     *        this target group's maximum age, must be >= 0; set to 0 to unset the maximum age
     *
     * @return void
     */
    public function setMaximumAge($maximumAge)
    {
        $this->setAsInteger('maximum_age', $maximumAge);
    }
}
