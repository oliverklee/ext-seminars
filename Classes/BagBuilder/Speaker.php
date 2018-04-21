<?php

/**
 * This builder class creates customized speaker bag objects.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_BagBuilder_Speaker extends Tx_Seminars_BagBuilder_Abstract
{
    /**
     * @var string class name of the bag class that will be built
     */
    protected $bagClassName = Tx_Seminars_Bag_Speaker::class;

    /**
     * @var string the table name of the bag to build
     */
    protected $tableName = 'tx_seminars_speakers';
}
