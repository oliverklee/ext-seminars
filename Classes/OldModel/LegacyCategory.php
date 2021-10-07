<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\OldModel;

use TYPO3\CMS\Core\Resource\FileReference;

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

    public function hasIcon(): bool
    {
        return $this->hasRecordPropertyInteger('icon');
    }

    public function getIcon(): ?FileReference
    {
        if (!$this->hasIcon()) {
            return null;
        }

        $images = $this->getFileRepository()->findByRelation('tx_seminars_categories', 'icon', $this->getUid());

        return \array_shift($images);
    }
}
