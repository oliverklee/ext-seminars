<?php

use TYPO3\CMS\Core\Localization\Parser\XliffParser;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class that adds the wizard icon.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_FrontEnd_WizardIcon
{
    /**
     * Processes the wizard items array.
     *
     * @param array[] $wizardItems the wizard items, may be empty
     *
     * @return array[] modified array with wizard items
     */
    public function proc(array $wizardItems)
    {
        $localLanguage = $this->includeLocalLang();

        $wizardItems['plugins_tx_seminars_pi1'] = [
            'icon' => ExtensionManagementUtility::extRelPath('seminars') . 'Resources/Public/Icons/ContentWizard.gif',
            'title' => $GLOBALS['LANG']->getLLL('pi1_title', $localLanguage),
            'description' => $GLOBALS['LANG']->getLLL('pi1_description', $localLanguage),
            'params' => '&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=seminars_pi1',
        ];

        return $wizardItems;
    }

    /**
     * Reads the locallang.xlf and returns the $LOCAL_LANG array found in that file.
     *
     * @return array[] the found language labels
     */
    public function includeLocalLang()
    {
        /** @var XliffParser $xmlParser */
        $xmlParser = GeneralUtility::makeInstance(XliffParser::class);
        $localLanguage = $xmlParser->getParsedData(
            ExtensionManagementUtility::extPath('seminars') . 'Resources/Private/Language/locallang.xlf',
            $GLOBALS['LANG']->lang
        );

        return $localLanguage;
    }
}
