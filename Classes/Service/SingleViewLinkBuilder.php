<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Service;

use OliverKlee\Oelib\Interfaces\Configuration;
use OliverKlee\Seminars\Model\Event;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * This class provides functions for creating the link/URL to the single view page of an event.
 *
 * @deprecated will be removed in version 6.0.0 in #3155
 */
class SingleViewLinkBuilder
{
    private Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Creates the absolute URL to the single view of the event $event.
     *
     * @return string the absolute URL for the event's single view, not htmlspecialchared
     */
    public function createAbsoluteUrlForEvent(Event $event): string
    {
        return GeneralUtility::locationHeaderUrl($this->createRelativeUrlForEvent($event));
    }

    /**
     * Creates the relative URL to the single view of the event $event.
     *
     * @return string the relative URL for the event's single view, not htmlspecialchared
     */
    public function createRelativeUrlForEvent(Event $event): string
    {
        $linkConfiguration = [
            'parameter' => $this->getSingleViewPageForEvent($event),
            'additionalParams' => GeneralUtility::implodeArrayForUrl(
                'tx_seminars_pi1',
                ['showUid' => $event->getUid()],
                '',
                false,
                true
            ),
        ];

        return $this->getContentObject()->typoLink_URL($linkConfiguration);
    }

    /**
     * Retrieves a content object to be used for creating typolinks.
     */
    protected function getContentObject(): ContentObjectRenderer
    {
        return $this->getFrontEndController()->cObj;
    }

    private function getFrontEndController(): TypoScriptFrontendController
    {
        $controller = $GLOBALS['TSFE'] ?? null;
        if (!$controller instanceof TypoScriptFrontendController) {
            throw new \BadMethodCallException('The SingleViewLinkBuilder may only be used in FE context.', 1645981344);
        }

        return $controller;
    }

    /**
     * Gets the single view page UID/URL from $event (if any single view page is set for
     * the event) or from the configuration.
     *
     * @return string the single view page UID/URL for $event, will be empty if neither
     *         the event nor the configuration has any single view page set
     */
    protected function getSingleViewPageForEvent(Event $event): string
    {
        if ($event->hasCombinedSingleViewPage()) {
            $result = $event->getCombinedSingleViewPage();
        } elseif ($this->configurationHasSingleViewPage()) {
            $result = (string)$this->getSingleViewPageFromConfiguration();
        } else {
            $result = '';
        }

        return $result;
    }

    /**
     * Checks whether there is a single view page set in the configuration.
     */
    protected function configurationHasSingleViewPage(): bool
    {
        return $this->getSingleViewPageFromConfiguration() > 0;
    }

    /**
     * Retrieves the single view page UID from the flexforms/TS Setup configuration.
     *
     * @return int<0, max> the single view page UID from the configuration, will be 0 if no page UID has been set
     */
    protected function getSingleViewPageFromConfiguration(): int
    {
        return $this->configuration->getAsNonNegativeInteger('detailPID');
    }
}
