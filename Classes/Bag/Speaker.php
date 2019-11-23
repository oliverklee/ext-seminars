<?php

declare(strict_types=1);

use OliverKlee\Seminars\Bag\AbstractBag;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This aggregate class holds a bunch of speaker objects and allows to iterate over them.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Bag_Speaker extends AbstractBag
{
    /**
     * @var string the name of the main DB table from which we get the records for this bag
     */
    protected $tableName = 'tx_seminars_speakers';

    /**
     * Creates the current item in $this->currentItem, using $this->dbResult
     * as a source. If the current item cannot be created, $this->currentItem
     * will be nulled out.
     *
     * $this->dbResult must be ensured to be not FALSE when this function is
     * called.
     *
     * @return void
     */
    protected function createItemFromDbResult()
    {
        $this->currentItem = GeneralUtility::makeInstance(
            \Tx_Seminars_OldModel_Speaker::class,
            0,
            $this->dbResult
        );
        $this->valid();
    }
}
