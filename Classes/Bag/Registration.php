<?php

declare(strict_types=1);

use OliverKlee\Seminars\Bag\AbstractBag;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * This aggregate class holds a bunch of registration objects and allows to iterate over them.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Bag_Registration extends AbstractBag
{
    /**
     * @var string the name of the main DB table from which we get the records for this bag
     */
    protected $tableName = 'tx_seminars_attendances';

    /**
     * @var ContentObjectRenderer
     */
    private $contentObject = null;

    /**
     * Creates the current item in $this->currentItem, using $this->dbResult
     * as a source. If the current item cannot be created, $this->currentItem
     * will be nulled out.
     *
     * $this->dbResult is ensured to be not FALSE when this function is called.
     *
     * @return void
     */
    protected function createItemFromDbResult()
    {
        $this->currentItem = GeneralUtility::makeInstance(
            \Tx_Seminars_OldModel_Registration::class,
            0,
            $this->dbResult
        );
        $this->currentItem->setContentObject($this->getContentObject());
        $this->valid();
    }

    protected function getContentObject(): ContentObjectRenderer
    {
        if ($this->contentObject === null) {
            $this->contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        }

        return $this->contentObject;
    }
}
