<?php

declare(strict_types=1);

use OliverKlee\Seminars\Model\Interfaces\Titled;

/**
 * This class represents a lodging.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Model_Lodging extends \Tx_Oelib_Model implements Titled
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
            throw new \InvalidArgumentException('The parameter $title must not be empty.', 1333296839);
        }

        $this->setAsString('title', $title);
    }
}
