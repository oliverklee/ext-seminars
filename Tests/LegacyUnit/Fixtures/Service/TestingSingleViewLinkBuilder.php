<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\Service;

use OliverKlee\Seminars\Model\Event;
use OliverKlee\Seminars\Service\SingleViewLinkBuilder;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * This class just makes some functions public for testing purposes.
 *
 * This class is final as it currently is used for mocks.
 */
class TestingSingleViewLinkBuilder extends SingleViewLinkBuilder
{
    public function getContentObject(): ContentObjectRenderer
    {
        return parent::getContentObject();
    }

    public function getSingleViewPageForEvent(Event $event): string
    {
        return parent::getSingleViewPageForEvent($event);
    }

    public function configurationHasSingleViewPage(): bool
    {
        return parent::configurationHasSingleViewPage();
    }

    public function getSingleViewPageFromConfiguration(): int
    {
        return parent::getSingleViewPageFromConfiguration();
    }
}
