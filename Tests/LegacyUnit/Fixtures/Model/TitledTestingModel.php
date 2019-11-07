<?php
declare(strict_types = 1);

/**
 * This class represents a titled model for testing purposes.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_Fixtures_Model_TitledTestingModel extends \Tx_Oelib_Model implements \Tx_Seminars_Interface_Titled
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
}
