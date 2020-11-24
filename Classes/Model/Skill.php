<?php

declare(strict_types=1);

/**
 * This class represents a skill.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Model_Skill extends \Tx_Oelib_Model implements \Tx_Seminars_Interface_Titled
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
            throw new \InvalidArgumentException('The parameter $title must not be empty.', 1333297023);
        }

        $this->setAsString('title', $title);
    }
}
