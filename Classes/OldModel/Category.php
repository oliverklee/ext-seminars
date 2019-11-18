<?php

declare(strict_types=1);

/**
 * This class represents an event category.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_OldModel_Category extends \Tx_Seminars_OldModel_Abstract
{
    /**
     * @var string the name of the SQL table this class corresponds to
     */
    protected $tableName = 'tx_seminars_categories';

    /**
     * Returns the icon of this category.
     *
     * @return string the file name of the icon (relative to the extension
     *                upload path) of the category, will be empty if the
     *                category has no icon
     */
    public function getIcon(): string
    {
        return $this->getRecordPropertyString('icon');
    }
}
