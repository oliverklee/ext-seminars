<?php

declare(strict_types=1);

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Templating\TemplateHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * This class provides functions for creating the link/URL to the single view page of an event.
 */
class Tx_Seminars_Service_SingleViewLinkBuilder
{
    /**
     * a plugin instance that provides access to the flexforms plugin settings
     *
     * @var TemplateHelper|null
     */
    private $plugin = null;

    /**
     * Sets the plugin used accessing to the flexforms plugin settings.
     *
     * @return void
     */
    public function setPlugin(TemplateHelper $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Returns the plugin used for accessing the flexforms plugin settings.
     *
     * @return TemplateHelper|null
     */
    protected function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * Creates the absolute URL to the single view of the event $event.
     *
     * @param \Tx_Seminars_Model_Event $event the event to create the link for
     *
     * @return string the absolute URL for the event's single view, not htmlspecialchared
     */
    public function createAbsoluteUrlForEvent(\Tx_Seminars_Model_Event $event): string
    {
        return GeneralUtility::locationHeaderUrl($this->createRelativeUrlForEvent($event));
    }

    /**
     * Creates the relative URL to the single view of the event $event.
     *
     * @param \Tx_Seminars_Model_Event $event the event to create the link for
     *
     * @return string
     *         the relative URL for the event's single view, not htmlspecialchared
     */
    public function createRelativeUrlForEvent(\Tx_Seminars_Model_Event $event): string
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
     *
     * @return ContentObjectRenderer a content object for creating typolinks
     */
    protected function getContentObject(): ContentObjectRenderer
    {
        if ($this->getFrontEndController() === null) {
            $this->createFakeFrontEnd();
        }

        return $this->getFrontEndController()->cObj;
    }

    /**
     * @return TypoScriptFrontendController|null
     */
    private function getFrontEndController()
    {
        return $GLOBALS['TSFE'] ?? null;
    }

    /**
     * Creates an artificial front end (which is necessary for creating
     * typolinks).
     *
     * @return void
     */
    protected function createFakeFrontEnd()
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
     *
     * @return void
     */
    private function suppressFrontEndCookies()
    {
        $_POST['FE_SESSION_KEY'] = '';
        $_GET['FE_SESSION_KEY'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['dontSetCookie'] = 1;
    }

    /**
     * Gets the single view page UID/URL from $event (if any single view page is set for
     * the event) or from the configuration.
     *
     * @param \Tx_Seminars_Model_Event $event the event for which to get the single view page
     *
     * @return string
     *         the single view page UID/URL for $event, will be empty if neither
     *         the event nor the configuration has any single view page set
     */
    protected function getSingleViewPageForEvent(\Tx_Seminars_Model_Event $event): string
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
     *
     * @return bool whether a single view page has been set in the configuration
     */
    protected function configurationHasSingleViewPage(): bool
    {
        return $this->getSingleViewPageFromConfiguration() > 0;
    }

    /**
     * Retrieves the single view page UID from the flexforms/TS Setup
     * configuration.
     *
     * @return int
     *         the single view page UID from the configuration, will be 0 if no
     *         page UID has been set
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
