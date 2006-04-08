<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005-2006 Oliver Klee (typo3-coding@oliverklee.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Class 'tx_seminars_salutationswitcher' for the 'seminars' extension
 * (taken from the 'salutationswitcher' extension).
 *
 * @author	Oliver Klee <typo3-coding@oliverklee.de>
 */

// In case we're on the back end, PATH_tslib isn't defined yet.
if (!defined('PATH_tslib')) {
	define('PATH_tslib', PATH_site.'tslib/');
}
require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_seminars_salutationswitcher extends tslib_pibase {
	/** list of allowed suffixes */
	var $allowedSuffixes = array('formal', 'informal');

	/**
	 * Returns the localized label of the LOCAL_LANG key, $key
	 * In $this->conf['salutation'], a suffix to the key may be set (which may be either 'formal' or 'informal').
	 * If a corresponding key exists, the formal/informal localized string is used instead.
	 * If the key doesn't exist, we just use the normal string.
	 *
	 * Example: key = 'greeting', suffix = 'informal'. If the key 'greeting_informal' exists, that string is used.
	 * If it doesn't exist, we'll try to use the string with the key 'greeting'.
	 *
	 * Notice that for debugging purposes prefixes for the output values can be set with the internal vars ->LLtestPrefixAlt and ->LLtestPrefix
	 *
	 * @param	string		The key from the LOCAL_LANG array for which to return the value.
	 * @param	string		Alternative string to return IF no value is found set for the key, neither for the local language nor the default.
	 * @param	boolean		If true, the output label is passed through htmlspecialchars()
	 * @return	string		The value from LOCAL_LANG.
	 */
	function pi_getLL($key, $alt = '', $hsc = FALSE) {
		// If the suffix is allowed and
		// we have a localized string for the desired salutation, we'll take that.
		if (isset($this->conf['salutation']) && in_array($this->conf['salutation'], $this->allowedSuffixes, 1)) {
			// Rewrite the language key to 'default' if it is 'en'. Otherwise, it will not work if language = English.
			if ($this->LLkey == 'en')	{
				$internal_LL_key = 'default';
			} else	{
				$internal_LL_key = $this->LLkey;
			}

			$expandedKey = $key.'_'.$this->conf['salutation'];

			if (isset($this->LOCAL_LANG[$internal_LL_key][$expandedKey])) {
				$key = $expandedKey;
			}
		}

		return parent::pi_getLL($key, $alt, $hsc);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_salutationswitcher.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_salutationswitcher.php']);
}

?>