<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2014 Niels Pardon (mail@niels-pardon.de)
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

/**
 * This is the base class for lists in the back end.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
abstract class tx_seminars_BackEnd_AbstractList {
	/**
	 * @var string
	 */
	const MODULE_NAME = 'web_txseminarsM2';

	/**
	 * @var string the name of the table we're working on
	 */
	protected $tableName = '';

	/**
	 * @var tx_seminars_BackEnd_Module the back-end module
	 */
	protected $page = NULL;

	/**
	 * @var tx_oelib_Template the template object
	 */
	protected $template = NULL;

	/**
	 * @var string the path to the template file of this list
	 */
	protected $templateFile = '';

	/**
	 * @var array the access rights to page UIDs
	 */
	protected $accessRights = array();

	/**
	 * @var int the depth of the recursion for the back-end lists
	 */
	const RECURSION_DEPTH = 250;

	/**
	 * @var int the page type of a sys-folder
	 */
	const SYSFOLDER_TYPE = 254;

	/**
	 * The constructor. Sets the table name and the back-end page object.
	 *
	 * @param tx_seminars_BackEnd_Module $module the current back-end module
	 */
	public function __construct(tx_seminars_BackEnd_Module $module) {
		$this->page = $module;

		$this->template = tx_oelib_TemplateRegistry::get($this->templateFile);
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		unset($this->page, $this->template);
	}

	/**
	 * Generates an edit record icon which is linked to the edit view of
	 * a record.
	 *
	 * @param int $uid the UID of the record, must be > 0
	 * @param int $pageUid the PID of the record, must be >= 0
	 *
	 * @return string the HTML source code to return
	 */
	public function getEditIcon($uid, $pageUid) {
		$result = '';

		if ($GLOBALS['BE_USER']->check('tables_modify', $this->tableName)
			&& $this->doesUserHaveAccess($pageUid)
		) {
			$params = '&edit['.$this->tableName.']['.$uid.']=edit';
			$editOnClick = $this->editNewUrl($params, $GLOBALS['BACK_PATH']);
			$langEdit = $GLOBALS['LANG']->getLL('edit');

			$result = '<a href="' .
				htmlspecialchars($editOnClick) . '">' .
				'<img src="/' . t3lib_extMgm::siteRelPath('seminars') . 'Resources/Public/Icons/Edit.gif' .
				'" title="' . $langEdit . '" alt="' . $langEdit . '" class="icon" />' .
				'</a>';
		}

		return $result;
	}

	/**
	 * Generates a linked delete record icon whith a JavaScript confirmation
	 * window.
	 *
	 * @param int $uid the UID of the record, must be > 0
	 * @param int $pageUid the PID of the record, must be >= 0
	 *
	 * @return string the HTML source code to return
	 */
	public function getDeleteIcon($uid, $pageUid) {
		global $LANG, $BE_USER;

		$result = '';

		if ($BE_USER->check('tables_modify', $this->tableName)
			&& $this->doesUserHaveAccess($pageUid)
		) {
			$params = '&cmd['.$this->tableName.']['.$uid.'][delete]=1';

			$referenceWarning = t3lib_BEfunc::referenceCount(
				$this->tableName,
				$uid,
				' '.$LANG->getLL('referencesWarning')
			);

			$confirmation = htmlspecialchars(
				'if (confirm('
				.$LANG->JScharCode(
					$LANG->getLL('deleteWarning')
					.$referenceWarning)
				.')) {return true;} else {return false;}');
			$langDelete = $LANG->getLL('delete', 1);
			$result = '<a href="' .
				htmlspecialchars($this->page->doc->issueCommand($params)) .
				'" onclick="' . $confirmation . '">' .
				'<img src="/' . t3lib_extMgm::siteRelPath('seminars') . 'Resources/Public/Icons/Garbage.gif' .
				'" title="' . $langDelete . '" alt="' . $langDelete . '" class="deleteicon" />' .
				'</a>';
		}

		return $result;
	}

	/**
	 * Returns a "create new record" image tag that is linked to the new record view.
	 *
	 * @param int $pid the page ID where the record should be stored, must be > 0
	 *
	 * @return string the HTML source code to return
	 */
	public function getNewIcon($pid) {
		global $BACK_PATH, $LANG, $BE_USER;

		$result = '';
		$newRecordPid = $this->getNewRecordPid();
		$pid = ($newRecordPid > 0) ? $newRecordPid : $pid;
		$pageData = $this->page->getPageData();

		if ($BE_USER->check('tables_modify', $this->tableName)
			&& $this->doesUserHaveAccess($pid)
			&& ($pageData['doktype'] == self::SYSFOLDER_TYPE)
		) {
			$params = '&edit['.$this->tableName.'][';

			if ($pageData['uid'] == $pid) {
				$params .= $pageData['uid'];
				$storageLabel = sprintf(
					$LANG->getLL('label_create_record_in_current_folder'),
					$pageData['title'],
					$pageData['uid']
				);
			} else {
				$storagePageData = t3lib_befunc::readPageAccess($pid, '');
				$params .= $pid;
				$storageLabel = sprintf(
					$LANG->getLL('label_create_record_in_foreign_folder'),
					$storagePageData['title'],
					$pid
				);
			}
			$params .= ']=new';
			$editOnClick = $this->editNewUrl($params, $BACK_PATH);
			$langNew = $LANG->getLL('newRecordGeneral');

			$result = TAB . TAB .
				'<div id="typo3-newRecordLink">' . LF .
				TAB . TAB . TAB .
				'<a href="' . htmlspecialchars($editOnClick) . '">' . LF .
				TAB . TAB . TAB . TAB .
				'<img' .
				t3lib_iconWorks::skinImg(
					$BACK_PATH,
					'gfx/new_record.gif',
					'width="7" height="4"'
				) .
				// We use an empty alt attribute as we already have a textual
				// representation directly next to the icon.
				' title="' . $langNew . '" alt="" />' . LF .
				TAB . TAB . TAB . TAB .
				$langNew . LF .
				TAB . TAB . TAB .
				'</a>' . LF .
				TAB . TAB .
				'</div>' . LF;

			/** @var $message t3lib_FlashMessage */
			$message = t3lib_div::makeInstance(
				't3lib_FlashMessage',
				$storageLabel,
				'',
				t3lib_FlashMessage::INFO
			);
			$this->addFlashMessage($message);

			if (class_exists('TYPO3\\CMS\\Core\\Messaging\\FlashMessageService', TRUE)) {
				/** @var $flashMessageService \TYPO3\CMS\Core\Messaging\FlashMessageService */
				$flashMessageService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessageService');
				/** @var $defaultFlashMessageQueue \TYPO3\CMS\Core\Messaging\FlashMessageQueue */
				$defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
				$renderedFlashMessages = $defaultFlashMessageQueue->renderFlashMessages();
			} else {
				$renderedFlashMessages = t3lib_FlashMessageQueue::renderFlashMessages();
			}

			$result .= '<div id="eventsList-clear"></div>' . $renderedFlashMessages;
		}

		return $result;
	}

	/**
	 * Adds a flash message to the queue.
	 *
	 * @param t3lib_FlashMessage $flashMessage
	 *
	 * @return void
	 */
	protected function addFlashMessage(t3lib_FlashMessage $flashMessage) {
		if (class_exists('TYPO3\\CMS\\Core\\Messaging\\FlashMessageService', TRUE)) {
			/** @var $flashMessageService \TYPO3\CMS\Core\Messaging\FlashMessageService */
			$flashMessageService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
				'TYPO3\\CMS\\Core\\Messaging\\FlashMessageService'
			);
			/** @var $defaultFlashMessageQueue \TYPO3\CMS\Core\Messaging\FlashMessageQueue */
			$defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
			$defaultFlashMessageQueue->enqueue($flashMessage);
		} else {
			t3lib_FlashMessageQueue::addMessage($flashMessage);
		}
	}

	/**
	 * Returns the url for the "create new record" link and the "edit record" link.
	 *
	 * @param string $params the parameters for TCE
	 * @param string $backPath the back path to the /typo3 directory
	 *
	 * @return string the URL to return
	 */
	protected function editNewUrl($params, $backPath = '') {
		$returnUrl = 'returnUrl=' . rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI'));

		return $backPath . 'alt_doc.php?' . $returnUrl . $params;
	}

	/**
	 * Returns a "CSV export" image tag that is linked to the CSV export,
	 * corresponding to the list that is visible in the BE.
	 *
	 * This icon is intended to be used next to the "create new record" icon.
	 *
	 * @return string the HTML source code of the linked CSV icon
	 */
	protected function getCsvIcon() {
		$pageData = $this->page->getPageData();
		$langCsv = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:labels.csv', 1);
		$csvUrl = t3lib_BEfunc::getModuleUrl(
			self::MODULE_NAME,
			array('id' => $pageData['uid'], 'csv' => '1', 'tx_seminars_pi2[table]' => $this->tableName)
		);

		$result = TAB . TAB .
			'<div id="typo3-csvLink">' . LF .
			TAB . TAB . TAB .
			'<a href="' . htmlspecialchars($csvUrl) .
			$this->getAdditionalCsvParameters() . '">' . LF .
			TAB . TAB . TAB . TAB .
			'<img src="/' . t3lib_extMgm::siteRelPath('seminars') . 'Resources/Public/Icons/Csv.gif" title="' .
			$langCsv . '" alt="" class="icon" />' .
			// We use an empty alt attribute as we already have a textual
			// representation directly next to the icon.
			TAB . TAB . TAB . TAB .
			$langCsv . LF .
			TAB . TAB . TAB .
			'</a>' . LF .
			TAB . TAB .
			'</div>' . LF;

		return $result;
	}

	/**
	 * Generates a linked hide or unhide icon depending on the record's hidden
	 * status.
	 *
	 * @param int $uid the UID of the record, must be > 0
	 * @param int $pageUid the PID of the record, must be >= 0
	 * @param bool $hidden
	 *        indicates whether the record is hidden (TRUE) or is visible (FALSE)
	 *
	 * @return string the HTML source code of the linked hide or unhide icon
	 */
	protected function getHideUnhideIcon($uid, $pageUid, $hidden) {
		$result = '';

		if ($GLOBALS['BE_USER']->check('tables_modify', $this->tableName)
			&& $this->doesUserHaveAccess($pageUid)
		) {
			if ($hidden) {
				$params = '&data[' . $this->tableName . '][' . $uid . '][hidden]=0';
				$icon = 'Unhide.gif';
				$langHide = $GLOBALS['LANG']->getLL('unHide');
			} else {
				$params = '&data[' . $this->tableName . '][' . $uid . '][hidden]=1';
				$icon = 'Hide.gif';
				$langHide = $GLOBALS['LANG']->getLL('hide');
			}

			$result = '<a href="' .
				htmlspecialchars($this->page->doc->issueCommand($params)) . '">' .
				'<img src="/' . t3lib_extMgm::siteRelPath('seminars') . 'Resources/Public/Icons/' . $icon .
				'" title="' . $langHide . '" alt="' . $langHide . '" class="hideicon" />' .
				'</a>';
		}

		return $result;
	}

	/**
	 * Checks if the currently logged-in BE user has access to records on the
	 * given page.
	 *
	 * @param int $pageUid the page to check the access for, must be >= 0
	 *
	 * @return bool TRUE if the user has access, FALSE otherwise
	 */
	protected function doesUserHaveAccess($pageUid) {
		if (!isset($this->accessRights[$pageUid])) {
			$this->accessRights[$pageUid] = $GLOBALS['BE_USER']
				->doesUserHaveAccess(
					t3lib_BEfunc::getRecord('pages', $pageUid), 16
			);
		}

		return $this->accessRights[$pageUid];
	}

	/**
	 * Returns the PID for new records to store.
	 *
	 * This will be determined by the storage setting of the logged-in BE-user's
	 * groups.
	 *
	 * @return int the PID for the storage of new records, will be >= 0
	 */
	protected abstract function getNewRecordPid();

	/**
	 * Gets the currently logged in back-end user.
	 *
	 * @return tx_seminars_Model_BackEndUser the currently logged in back-end
	 *                                       user
	 */
	protected function getLoggedInUser() {
		return tx_oelib_BackEndLoginManager::getInstance()->getLoggedInUser(
			'tx_seminars_Mapper_BackEndUser'
		);
	}

	/**
	 * Returns the parameters to add to the CSV icon link.
	 *
	 * @return string the additional link parameters for the CSV icon link, will
	 *                always start with an &amp and be htmlspecialchared, may
	 *                be empty
	 */
	protected function getAdditionalCsvParameters() {
		$pageData = $this->page->getPageData();

		return '&amp;tx_seminars_pi2[pid]=' . $pageData['uid'];
	}
}