<?php

declare(strict_types=1);

use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Seminars\Model\Interfaces\Titled;

/**
 * This class represents a category.
 */
class Tx_Seminars_Model_Category extends AbstractModel implements Titled
{
    /**
     * Returns our title.
     *
     * @return string our title, will not be empty
     */
    public function getTitle(): string
    {
        return $this->getAsString('title');
    }

    /**
     * Sets our title.
     *
     * @param string $title our title to set, must not be empty
     *
     * @return void
     */
    public function setTitle(string $title)
    {
        if ($title == '') {
            throw new \InvalidArgumentException('The parameter $title must not be empty.', 1333296115);
        }

        $this->setAsString('title', $title);
    }

    /**
     * Returns the icon of this category.
     *
     * @return string the file name of the icon (relative to the extension
     *                upload path) of the category, will be empty if the
     *                category has no icon
     */
    public function getIcon(): string
    {
        return $this->getAsString('icon');
    }

    /**
     * Sets the icon of this category.
     *
     * @param string $icon the file name of the icon (relative to the extension upload path) of the category, may be empty
     *
     * @return void
     */
    public function setIcon(string $icon)
    {
        $this->setAsString('icon', $icon);
    }

    /**
     * Returns whether this category has an icon.
     *
     * @return bool TRUE if this category has an icon, FALSE otherwise
     */
    public function hasIcon(): bool
    {
        return $this->hasString('icon');
    }

    /**
     * Gets the UID of the single view page for events of this category.
     *
     * @return int the single view page, will be 0 if none has been set
     */
    public function getSingleViewPageUid(): int
    {
        return $this->getAsInteger('single_view_page');
    }

    /**
     * Checks whether this category has a single view page UID set.
     *
     * @return bool
     *         TRUE if this category has a single view page set, FALSE otherwise
     */
    public function hasSingleViewPageUid(): bool
    {
        return $this->hasInteger('single_view_page');
    }
}
