<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\Model;

/**
 * This class represents a titled model for testing purposes.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class TitledTestingModel extends \Tx_Oelib_Model implements \Tx_Seminars_Interface_Titled
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
}
