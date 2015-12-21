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
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
	 * @var string
	 */
	const MODULE_NAME = 'web_txseminarsM2';

	/**
	 * available sub modules
	 *
	 * @var string[]
	 */
	protected $availableSubModules = array();

	/**
	 * the ID of the currently selected sub module
	 *
	 * @var int
	 */
	protected $subModule = 0;

	/**
	 * Initializes some variables and also starts the initialization of the parent class.
	 *
	 * @return void
	 */
	public function init() {
		parent::init();

		$this->id = (int)$this->id;
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

		$this->doc = GeneralUtility::makeInstance('bigDoc');
		$this->doc->backPath = $BACK_PATH;
		$this->doc->docType = 'xhtml_strict';

		$this->doc->getPageRenderer()->addCssFile(
			'../typo3conf/ext/seminars/Resources/Public/CSS/BackEnd/BackEnd.css',
			'stylesheet',
			'all',
			'',
			FALSE
		);
		$this->doc->getPageRenderer()->addCssFile(
			'../typo3conf/ext/seminars/Resources/Public/CSS/BackEnd/Print.css',
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
			/** @var FlashMessage $message */
			$message = GeneralUtility::makeInstance(
				FlashMessage::class,
				$GLOBALS['LANG']->getLL('message_noPageTypeSelected'),
				'',
				FlashMessage::INFO
			);
			$this->addFlashMessage($message);

			echo $this->content . $this->getRenderedFlashMessages() . $this->doc->endPage();
			return;
		}

		$pageAccess = BackendUtility::readPageAccess($this->id, $this->perms_clause);
		if (!is_array($pageAccess) && !$BE_USER->user['admin']) {
			echo $this->content . $this->getRenderedFlashMessages() . $this->doc->endPage();
			return;
		}

		if (!$this->hasStaticTemplate()) {
			/** @var FlashMessage $message */
			$message = GeneralUtility::makeInstance(
				FlashMessage::class,
				$GLOBALS['LANG']->getLL('message_noStaticTemplateFound'),
				'',
				FlashMessage::WARNING
			);
			$this->addFlashMessage($message);

			echo $this->content . $this->getRenderedFlashMessages() . $this->doc->endPage();
			return;
		}

		$this->setPageData($pageAccess);

		// JavaScript function called within getDeleteIcon()
		$this->doc->JScode = '<script type="text/javascript">function jumpToUrl(URL) {document.location = URL;}</script>';

		// define the sub modules that should be available in the tab menu
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
		$this->subModule = (int)GeneralUtility::_GET('subModule');

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
			$moduleToken = t3lib_formprotection_Factory::get()->generateToken('moduleCall', self::MODULE_NAME);
			$this->content .= $this->doc->getTabMenu(
				array('M' => self::MODULE_NAME, 'moduleToken' => $moduleToken, 'id' => $this->id),
				'subModule', $this->subModule, $this->availableSubModules
			);
			$this->content .= $this->doc->spacer(5);
		}

		// Select which sub module to display.
		// If no sub module is specified, an empty page will be displayed.
		switch ($this->subModule) {
			case 2:
				/** @var tx_seminars_BackEnd_RegistrationsList $registrationsList */
				$registrationsList = GeneralUtility::makeInstance('tx_seminars_BackEnd_RegistrationsList', $this);
				$this->content .= $registrationsList->show();
				break;
			case 3:
				/** @var tx_seminars_BackEnd_SpeakersList $speakersList */
				$speakersList = GeneralUtility::makeInstance('tx_seminars_BackEnd_SpeakersList', $this);
				$this->content .= $speakersList->show();
				break;
			case 4:
				/** @var tx_seminars_BackEnd_OrganizersList $organizersList */
				$organizersList = GeneralUtility::makeInstance('tx_seminars_BackEnd_OrganizersList', $this);
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
					/** @var tx_seminars_BackEnd_EventsList $eventsList */
					$eventsList = GeneralUtility::makeInstance('tx_seminars_BackEnd_EventsList', $this);
					$this->content .= $eventsList->show();
				}
			default:
		}

		echo $this->content . $this->doc->endPage();
	}

	/**
	 * Adds a flash message to the queue.
	 *
	 * @param FlashMessage $flashMessage
	 *
	 * @return void
	 */
	protected function addFlashMessage(FlashMessage $flashMessage) {
		/** @var FlashMessageService $flashMessageService */
		$flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
		$defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
		$defaultFlashMessageQueue->enqueue($flashMessage);
	}

	/**
	 * Returns the rendered flash messages.
	 *
	 * @return string
	 */
	protected function getRenderedFlashMessages() {
		/** @var FlashMessageService $flashMessageService */
		$flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
		$defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
		$renderedFlashMessages = $defaultFlashMessageQueue->renderFlashMessages();

		return $renderedFlashMessages;
	}

	/**
	 * Checks whether the user requested the form for sending an e-mail and
	 * whether all pre-conditions for showing the form are met.
	 *
	 * @return bool TRUE if the form was requested and pre-conditions are met, FALSE otherwise
	 */
	private function isGeneralEmailFormRequested() {
		if ((int)GeneralUtility::_POST('eventUid') <= 0) {
			return FALSE;
		}

		return GeneralUtility::_POST('action') == 'sendEmail';
	}

	/**
	 * Checks whether the user requested the form for confirming an event and
	 * whether all pre-conditions for showing the form are met.
	 *
	 * @return bool TRUE if the form was requested and pre-conditions are met, FALSE otherwise
	 */
	private function isConfirmEventFormRequested() {
		if ((int)GeneralUtility::_POST('eventUid') <= 0) {
			return FALSE;
		}

		return GeneralUtility::_POST('action') == 'confirmEvent';
	}

	/**
	 * Checks whether the user requested the form for canceling an event and
	 * whether all pre-conditions for showing the form are met.
	 *
	 * @return bool TRUE if the form was requested and pre-conditions are
	 *                 met, FALSE otherwise
	 */
	private function isCancelEventFormRequested() {
		if ((int)GeneralUtility::_POST('eventUid') <= 0) {
			return FALSE;
		}

		return GeneralUtility::_POST('action') == 'cancelEvent';
	}

	/**
	 * Returns the form to send an e-mail.
	 *
	 * @return string the HTML source for the form
	 */
	private function getGeneralMailForm() {
		/** @var tx_seminars_BackEnd_GeneralEventMailForm $form */
		$form = GeneralUtility::makeInstance(
			'tx_seminars_BackEnd_GeneralEventMailForm', (int)GeneralUtility::_GP('eventUid')
		);
		$form->setPostData(GeneralUtility::_POST());

		return $form->render();
	}

	/**
	 * Returns the form to confirm an event.
	 *
	 * @return string the HTML source for the form
	 */
	private function getConfirmEventMailForm() {
		/** @var Tx_Seminars_BackEnd_ConfirmEventMailForm $form */
		$form = GeneralUtility::makeInstance(
			Tx_Seminars_BackEnd_ConfirmEventMailForm::class, (int)GeneralUtility::_GP('eventUid')
		);
		$form->setPostData(GeneralUtility::_POST());

		return $form->render();
	}

	/**
	 * Returns the form to canceling an event.
	 *
	 * @return string the HTML source for the form
	 */
	private function getCancelEventMailForm() {
		/** @var Tx_Seminars_BackEnd_CancelEventMailForm $form */
		$form = GeneralUtility::makeInstance(
			Tx_Seminars_BackEnd_CancelEventMailForm::class, (int)GeneralUtility::_GP('eventUid')
		);
		$form->setPostData(GeneralUtility::_POST());

		return $form->render();
	}

	/**
	 * Checks whether this extension's static template is included on the
	 * current page.
	 *
	 * @return bool TRUE if the static template has been included, FALSE otherwise
	 */
	private function hasStaticTemplate() {
		return tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars')->getAsBoolean('isStaticTemplateLoaded');
	}
}

// This checks permissions and exits if the users has no permission for entry.
$GLOBALS['BE_USER']->modAccess($GLOBALS['MCONF'], TRUE);

if (GeneralUtility::_GET('csv') !== '1') {
	$GLOBALS['LANG']->includeLLFile('EXT:lang/locallang_common.xml');
	$GLOBALS['LANG']->includeLLFile('EXT:lang/locallang_show_rechis.xml');
	$GLOBALS['LANG']->includeLLFile('EXT:lang/locallang_mod_web_list.xml');
	$GLOBALS['LANG']->includeLLFile('EXT:seminars/Classes/BackEnd/locallang.xml');
	$GLOBALS['LANG']->includeLLFile('EXT:seminars/Classes/pi2/locallang.xml');

	/** @var tx_seminars_module2 $SOBE */
	$SOBE = GeneralUtility::makeInstance('tx_seminars_module2');
	$SOBE->init();

	$SOBE->main();
} else {
	/** @var tx_seminars_pi2 $csvExporter */
	$csvExporter = GeneralUtility::makeInstance('tx_seminars_pi2');
	echo $csvExporter->main();
}