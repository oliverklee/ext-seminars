<?php
/***************************************************************
* Copyright notice
*
* (c) 2006-2010 Mario Rimann (typo3-coding@rimann.org)
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

unset($MCONF);
$MCONF = array();

require_once('conf.php');
require_once($BACK_PATH . 'init.php');
require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');
require_once($BACK_PATH . 'template.php');

$LANG->includeLLFile('EXT:seminars/BackEnd/locallang.xml');

// This checks permissions and exits if the users has no permission for entry.
$BE_USER->modAccess($MCONF, 1);

/**
 * Module 'Events' for the 'seminars' extension (the ExtJS version).
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Mario Rimann <typo3-coding@rimann.org>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_module1 extends tx_seminars_BackEnd_Module {
	/**
	 * locallang files to add as inline language labels
	 *
	 * @var array
	 */
	private $locallangFiles = array(
		'EXT:lang/locallang_show_rechis.xml',
		'EXT:lang/locallang_mod_web_list.xml',
		'EXT:seminars/BackEnd/locallang.xml',
		'EXT:seminars/pi2/locallang.xml',
	);

	/**
	 * Initializes some variables and also starts the initialization of the
	 * parent class.
	 */
	public function init() {
		parent::init();

		$this->id = intval($this->id);
	}

	/**
	 * Main function of the module. Writes the content to $this->content.
	 *
	 * No return value; output is directly written to the page.
	 */
	public function main() {
		$this->doc = t3lib_div::makeInstance('bigDoc');
		$this->doc->backPath = $GLOBALS['BACK_PATH'];
		$this->doc->docType = 'xhtml_strict';
		$this->doc->getPageRenderer()->addCssFile(
			'../Resources/Public/CSS/BackEndExtJs/BackEnd.css',
			'stylesheet',
			'all',
			'',
			FALSE
		);

		$this->content = '';

		if ($this->id <= 0) {
			$this->content = '<p class="errorMessage">' .
				$GLOBALS['LANG']->getLL('message_noPageTypeSelected') . '</p>';
			$this->outputContent();
			return;
		}

		$pageAccess = t3lib_BEfunc::readPageAccess($this->id, $this->perms_clause);
		if (!is_array($pageAccess) && !$GLOBALS['BE_USER']->user['admin']) {
			$this->outputContent();
			return;
		}

		if (!$this->hasStaticTemplate()) {
			$this->content = '<p class="errorMessage">' .
				$GLOBALS['LANG']->getLL('message_noStaticTemplateFound') .
				'</p>';

			$this->outputContent();
			return;
		}

		$this->doc->getPageRenderer()->addJsFile(
			'../Resources/Public/JavaScript/BackEndExtJs/BackEnd.js',
			'text/javascript',
			FALSE,
			TRUE
		);
		$this->doc->getPageRenderer()->loadExtJS();
		$this->addInlineLanguageLabels();
		$this->addInlineSettings();

		$this->setPageData($pageAccess);

		// Output the whole content.
		$this->outputContent();
	}

	/**
	 * Reads all language labels from every file listed in $this->locallangFiles
	 * and adds them as inline language labels to the page renderer which
	 * outputs them as JSON in the page header.
	 */
	private function addInlineLanguageLabels() {
		foreach ($this->locallangFiles as $file) {
			$localizedLabels = t3lib_div::readLLfile(
				$file,
				$GLOBALS['LANG']->lang,
				$GLOBALS['LANG']->charSet
			);
			$this->doc->getPageRenderer()->addInlineLanguageLabelArray(
				$localizedLabels[$GLOBALS['LANG']->lang]
			);
		}
	}

	/**
	 * Adds some inline settings to the page renderer which outputs them as
	 * JSON in the page header.
	 */
	private function addInlineSettings() {
		$this->addSubmoduleAccessInlineSettings();

		$this->doc->getPageRenderer()->addInlineSetting(FALSE, 'PID', $this->id);

		$ajaxUrl = $GLOBALS['BACK_PATH'] . 'ajax.php?ajaxID=';

		$this->doc->getPageRenderer()->addInlineSetting(
			'Backend.Seminars.Events.Store',
			'autoLoadURL',
			$ajaxUrl . 'Seminars::getEvents'
		);
		$this->doc->getPageRenderer()->addInlineSetting(
			'Backend.Seminars.Registrations.Store',
			'autoLoadURL',
			$ajaxUrl . 'Seminars::getRegistrations'
		);
		$this->doc->getPageRenderer()->addInlineSetting(
			'Backend.Seminars.Speakers.Store',
			'autoLoadURL',
			$ajaxUrl . 'Seminars::getSpeakers'
		);
		$this->doc->getPageRenderer()->addInlineSetting(
			'Backend.Seminars.Organizers.Store',
			'autoLoadURL',
			$ajaxUrl . 'Seminars::getOrganizers'
		);

		$this->doc->getPageRenderer()->addInlineSettingArray(
			'Backend.Seminars.URL',
			array(
				'alt_doc' => $GLOBALS['BACK_PATH'] . 'alt_doc.php',
			)
		);
	}

	/**
	 * Adds the sub-module access settings as inline setting to the page
	 * renderer which outputs them as JSON in the page header.
	 */
	private function addSubmoduleAccessInlineSettings() {
		$tables = array(
			'Events' => 'tx_seminars_seminars',
			'Registrations' => 'tx_seminars_attendances',
			'Speakers' => 'tx_seminars_speakers',
			'Organizers' => 'tx_seminars_organizers',
		);

		foreach ($tables as $module => $table) {
			$value = TRUE;

			if ($GLOBALS['BE_USER']->check('tables_select', $table)) {
				$value = FALSE;
			}

			$this->doc->getPageRenderer()->addInlineSetting(
				'Backend.Seminars.' . $module . '.TabPanel',
				'hidden',
				$value
			);
		}
	}

	/**
	 * Wraps the content in $this->content with the HTML for the page start and
	 * the page end and echos it.
	 */
	private function outputContent() {
		echo $this->doc->startPage($GLOBALS['LANG']->getLL('title')) .
			$this->content .
			$this->doc->endPage();
	}

	/**
	 * Checks whether this extension's static template is included on the
	 * current page.
	 *
	 * @return boolean TRUE if the static template has been included, FALSE
	 *                 otherwise
	 */
	private function hasStaticTemplate() {
		return tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars')
			->getAsBoolean('isStaticTemplateLoaded');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/BackEndExtJs/index.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/BackEndExtJs/index.php']);
}

// Make instance:
$SOBE = tx_oelib_ObjectFactory::make('tx_seminars_module1');
$SOBE->init();

// Include files?
foreach ($SOBE->include_once as $INC_FILE) {
	include_once($INC_FILE);
}

$SOBE->main();
?>