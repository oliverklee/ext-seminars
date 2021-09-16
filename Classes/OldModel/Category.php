<?php

declare(strict_types=1);

use OliverKlee\Seminars\OldModel\AbstractModel;

/**
 * This class represents an event category.
 */
class Tx_Seminars_OldModel_Category extends AbstractModel
{
    /**
     * @var string the name of the SQL table this class corresponds to
     */
    protected static $tableName = 'tx_seminars_categories';

    /**
     * @var bool whether to call `TemplateHelper::init()` during construction
     */
    protected $needsTemplateHelperInitialization = false;

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
