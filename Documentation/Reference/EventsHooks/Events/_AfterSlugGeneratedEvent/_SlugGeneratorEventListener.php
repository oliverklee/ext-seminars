<?php

declare(strict_types=1);

namespace MyVendor\MyExtension\EventListener\Seo;

use OliverKlee\Seminars\Seo\Event\AfterSlugGeneratedEvent;

/**
 * Generates event slugs in the format "slugified-title_uid".
 */
final class SlugGeneratorEventListener
{
    public function __invoke(AfterSlugGeneratedEvent $event): void
    {
        $slugContext = $event->getSlugContext();
        if ($slugContext->getEventUid() > 0) {
            $event->setSlug($slugContext->getSlugifiedTitle() . '_' . $slugContext->getEventUid());
        }
    }
}
