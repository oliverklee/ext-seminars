<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * This class represents a event. It is a dummy placeholder until we have resolved the STI.
 */
class Event extends AbstractEntity implements EventInterface
{
    use EventTrait;
}
