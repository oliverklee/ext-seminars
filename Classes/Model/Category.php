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
     * @return string our title, will not be empty
     */
    public function getTitle(): string
    {
        return $this->getAsString('title');
    }

    /**
     * @return string the file name of the icon (relative to the extension
     *                upload path) of the category, will be empty if the
     *                category has no icon
     */
    public function getIcon(): string
    {
        return $this->getAsString('icon');
    }

    /**
     * @param string $icon the file name of the icon (relative to the extension upload path) of the category,
     *        may be empty
     */
    public function setIcon(string $icon): void
    {
        $this->setAsString('icon', $icon);
    }

    public function hasIcon(): bool
    {
        return $this->hasString('icon');
    }

    /**
     * @return int the single view page, will be 0 if none has been set
     */
    public function getSingleViewPageUid(): int
    {
        return $this->getAsInteger('single_view_page');
    }

    public function hasSingleViewPageUid(): bool
    {
        return $this->hasInteger('single_view_page');
    }
}
