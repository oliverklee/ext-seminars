<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2009 Niels Pardon (mail@niels-pardon.de)
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
 * Class 'organizers list' for the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_seminars_BackEnd_OrganizersList extends tx_seminars_BackEnd_List {
	/**
	 * @var string the name of the table we're working on
	 */
	protected $tableName = 'tx_seminars_organizers';

	/**
	 * @var tx_seminars_organizer the organizer which we want to list
	 */
	private $organizer = null;

	/**
	 * @var string the path to the template file of this list
	 */
	protected $templateFile = 'EXT:seminars/Resources/Private/Templates/BackEnd/OrganizersList.html';

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		if ($this->organizer) {
			$this->organizer->__destruct();
			unset($this->organizer);
		}

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

		$builder = tx_oelib_ObjectFactory::make('tx_seminars_OrganizerBagBuilder');

		$builder->setSourcePages($pageData['uid'], self::RECURSION_DEPTH);

		$organizerBag = $builder->build();

		$tableRows = '';

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

		$content .= $this->template->getSubpart('SEMINARS_ORGANIZER_LIST');

		$content .= $organizerBag->checkConfiguration();
		$organizerBag->__destruct();

		return $content;
	}

	/**
	 * Returns the storage folder for new organizer records.
	 *
	 * This will be determined by the auxiliary folder storage setting of the
	 * currently logged-in BE-user.
	 *
	 * @return integer the PID for new organizer records, will be >= 0
	 */
	protected function getNewRecordPid() {
		return $this->getLoggedInUser()->getAuxiliaryRecordsFolder();
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/BackEnd/class.tx_seminars_BackEnd_OrganizersList.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/BackEnd/class.tx_seminars_BackEnd_OrganizersList.php']);
}
?>