<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2012 Niels Pardon (mail@niels-pardon.de)
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
 * Class 'speakers list' for the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_seminars_BackEnd_SpeakersList extends tx_seminars_BackEnd_AbstractList {
	/**
	 * @var string the name of the table we're working on
	 */
	protected $tableName = 'tx_seminars_speakers';

	/**
	 * @var tx_seminars_speaker the speaker which we want to list
	 */
	private $speaker = NULL;

	/**
	 * @var string the path to the template file of this list
	 */
	protected $templateFile = 'EXT:seminars/Resources/Private/Templates/BackEnd/SpeakersList.html';

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		if ($this->speaker) {
			$this->speaker->__destruct();
			unset($this->speaker);
		}

		parent::__destruct();
	}

	/**
	 * Generates and prints out a speakers list.
	 *
	 * @return string the HTML source code to display
	 */
	public function show() {
		$content = '';

		$pageData = $this->page->getPageData();

		$this->template->setMarker(
			'new_record_button', $this->getNewIcon($pageData['uid'])
		);

		$this->template->setMarker(
			'label_full_name', $GLOBALS['LANG']->getLL('speakerlist.title')
		);
		$this->template->setMarker(
			'label_skills', $GLOBALS['LANG']->getLL('speakerlist.skills')
		);

		$builder = tx_oelib_ObjectFactory::make('tx_seminars_BagBuilder_Speaker');
		$builder->showHiddenRecords();

		$builder->setSourcePages($pageData['uid'], self::RECURSION_DEPTH);

		$speakerBag = $builder->build();

		$tableRows = '';

		foreach ($speakerBag as $this->speaker) {
			$this->template->setMarker(
				'icon', $this->speaker->getRecordIcon()
			);
			$this->template->setMarker(
				'full_name', htmlspecialchars($this->speaker->getTitle())
			);
			$this->template->setMarker(
				'edit_button',
				$this->getEditIcon(
					$this->speaker->getUid(), $this->speaker->getPageUid()
				)
			);
			$this->template->setMarker(
				'delete_button',
				$this->getDeleteIcon(
					$this->speaker->getUid(), $this->speaker->getPageUid()
				)
			);
			$this->template->setMarker(
				'hide_unhide_button',
				$this->getHideUnhideIcon(
					$this->speaker->getUid(),
					$this->speaker->getPageUid(),
					$this->speaker->isHidden()
				)
			);
			$this->template->setMarker(
				'skills', htmlspecialchars($this->speaker->getSkillsShort())
			);

			$tableRows .= $this->template->getSubpart('SPEAKER_ROW');
		}

		$this->template->setSubpart('SPEAKER_ROW', $tableRows);
		$this->template->setMarker(
			'label_print_button', $GLOBALS['LANG']->getLL('print')
		);
		$content .= $this->template->getSubpart('SEMINARS_SPEAKER_LIST');

		$content .= $speakerBag->checkConfiguration();

		$speakerBag->__destruct();

		return $content;
	}

	/**
	 * Returns the storage folder for new speaker records.
	 *
	 * This will be determined by the auxiliary folder storage setting of the
	 * currently logged-in BE-user.
	 *
	 * @return integer the PID for new speaker records, will be >= 0
	 */
	protected function getNewRecordPid() {
		return $this->getLoggedInUser()->getAuxiliaryRecordsFolder();
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/BackEnd/SpeakersList.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/BackEnd/SpeakersList.php']);
}
?>