<?php
/***************************************************************
* Copyright notice
*
* (c) 2007 Niels Pardon (mail@niels-pardon.de)
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
 * Class 'tx_seminars_registrationmanagerchild' for the 'seminars' extension.
 *
 * This is mere a class used for unit tests of the 'seminars' extension. Don't
 * use it for any other purpose.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 * @author		Niels Pardon <mail@niels-pardon.de>
 */

require_once(t3lib_extMgm::extPath('seminars')
	.'class.tx_seminars_registrationmanager.php');

final class tx_seminars_registrationmanagerchild extends tx_seminars_registrationmanager {
	public $prefixId = 'tx_seminars_registrationmanagerchild';
	public $scriptRelPath
		= 'tests/fixtures/class.tx_seminars_registrationmanagerchild.php';

	/**
	 * The constructor.
	 *
	 * @param	array	TS setup configuration array, may be empty
	 */
	public function __construct(array $configuration) {
		// Call the base classe's constructor manually as this isn't done
		// automatically.
		parent::tslib_pibase();

		$this->conf = $configuration;

		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		$this->setTableNames();
		$this->setRecordTypes();
	}

	/**
	 * Sets the TypoScript configuration for the parameter
	 * unregistrationDeadlineDaysBeforeBeginDate.
	 *
	 * @param	integer		days before the begin date until unregistration
	 *						should be possible
	 */
	public function setGlobalUnregistrationDeadline($days) {
		$this->conf['unregistrationDeadlineDaysBeforeBeginDate'] = $days;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminarst/tests/fixtures/class.tx_seminars_registrationmanagerchild.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/tests/fixtures/class.tx_seminars_registrationmanagerchild.php']);
}

?>
