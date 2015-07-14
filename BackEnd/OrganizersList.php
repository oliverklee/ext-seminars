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

/**
 * This class creates an organizer list in the back end.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_seminars_BackEnd_OrganizersList extends tx_seminars_BackEnd_AbstractList {
	/**
	 * @var string the name of the table we're working on
	 */
	protected $tableName = 'tx_seminars_organizers';

	/**
	 * @var tx_seminars_OldModel_Organizer the organizer which we want to list
	 */
	private $organizer = NULL;

	/**
	 * @var string the path to the template file of this list
	 */
	protected $templateFile = 'EXT:seminars/Resources/Private/Templates/BackEnd/OrganizersList.html';

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		unset($this->organizer);

		parent::__destruct();
	}


	/**
	 * Generates and prints out a organizers list.
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
			'label_full_name', $GLOBALS['LANG']->getLL('organizerlist.title')
		);

		/** @var tx_seminars_BagBuilder_Organizer $builder */
		$builder = t3lib_div::makeInstance('tx_seminars_BagBuilder_Organizer');

		$builder->setSourcePages($pageData['uid'], self::RECURSION_DEPTH);

		$organizerBag = $builder->build();

		$tableRows = '';

		/** @var tx_seminars_OldModel_Organizer $organizerBag */
		foreach ($organizerBag as $this->organizer) {
			$this->template->setMarker(
				'icon', $this->organizer->getRecordIcon()
			);
			$this->template->setMarker(
				'full_name', htmlspecialchars($this->organizer->getTitle())
			);
			$this->template->setMarker(
				'edit_button',
				$this->getEditIcon(
					$this->organizer->getUid(), $this->organizer->getPageUid()
				)
			);
			$this->template->setMarker(
				'delete_button',
				$this->getDeleteIcon(
					$this->organizer->getUid(), $this->organizer->getPageUid()
				)
			);

			$tableRows .= $this->template->getSubpart('ORGANIZER_ROW');
		}
		$this->template->setSubpart('ORGANIZER_ROW', $tableRows);
		$this->template->setMarker(
			'label_print_button', $GLOBALS['LANG']->getLL('print')
		);

		$content .= $this->template->getSubpart('SEMINARS_ORGANIZER_LIST');

		$content .= $organizerBag->checkConfiguration();

		return $content;
	}

	/**
	 * Returns the storage folder for new organizer records.
	 *
	 * This will be determined by the auxiliary folder storage setting of the
	 * currently logged-in BE-user.
	 *
	 * @return int the PID for new organizer records, will be >= 0
	 */
	protected function getNewRecordPid() {
		return $this->getLoggedInUser()->getAuxiliaryRecordsFolder();
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/BackEnd/OrganizersList.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/BackEnd/OrganizersList.php']);
}