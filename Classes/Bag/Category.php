<?php

declare(strict_types=1);

use OliverKlee\Seminars\Bag\AbstractBag;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This aggregate class holds a bunch of category objects and allows to iterate over them.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Bag_Category extends AbstractBag
{
    /**
     * @var string the name of the main DB table from which we get the records for this bag
     */
    protected $tableName = 'tx_seminars_categories';

    /**
     * Creates the current item in $this->currentItem, using $this->dbResult
     * as a source. If the current item cannot be created, $this->currentItem
     * will be nulled out.
     *
     * $this->dbResult must be ensured to be non-NULL when this function is
     * called.
     *
     * @return void
     */
    protected function createItemFromDbResult()
    {
        $this->currentItem = GeneralUtility::makeInstance(
            \Tx_Seminars_OldModel_Category::class,
            0,
            $this->dbResult
        );
        $this->valid();
    }
}
