<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Service;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Templating\TemplateHelper;
use OliverKlee\Seminars\Model\Event;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * This class provides functions for creating the link/URL to the single view page of an event.
 */
class SingleViewLinkBuilder
{
    /**
     * a plugin instance that provides access to the flexforms plugin settings
     *
     * @var TemplateHelper|null
     */
    private $plugin = null;

    /**
     * Sets the plugin used accessing to the flexforms plugin settings.
     */
    public function setPlugin(TemplateHelper $plugin): void
    {
        $this->plugin = $plugin;
    }

    /**
     * Returns the plugin used for accessing the flexforms plugin settings.
     */
    protected function getPlugin(): ?TemplateHelper
    {
        return $this->plugin;
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

        return (string)$this->getContentObject()->typoLink_URL($linkConfiguration);
    }

    /**
     * Retrieves a content object to be used for creating typolinks.
     */
    protected function getContentObject(): ContentObjectRenderer
    {
        if ($this->getFrontEndController() === null) {
            $this->createFakeFrontEnd();
        }

        return $this->getFrontEndController()->cObj;
    }

    private function getFrontEndController(): ?TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'] ?? null;
    }

    /**
     * Creates an artificial front end (which is necessary for creating typolinks).
     */
    protected function createFakeFrontEnd(): void
    {
        $this->suppressFrontEndCookies();

        /** @var TypoScriptFrontendController $frontEnd */
        $frontEnd = GeneralUtility::makeInstance(
            TypoScriptFrontendController::class,
            $GLOBALS['TYPO3_CONF_VARS'],
            0,
            0
        );

        $frontEnd->fe_user = GeneralUtility::makeInstance(FrontendUserAuthentication::class);

        $frontEnd->determineId();
        $frontEnd->initTemplate();
        $frontEnd->config = [];

        $frontEnd->newCObj();

        $GLOBALS['TSFE'] = $frontEnd;
    }

    /**
     * Makes sure that no FE login cookies will be sent.
     */
    private function suppressFrontEndCookies(): void
    {
        $_POST['FE_SESSION_KEY'] = '';
        $_GET['FE_SESSION_KEY'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['dontSetCookie'] = 1;
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
     * @return int the single view page UID from the configuration, will be 0 if no page UID has been set
     */
    protected function getSingleViewPageFromConfiguration(): int
    {
        if ($this->getPlugin() instanceof TemplateHelper) {
            $result = $this->getPlugin()->getConfValueInteger('detailPID');
        } else {
            $result = ConfigurationRegistry::get('plugin.tx_seminars_pi1')->getAsInteger('detailPID');
        }

        return $result;
    }
}
