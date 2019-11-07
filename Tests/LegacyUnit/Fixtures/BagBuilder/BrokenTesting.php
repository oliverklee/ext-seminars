<?php
declare(strict_types = 1);

/**
 * This builder class creates customized test bag objects.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_Fixtures_BagBuilder_BrokenTesting extends \Tx_Seminars_BagBuilder_Abstract
{
    /**
     * @var string class name of the bag class that will be built
     */
    protected $bagClassName = \Tx_Seminars_Tests_Unit_Fixtures_Bag_Testing::class;
}
