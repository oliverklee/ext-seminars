<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Model;

use OliverKlee\Oelib\Model\AbstractModel;

/**
 * This class represents a category.
 */
class Category extends AbstractModel
{
    /**
     * @return int<0, max> the single view page, will be 0 if none has been set
     */
    public function getSingleViewPageUid(): int
    {
        return $this->getAsNonNegativeInteger('single_view_page');
    }

    public function hasSingleViewPageUid(): bool
    {
        return $this->hasInteger('single_view_page');
    }
}
