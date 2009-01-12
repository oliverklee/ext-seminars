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

require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');

/**
 * Class 'registrations list' for the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_mod2_registrationslist extends tx_seminars_mod2_backendlist {
	/**
	 * @var string the table we're working on
	 */
	protected $tableName = SEMINARS_TABLE_ATTENDANCES;

	/**
	 * @var string warnings from the registration bag configcheck
	 */
	private $configCheckWarnings = '';

	/**
	 * Generates and prints out a registrations list.
	 *
	 * @return string the HTML source code to display
	 */
	public function show() {
		// Initializes the variable for the HTML source code.
		$content = '';

		$pageData = $this->page->getPageData();
		$content .= $this->getNewIcon($pageData['uid']);

		// Generates the table with the regular attendances.
		$content .= TAB . TAB . '<div style="clear: both;"></div>' . LF;
		$content .= $this->getRegistrationTable(false);

		// Generates the table with the attendances on the registration queue.
		$content .= $this->getRegistrationTable(true);

		$content .= $this->configCheckWarnings;

		return $content;
	}

	/**
	 * Gets the registration table for regular attendances and attendances on
	 * the registration queue.
	 *
	 * @param boolean True if the registration table for the registration
	 *                queue should be generated and false if the table for
	 *                the regular attendances should be generated.
	 *
	 * @return string the registration table, nicely formatted as HTML
	 */
	private function getRegistrationTable($showRegistrationQueue) {
		global $LANG;

		$content = '';

		// Sets the table layout of the registration list.
		$tableLayout = array(
			'table' => array(
				TAB . TAB .
					'<table cellpadding="0" cellspacing="0" class="typo3-dblist">' .
					LF,
				TAB . TAB .
					'</table>' . LF,
			),
			array(
				'tr' => array(
					TAB . TAB . TAB .
						'<thead>' . LF .
						TAB . TAB . TAB . TAB .
						'<tr>' . LF,
					TAB . TAB . TAB . TAB .
						'</tr>' . LF .
						TAB . TAB . TAB .
						'</thead>' . LF,
				),
				'defCol' => array(
					TAB . TAB . TAB . TAB . TAB .
						'<td class="c-headLineTable">' . LF,
					TAB . TAB . TAB . TAB . TAB .
						'</td>' . LF,
				),
			),
			'defRow' => array(
				'tr' => array(
					TAB . TAB . TAB .
						'<tr>' . LF,
					TAB . TAB . TAB .
						'</tr>' . LF,
				),
				'defCol' => array(
					TAB . TAB . TAB . TAB .
						'<td>' . LF,
					TAB . TAB . TAB . TAB .
						'</td>' . LF,
				),
			),
		);

		// Fills the first row of the table array with the header.
		$table = array(
			array(
				'',
				TAB . TAB . TAB . TAB . TAB . TAB .
					'<span style="color: #ffffff; font-weight: bold;">' .
					$LANG->getLL('registrationlist.feuser.name') . '</span>' . LF,
				TAB . TAB . TAB . TAB . TAB . TAB .
					'<span style="color: #ffffff; font-weight: bold;">' .
					$LANG->getLL('registrationlist.seminar.accreditation_number') . '</span>' . LF,
				TAB . TAB . TAB . TAB . TAB . TAB .
					'<span style="color: #ffffff; font-weight: bold;">' .
					$LANG->getLL('registrationlist.seminar.title') . '</span>' . LF,
				TAB . TAB . TAB . TAB . TAB . TAB .
					'<span style="color: #ffffff; font-weight: bold;">' .
					$LANG->getLL('registrationlist.seminar.date') . '</span>' . LF,
				TAB . TAB . TAB . TAB . TAB . TAB .
					'&nbsp;' . LF,
			),
		);

		$registrationBagBuilder = t3lib_div::makeInstance(
			'tx_seminars_registrationBagBuilder'
		);
		$pageData = $this->page->getPageData();
		$registrationBagBuilder->setSourcePages($pageData['uid']);
		if ($showRegistrationQueue) {
			$registrationBagBuilder->limitToOnQueue();
		} else {
			$registrationBagBuilder->limitToRegular();
		}

		$registrationBag = $registrationBagBuilder->build();

		$labelHeading = ($showRegistrationQueue)
			? $LANG->getLL('registrationlist.label_queueRegistrations')
			: $LANG->getLL('registrationlist.label_regularRegistrations');

		$content .= TAB . TAB . '<h3>' .
			$labelHeading .
			' (' . $registrationBag->count() . ')' .
			'</h3>' . LF;

		foreach ($registrationBag as $registration) {
			try {
				$userName = $registration->getUserName();
			} catch (tx_oelib_Exception_NotFound $e) {
				$userName = $LANG->getLL('registrationlist.deleted');
			}
			$event = $registration->getSeminarObject();
			if ($event->isOk()) {
				$eventTitle = htmlspecialchars($event->getTitle());
				$eventDate = $event->getDate();
				$accreditationNumber = htmlspecialchars(
					$event->getAccreditationNumber()
				);
			} else {
				$eventTitle = $LANG->getLL('registrationlist.deleted');
				$eventDate = '';
				$accreditationNumber = '';
			}
			// Adds the result row to the table array.
			$table[] = array(
				TAB . TAB . TAB . TAB . TAB .
					$registration->getRecordIcon() . LF,
				TAB . TAB . TAB . TAB . TAB .
					$userName . LF,
				TAB . TAB . TAB . TAB . TAB .
					$accreditationNumber . LF,
				TAB . TAB . TAB . TAB . TAB .
					$eventTitle . LF,
				TAB . TAB . TAB . TAB . TAB .
					$eventDate . LF,
				TAB . TAB . TAB . TAB . TAB .
					$this->getEditIcon(
						$registration->getUid()
					) .
					$this->getDeleteIcon(
						$registration->getUid()
					) . LF,
			);
		}

		if ($this->configCheckWarnings == '') {
			$this->configCheckWarnings =
				$registrationBag->checkConfiguration();
		}

		// Outputs the table array using the tableLayout array with the template
		// class.
		$content .= $this->page->doc->table($table, $tableLayout);

		return $content;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/mod2/class.tx_seminars_mod2_registrationslist.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/mod2/class.tx_seminars_mod2_registrationslist.php']);
}
?>