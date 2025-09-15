<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Event;

use OliverKlee\Seminars\Domain\Model\Category;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * This interface is required for events that contain topic information: `SingleEvent` and `EventTopic`.
 */
interface EventTopicInterface extends EventInterface
{
    /**
     * @return ObjectStorage<Category>
     */
    public function getCategories(): ObjectStorage;
}
