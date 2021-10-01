<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\OldModel;

/**
 * This class represents an event category.
 */
class LegacyCategory extends AbstractModel
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
