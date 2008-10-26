<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2008 Niels Pardon (mail@niels-pardon.de)
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

require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');

/**
 * Class 'back-end list' for the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_backendlist {
	/** the table we're working on */
	protected $tableName = '';

	/** Holds a reference to the back-end page object. */
	protected $page = null;

	/**
	 * The constructor. Sets the table name and the back-end page object.
	 *
	 * @param t3lib_SCbase the current back-end page object
	 *
	 * @access public
	 */
	function __construct(t3lib_SCbase $page) {
		$this->page = $page;
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		unset($this->page);
	}

	/**
	 * Generates an edit record icon which is linked to the edit view of
	 * a record.
	 *
	 * @param integer the UID of the record, must be > 0
	 *
	 * @return string the HTML source code to return
	 *
	 * @access public
	 */
	function getEditIcon($uid) {
		global $BACK_PATH, $LANG, $BE_USER;

		$result = '';

		if ($BE_USER->check('tables_modify', $this->tableName)
			&& $BE_USER->doesUserHaveAccess(
				t3lib_BEfunc::getRecord(
					'pages',
					$this->page->pageInfo['uid']
				),
			16)
		) {
			$params = '&edit['.$this->tableName.']['.$uid.']=edit';
			$editOnClick = $this->editNewUrl($params, $BACK_PATH);
			$langEdit = $LANG->getLL('edit');
			$result = '<a href="'.htmlspecialchars($editOnClick).'">'
				.'<img '
				.t3lib_iconWorks::skinImg(
					$BACK_PATH,
					'gfx/edit2.gif',
					'width="11" height="12"')
				.' title="'.$langEdit.'" alt="'.$langEdit.'" class="icon" />'
				.'</a>';
		}

		return $result;
	}

	/**
	 * Generates a linked delete record icon whith a JavaScript confirmation
	 * window.
	 *
	 * @param integer the UID of the record, must be > 0
	 *
	 * @return string the HTML source code to return
	 *
	 * @access public
	 */
	function getDeleteIcon($uid) {
		global $BACK_PATH, $LANG, $BE_USER;

		$result = '';

		if ($BE_USER->check('tables_modify', $this->tableName)
			&& $BE_USER->doesUserHaveAccess(
				t3lib_BEfunc::getRecord(
					'pages',
					$this->page->pageInfo['uid']
				),
				16
			)
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
			$result = '<a href="'
				.htmlspecialchars($this->page->doc->issueCommand($params))
				.'" onclick="'.$confirmation.'">'
				.'<img'
				.t3lib_iconWorks::skinImg(
					$BACK_PATH,
					'gfx/garbage.gif',
					'width="11" height="12"'
				)
				.' title="'.$langDelete.'" alt="'.$langDelete
				.'" class="deleteicon" /></a>';
		}

		return $result;
	}

	/**
	 * Returns a "create new record" image tag that is linked to the new record view.
	 *
	 * @param string the name of the table where the record should be saved to
	 * @param integer the page ID where the record should be stored, must be > 0
	 *
	 * @return string the HTML source code to return
	 *
	 * @access public
	 */
	function getNewIcon($pid) {
		global $BACK_PATH, $LANG, $BE_USER;

		$result = '';

		if ($BE_USER->check('tables_modify', $this->tableName)
			&& $BE_USER->doesUserHaveAccess(
				t3lib_BEfunc::getRecord(
					'pages',
					$this->page->pageInfo['uid']
				),
				16)
			&& $this->page->pageInfo['doktype'] == 254) {
			$params = '&edit['.$this->tableName.']['.$pid.']=new';
			$editOnClick = $this->editNewUrl($params, $BACK_PATH);
			$langNew = $LANG->getLL('newRecordGeneral');
			$result = TAB.TAB
				.'<div id="typo3-newRecordLink">'.LF
				.TAB.TAB.TAB
				.'<a href="'.htmlspecialchars($editOnClick).'">'.LF
				.TAB.TAB.TAB.TAB
				.'<img'
				.t3lib_iconWorks::skinImg(
					$BACK_PATH,
					'gfx/new_record.gif',
					'width="7" height="4"')
				// We use an empty alt attribute as we already have a textual
				// representation directly next to the icon.
				.' title="'.$langNew.'" alt="" />'.LF
				.TAB.TAB.TAB.TAB
				.$langNew.LF
				.TAB.TAB.TAB
				.'</a>'.LF
				.TAB.TAB
				.'</div>'.LF;
		}

		return $result;
	}

	/**
	 * Returns the url for the "create new record" link and the "edit record" link.
	 *
	 * @param string the parameters for TCE
	 * @param string the back path to the /typo3 directory
	 *
	 * @return string the URL to return
	 *
	 * @access protected
	 */
	function editNewUrl($params, $backPath = '') {
		$returnUrl = 'returnUrl=' .
			rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI'));

		return $backPath . 'alt_doc.php?' . $returnUrl . $params;
	}

	/**
	 * Returns a "CSV export" image tag that is linked to the CSV export,
	 * corresponding to the list that is visible in the BE.
	 *
	 * This icon is intended to be used next to the "create new record" icon.
	 *
	 * @param string the name of the table from which the records should be
	 *               exported, eg. "tx_seminars_seminars"
	 *
	 * @return string the HTML source code of the linked CSV icon
	 *
	 * @access protected
	 */
	function getCsvIcon() {
		global $BACK_PATH, $LANG;

		$langCsv = $LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.csv', 1);
		$result = TAB.TAB
			.'<div id="typo3-csvLink">'.LF
			.TAB.TAB.TAB
			.'<a href="class.tx_seminars_csv.php?id='.$this->page->pageInfo['uid']
			.'&amp;tx_seminars_pi2[table]='.$this->tableName
			.'&amp;tx_seminars_pi2[pid]='.$this->page->pageInfo['uid'].'">'.LF
			.TAB.TAB.TAB.TAB
			.'<img'
			.t3lib_iconWorks::skinImg(
				$BACK_PATH,
				'gfx/csv.gif',
				'width="27" height="14"'
			)
			// We use an empty alt attribute as we already have a textual
			// representation directly next to the icon.
			.' title="'.$langCsv.'" alt="" />'.LF
			.TAB.TAB.TAB.TAB
			.$langCsv.LF
			.TAB.TAB.TAB
			.'</a>'.LF
			.TAB.TAB
			.'</div>'.LF;

		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/mod2/class.tx_seminars_backendlist.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/mod2/class.tx_seminars_backendlist.php']);
}
?>