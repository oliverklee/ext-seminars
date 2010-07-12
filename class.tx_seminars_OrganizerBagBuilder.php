<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2010 Niels Pardon (mail@niels-pardon.de)
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
 * Class 'tx_seminars_OrganizerBagBuilder' for the 'seminars' extension.
 *
 * This builder class creates customized organizerbag objects.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_OrganizerBagBuilder extends tx_seminars_bagbuilder {
	/**
	 * @var string class name of the bag class that will be built
	 */
	protected $bagClassName = 'tx_seminars_Bag_Organizer';

	/**
	 * @var string the table name of the bag to build
	 */
	protected $tableName = 'tx_seminars_organizers';

	/**
	 * Limits the bag to contain only organizers of the event given in the
	 * parameter $eventUid (must be a single or date event as topic events don't
	 * have any organizers).
	 *
	 * @param integer the event UID to limit the organizers for, must be > 0
	 */
	public function limitToEvent($eventUid) {
		if ($eventUid <= 0) {
			throw new Exception('The parameter $eventUid must be > 0.');
		}

		$this->whereClauseParts['event'] = 'EXISTS (' .
			'SELECT * FROM tx_seminars_seminars_organizers_mm' .
			' WHERE uid_local = ' . $eventUid . ' AND uid_foreign = ' .
			'tx_seminars_organizers.uid)';
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_OrganizerBagBuilder.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_OrganizerBagBuilder.php']);
}
?>