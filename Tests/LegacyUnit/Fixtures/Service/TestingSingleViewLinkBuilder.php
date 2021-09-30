<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\Service;

use OliverKlee\Seminars\Model\Event;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * This class just makes some functions public for testing purposes.
 */
class TestingSingleViewLinkBuilder extends \Tx_Seminars_Service_SingleViewLinkBuilder
{
    /**
     * Retrieves a content object to be used for creating typolinks.
     *
     * @return ContentObjectRenderer a content object for creating typolinks
     */
    public function getContentObject(): ContentObjectRenderer
    {
        return parent::getContentObject();
    }

    /**
     * Gets the single view page UID/URL from $event (if any single view page is set for
     * the event) or from the configuration.
     *
     * @param Event $event the event for which to get the single view page
     *
     * @return string
     *         the single view page UID/URL for $event, will be empty if neither
     *         the event nor the configuration has any single view page set
     */
    public function getSingleViewPageForEvent(Event $event): string
    {
        return parent::getSingleViewPageForEvent($event);
    }

    /**
     * Checks whether there is a single view page set in the configuration.
     *
     * @return bool
     *         TRUE if a single view page has been set in the configuration,
     *         FALSE otherwise
     */
    public function configurationHasSingleViewPage(): bool
    {
        return parent::configurationHasSingleViewPage();
    }

    /**
     * Retrieves the single view page UID from the flexforms/TS Setup
     * configuration.
     *
     * @return int
     *         the single view page UID from the configuration, will be 0 if no
     *         page UID has been set
     */
    public function getSingleViewPageFromConfiguration(): int
    {
        return parent::getSingleViewPageFromConfiguration();
    }
}
