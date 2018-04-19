<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Core\TimeTracker\NullTimeTracker;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Plugin\AbstractPlugin;

/**
 * This class provides functions for creating the link/URL to the single view page of an event.
 *
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Service_SingleViewLinkBuilder
{
    /**
     * a plugin instance that provides access to the flexforms plugin settings
     *
     * @var AbstractPlugin
     */
    private $plugin = null;

    /**
     * whether this class has created a fake front end which needs to get
     * cleaned up again
     *
     * @var bool
     */
    private $hasFakeFrontEnd = false;

    /**
     * The destructor.
     */
    public function __destruct()
    {
        unset($this->plugin);

        if ($this->hasFakeFrontEnd) {
            $this->discardFakeFrontEnd();
        }
    }

    /**
     * Discards the fake front end.
     *
     * This function nulls out $GLOBALS['TSFE'] and $GLOBALS['TT']. In addition,
     * any logged-in front-end user will be logged out.
     *
     * @return void
     */
    protected function discardFakeFrontEnd()
    {
        unset(
            $GLOBALS['TSFE']->tmpl, $GLOBALS['TSFE']->sys_page,
            $GLOBALS['TSFE']->fe_user, $GLOBALS['TSFE']->TYPO3_CONF_VARS,
            $GLOBALS['TSFE']->config, $GLOBALS['TSFE']->TCAcachedExtras,
            $GLOBALS['TSFE']->imagesOnPage, $GLOBALS['TSFE']->cObj,
            $GLOBALS['TSFE']->csConvObj, $GLOBALS['TSFE']->pagesection_lockObj,
            $GLOBALS['TSFE']->pages_lockObj
        );
        $GLOBALS['TSFE'] = null;
        $GLOBALS['TT'] = null;

        $this->hasFakeFrontEnd = false;
    }

    /**
     * Sets the plugin used accessing to the flexforms plugin settings.
     *
     * @param AbstractPlugin $plugin a seminars plugin instance
     *
     * @return void
     */
    public function setPlugin(AbstractPlugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Returns the plugin used for accessing the flexforms plugin settings.
     *
     * @return Tx_Oelib_TemplateHelper
     *         the plugin, will be NULL if non has been set via setPlugin
     *
     * @see setPlugin
     */
    protected function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * Creates the absolute URL to the single view of the event $event.
     *
     * @param Tx_Seminars_Model_Event $event the event to create the link for
     *
     * @return string
     *         the absolute URL for the event's single view, not htmlspecialchared
     */
    public function createAbsoluteUrlForEvent(Tx_Seminars_Model_Event $event)
    {
        return GeneralUtility::locationHeaderUrl(
            $this->createRelativeUrlForEvent($event)
        );
    }

    /**
     * Creates the relative URL to the single view of the event $event.
     *
     * @param Tx_Seminars_Model_Event $event the event to create the link for
     *
     * @return string
     *         the relative URL for the event's single view, not htmlspecialchared
     */
    public function createRelativeUrlForEvent(Tx_Seminars_Model_Event $event)
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
     *
     * @return ContentObjectRenderer a content object for creating typolinks
     */
    protected function getContentObject()
    {
        if (!isset($GLOBALS['TSFE']) || !is_object($GLOBALS['TSFE'])) {
            $this->createFakeFrontEnd();
        }

        return $GLOBALS['TSFE']->cObj;
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

        $GLOBALS['TT'] = GeneralUtility::makeInstance(NullTimeTracker::class);

        /** @var TypoScriptFrontendController $frontEnd */
        $frontEnd = GeneralUtility::makeInstance(TypoScriptFrontendController::class, $GLOBALS['TYPO3_CONF_VARS'], 0, 0);

        // simulates a normal FE without any logged-in FE or BE user
        $frontEnd->beUserLogin = false;
        $frontEnd->workspacePreview = '';
        $frontEnd->initFEuser();
        $frontEnd->determineId();
        $frontEnd->initTemplate();
        $frontEnd->config = [];

        $frontEnd->tmpl->getFileName_backPath = PATH_site;

        $frontEnd->newCObj();

        $GLOBALS['TSFE'] = $frontEnd;

        $this->hasFakeFrontEnd = true;
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
     * @param Tx_Seminars_Model_Event $event the event for which to get the single view page
     *
     * @return string
     *         the single view page UID/URL for $event, will be empty if neither
     *         the event nor the configuration has any single view page set
     */
    protected function getSingleViewPageForEvent(Tx_Seminars_Model_Event $event)
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
     * @return bool
     *         TRUE if a single view page has been set in the configuration,
     *         FALSE otherwise
     */
    protected function configurationHasSingleViewPage()
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
    protected function getSingleViewPageFromConfiguration()
    {
        if ($this->plugin !== null) {
            $result = $this->getPlugin()->getConfValueInteger('detailPID');
        } else {
            $result = Tx_Oelib_ConfigurationRegistry
                ::get('plugin.tx_seminars_pi1')->getAsInteger('detailPID');
        }

        return $result;
    }
}
