<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Event;

use OliverKlee\Seminars\Domain\Model\Category;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Dummy event topic to be used as empty option in selects.
 */
class NullEventTopic extends AbstractDomainObject implements EventTopicInterface
{
    /**
     * @return int<1, max>|null
     */
    public function getUid(): ?int
    {
        return null;
    }

    public function getTitle(): string
    {
        return '';
    }

    /**
     * @return ObjectStorage<Category>
     */
    public function getCategories(): ObjectStorage
    {
        /** @var ObjectStorage<Category> $categories */
        $categories = new ObjectStorage();

        return $categories;
    }
}
