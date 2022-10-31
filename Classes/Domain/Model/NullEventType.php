<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;

/**
 * Dummy event type to be used as empty option in selects.
 */
class NullEventType extends AbstractDomainObject implements EventTypeInterface
{
    public function getUid(): ?int
    {
        return null;
    }

    /**
     * @return ''
     */
    public function getTitle(): string
    {
        return '';
    }
}
