<?php
/***************************************************************
* Copyright notice
*
* (c) 2006-2013 Mario Rimann (typo3-coding@rimann.org)
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
require_once($BACK_PATH . 'template.php');

$LANG->includeLLFile('EXT:lang/locallang_common.xml');
$LANG->includeLLFile('EXT:lang/locallang_show_rechis.xml');
$LANG->includeLLFile('EXT:lang/locallang_mod_web_list.xml');
$LANG->includeLLFile('EXT:seminars/BackEnd/locallang.xml');
$LANG->includeLLFile('EXT:seminars/pi2/locallang.xml');

// This checks permissions and exits if the users has no permission for entry.
$BE_USER->modAccess($MCONF, 1);

/**
 * Back-end module "Events".
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Mario Rimann <typo3-coding@rimann.org>
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_module2 extends tx_seminars_BackEnd_Module {
	/**
	 * available sub modules
	 *
	 * @var array
	 */
	private $availableSubModules;

	/**
	 * the ID of the currently selected sub module
	 *
	 * @var integer
	 */
	private $subModule;

	/**
	 * Initializes some variables and also starts the initialization of the parent class.
	 *
	 * @return void
	 */
	public function init() {
		parent::init();

		$this->id = intval($this->id);
	}

	/**
	 * Main function of the module. Writes the content to $this->content.
	 *
	 * No return value; output is directly written to the page.
	 *
	 * @return void
	 */
	public function main() {
		global $LANG, $BACK_PATH, $BE_USER;

		// starts the document
		$this->doc = t3lib_div::makeInstance('bigDoc');
		$this->doc->backPath = $BACK_PATH;
		$this->doc->docType = 'xhtml_strict';

		$this->doc->getPageRenderer()->addCssFile(
			'BackEnd.css',
			'stylesheet',
			'all',
			'',
			FALSE
		);
		$this->doc->getPageRenderer()->addCssFile(
			'../Resources/Public/CSS/BackEnd/Print.css',
			'stylesheet',
			'print',
			'',
			FALSE
		);

		// draw the header
		$this->content = $this->doc->startPage($LANG->getLL('title'));
		$this->content .= $this->doc->header($LANG->getLL('title'));
		$this->content .= $this->doc->spacer(5);

		if ($this->id <= 0) {
			$message = t3lib_div::makeInstance(
				't3lib_FlashMessage',
				$GLOBALS['LANG']->getLL('message_noPageTypeSelected'),
				'',
				t3lib_FlashMessage::INFO
			);
			t3lib_FlashMessageQueue::addMessage($message);

			echo $this->content . t3lib_FlashMessageQueue::renderFlashMessages() .
				$this->doc->endPage();
			return;
		}

		$pageAccess = t3lib_BEfunc::readPageAccess($this->id, $this->perms_clause);
		if (!is_array($pageAccess) && !$BE_USER->user['admin']) {
			echo $this->content . t3lib_FlashMessageQueue::renderFlashMessages() .
				$this->doc->endPage();

			return;
		}

		if (!$this->hasStaticTemplate()) {
			$message = t3lib_div::makeInstance(
				't3lib_FlashMessage',
				$GLOBALS['LANG']->getLL('message_noStaticTemplateFound'),
				'',
				t3lib_FlashMessage::WARNING
			);
			t3lib_FlashMessageQueue::addMessage($message);

			echo $this->content . t3lib_FlashMessageQueue::renderFlashMessages() .
				$this->doc->endPage();
			return;
		}

		$this->setPageData($pageAccess);

		// JavaScript function called within getDeleteIcon()
		$this->doc->JScode = '
			<script type="text/javascript">/*<![CDATA[*/
				function jumpToUrl(URL) {
					document.location = URL;
				}
			/*]]>*/</script>
		';

		// define the sub modules that should be available in the tabmenu
		$this->availableSubModules = array();

		// only show the tabs if the back-end user has access to the
		// corresponding tables
		if ($BE_USER->check('tables_select', 'tx_seminars_seminars')) {
			$this->availableSubModules[1] = $LANG->getLL('subModuleTitle_events');
		}

		if ($BE_USER->check('tables_select', 'tx_seminars_attendances')) {
			$this->availableSubModules[2] = $LANG->getLL('subModuleTitle_registrations');
		}

		if ($BE_USER->check('tables_select', 'tx_seminars_speakers')) {
			$this->availableSubModules[3] = $LANG->getLL('subModuleTitle_speakers');
		}

		if ($BE_USER->check('tables_select', 'tx_seminars_organizers')) {
			$this->availableSubModules[4] = $LANG->getLL('subModuleTitle_organizers');
		}

		// Read the selected sub module (from the tab menu) and make it available within this class.
		$this->subModule = intval(t3lib_div::_GET('subModule'));

		// If $this->subModule is not a key of $this->availableSubModules,
		// set it to the key of the first element in $this->availableSubModules
		// so the first tab is activated.
		if (!array_key_exists($this->subModule, $this->availableSubModules)) {
			reset($this->availableSubModules);
			$this->subModule = key($this->availableSubModules);
		}

		// Only generate the tab menu if the current back-end user has the
		// rights to show any of the tabs.
		if ($this->subModule) {
			$this->content .= $this->doc->getTabMenu(array('id' => $this->id),
				'subModule',
				$this->subModule,
				$this->availableSubModules);
			$this->content .= $this->doc->spacer(5);
		}

		// Select which sub module to display.
		// If no sub module is specified, an empty page will be displayed.
		switch ($this->subModule) {
			case 2:
				$registrationsList = tx_oelib_ObjectFactory::make(
					'tx_seminars_BackEnd_RegistrationsList', $this
				);
				$this->content .= $registrationsList->show();
				break;
			case 3:
				$speakersList = tx_oelib_ObjectFactory::make(
					'tx_seminars_BackEnd_SpeakersList', $this
				);
				$this->content .= $speakersList->show();
				break;
			case 4:
				$organizersList = tx_oelib_ObjectFactory::make(
					'tx_seminars_BackEnd_OrganizersList', $this
				);
				$this->content .= $organizersList->show();
				break;
			case 1:
				if ($this->isGeneralEmailFormRequested()) {
					$this->content .= $this->getGeneralMailForm();
				} elseif ($this->isConfirmEventFormRequested()) {
					$this->content .= $this->getConfirmEventMailForm();
				} elseif ($this->isCancelEventFormRequested()) {
					$this->content .= $this->getCancelEventMailForm();
				} else {
					$eventsList = tx_oelib_ObjectFactory::make(
						'tx_seminars_BackEnd_EventsList', $this
					);
					$this->content .= $eventsList->show();
					$eventsList->__destruct();
				}
			default:
				$this->content .= '';
				break;
		}

		echo $this->content . $this->doc->endPage();
	}

	/**
	 * Checks whether the user requested the form for sending an e-mail and
	 * whether all pre-conditions for showing the form are met.
	 *
	 * @return boolean TRUE if the form was requested and pre-conditions are
	 *                 met, FALSE otherwise
	 */
	private function isGeneralEmailFormRequested() {
		if (!(intval(t3lib_div::_POST('eventUid')) > 0)) {
			return FALSE;
		}

		return t3lib_div::_POST('action') == 'sendEmail';
	}

	/**
	 * Checks whether the user requested the form for confirming an event and
	 * whether all pre-conditions for showing the form are met.
	 *
	 * @return boolean TRUE if the form was requested and pre-conditions are
	 *                 met, FALSE otherwise
	 */
	private function isConfirmEventFormRequested() {
		if ((!intval(t3lib_div::_POST('eventUid')) > 0)) {
			return FALSE;
		}

		return t3lib_div::_POST('action') == 'confirmEvent';
	}

	/**
	 * Checks whether the user requested the form for canceling an event and
	 * whether all pre-conditions for showing the form are met.
	 *
	 * @return boolean TRUE if the form was requested and pre-conditions are
	 *                 met, FALSE otherwise
	 */
	private function isCancelEventFormRequested() {
		if (!(intval(t3lib_div::_POST('eventUid')) > 0)) {
			return FALSE;
		}

		return t3lib_div::_POST('action') == 'cancelEvent';
	}

	/**
	 * Returns the form to send an e-mail.
	 *
	 * @return string the HTML source for the form
	 */
	private function getGeneralMailForm() {
		$form = tx_oelib_ObjectFactory::make(
			'tx_seminars_BackEnd_GeneralEventMailForm',
			intval(t3lib_div::_GP('eventUid'))
		);
		$form->setPostData(t3lib_div::_POST());

		$result = $form->render();
		$form->__destruct();

		return $result;
	}

	/**
	 * Returns the form to confirm an event.
	 *
	 * @return string the HTML source for the form
	 */
	private function getConfirmEventMailForm() {
		$form = tx_oelib_ObjectFactory::make(
			'tx_seminars_BackEnd_ConfirmEventMailForm',
			intval(t3lib_div::_GP('eventUid'))
		);
		$form->setPostData(t3lib_div::_POST());

		$result = $form->render();
		$form->__destruct();

		return $result;
	}

	/**
	 * Returns the form to canceling an event.
	 *
	 * @return string the HTML source for the form
	 */
	private function getCancelEventMailForm() {
		$form = tx_oelib_ObjectFactory::make(
			'tx_seminars_BackEnd_CancelEventMailForm',
			intval(t3lib_div::_GP('eventUid'))
		);
		$form->setPostData(t3lib_div::_POST());

		$result = $form->render();
		$form->__destruct();

		return $result;
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

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/BackEnd/index.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/BackEnd/index.php']);
}

// Make instance:
$SOBE = tx_oelib_ObjectFactory::make('tx_seminars_module2');
$SOBE->init();

// Include files?
foreach ($SOBE->include_once as $INC_FILE) {
	include_once($INC_FILE);
}

$SOBE->main();
?>