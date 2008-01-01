<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2008 Mario Rimann (typo3-coding@rimann.org)
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
 * Class 'tx_seminars_dbpluginchild' for the 'seminars' extension.
 *
 * This is mere a class used for unit tests of the 'seminars' extension. Don't
 * use it for any other purpose.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 * @author		Mario Rimann <typo3-coding@rimann.org>
 */

require_once(PATH_tslib.'class.tslib_content.php');
require_once(PATH_t3lib.'class.t3lib_timetrack.php');

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_dbplugin.php');

final class tx_seminars_dbpluginchild extends tx_seminars_dbplugin {
	public $prefixId = 'tx_seminars_dbpluginchild';
	public $scriptRelPath
		= 'tests/fixtures/class.tx_seminars_dbpluginchild.php';
	public $extKey = 'seminars';

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
	}

	/**
	 * Sets the current language for this plugin and loads the language files.
	 *
	 * @param	string		two-letter lowercase language like "en" or "de"
	 * 						or "default" (which is an alias for "en")
	 */
	public function setLanguage($language) {
		if ($this->getLanguage() != $language) {
			// Make sure the language file are reloaded.
			$this->LOCAL_LANG_loaded = false;
			$this->LLkey = $language;
		}

		$this->pi_loadLL();
	}

	/**
	 * Gets the current language.
	 *
	 * @return	string		the two-letter key of the current language like "en",
	 * 						"de" or "default" (which is the only non-two-letter
	 *						code and an alias for "en"), will return an empty
	 *						string if no language key has been set yet
	 */
	public function getLanguage() {
		return $this->LLkey;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminarst/tests/fixtures/class.tx_seminars_dbpluginchild.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/tests/fixtures/class.tx_seminars_dpbluginchild.php']);
}

?>
