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
     * The constructor. Creates a bag that contains test records and allows to iterate over them.
     *
     * @param string $queryParameters
     *        string that will be prepended to the WHERE clause using AND, e.g. 'pid=42'
     *        (the AND and the enclosing spaces are not necessary for this parameter)
     * @param string $additionalTableNames
     *        comma-separated names of additional DB tables used for JOINs, may be empty
     * @param string $groupBy
     *        GROUP BY clause (may be empty), must already be safeguarded against SQL injection
     * @param string $orderBy
     *        ORDER BY clause (may be empty), must already be safeguarded against SQL injection
     * @param string $limit
     *        LIMIT clause (may be empty), must already be safeguarded against SQL injection
     */
    public function __construct(
        $queryParameters = '1=1',
        $additionalTableNames = '',
        $groupBy = '',
        $orderBy = 'uid',
        $limit = ''
    ) {
        parent::__construct(
            'tx_seminars_categories',
            $queryParameters,
            $additionalTableNames,
            $groupBy,
            $orderBy,
            $limit
        );
    }

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
