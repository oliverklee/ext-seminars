<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Model;

use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Seminars\Model\Interfaces\Titled;

/**
 * This class represents an event type.
 */
class EventType extends AbstractModel implements Titled
{
    /**
     * @return string our title, will not be empty
     */
    public function getTitle(): string
    {
        return $this->getAsString('title');
    }

    /**
     * @param string $title our title to set, must not be empty
     */
    public function setTitle(string $title): void
    {
        if ($title == '') {
            throw new \InvalidArgumentException('The parameter $title must not be empty.', 1333296812);
        }

        $this->setAsString('title', $title);
    }

    /**
     * Gets the UID of the single view page for events of this type.
     *
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
