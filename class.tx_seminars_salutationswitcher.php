<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2008 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Class 'tx_seminars_salutationswitcher' for the 'seminars' extension
 * (taken from the 'salutationswitcher' extension).
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

// In case we're on the back end, PATH_tslib isn't defined yet.
if (!defined('PATH_tslib')) {
	define('PATH_tslib', t3lib_extMgm::extPath('cms').'tslib/');
}
require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_seminars_salutationswitcher extends tslib_pibase {
	/**
	  * Pointer to alternative fall-back language to use. This is non-empty so
	  * we always have a valid fallback language even if it hasn't been
	  * explicitely set.
	  */
	var $altLLkey = 'default';

	/**
	 * A list of language keys for which the localizations have been loaded
	 * (or null if the list has not been compiled yet).
	 */
	var $availableLanguages = null;

	/**
	 * An ordered list of language label suffixes that should be tried to get
	 * localizations in the preferred order of formality (or null if the list
	 * has not been compiled yet).
	 */
	var $suffixesToTry = null;

	/**
	 * Returns the localized label of the LOCAL_LANG key, $key
	 * In $this->conf['salutation'], a suffix to the key may be set (which may
	 * be either 'formal' or 'informal'). If a corresponding key exists, the
	 * formal/informal localized string is used instead.
	 * If the key doesn't exist, we just use the normal string.
	 *
	 * Example: key = 'greeting', suffix = 'informal'. If the key
	 * 'greeting_informal' exists, that string is used.
	 * If it doesn't exist, we'll try to use the string with the key 'greeting'.
	 *
	 * Note: Although this function is deprecated, it must not be removed
	 * because some pi_base functions rely on its existence.
	 *
	 * @param	string		the key from the LOCAL_LANG array for which to
	 *						return the value
	 * @param	string		(unused, for legacy purposes)
	 * @param	boolean		whether the output label should be passed through
	 *				 		htmlspecialchars()
	 *
	 * @return	string		the value from LOCAL_LANG
	 *
	 * @deprecated	2007-08-22	Use translate instead.
	 */
	public function pi_getLL($key, $unused = '', $useHtmlSpecialChars = false) {
		return $this->translate($key, $useHtmlSpecialChars);
	}

	/**
	 * Returns the localized label of the LOCAL_LANG key, $key
	 * In $this->conf['salutation'], a suffix to the key may be set (which may
	 * be either 'formal' or 'informal'). If a corresponding key exists, the
	 * formal/informal localized string is used instead.
	 * If the formal/informal key doesn't exist, we just use the normal string.
	 *
	 * Example: key = 'greeting', suffix = 'informal'. If the key
	 * 'greeting_informal' exists, that string is used.
	 * If it doesn't exist, we'll try to use the string with the key 'greeting'.
	 *
	 * @param	string		the key from the LOCAL_LANG array for which to
	 *						return the value
	 * @param	boolean		whether the output label should be passed through
	 *						htmlspecialchars()
	 *
	 * @return	string		the value from LOCAL_LANG
	 */
	public function translate($key, $useHtmlSpecialChars = false) {
		$hasFoundATranslation = false;

		$availableLanguages = $this->getAvailableLanguages();
		$suffixesToTry = $this->getSuffixesToTry();

		foreach ($availableLanguages as $language) {
			foreach ($suffixesToTry as $suffix) {
				$completeKey = $key.$suffix;
				if (isset($this->LOCAL_LANG[$language][$completeKey])) {
					$result = parent::pi_getLL($completeKey);
					$hasFoundATranslation = true;
					break 2;
				}
			}
		}

		// If still nothing has been found, just return the key.
		if (!$hasFoundATranslation) {
			$result = $key;
		}

		if ($useHtmlSpecialChars) {
			$result = htmlspecialchars($result);
		}

		return $result;
	}

	/**
	 * Compiles a list of language keys for which localizations have been loaded.
	 *
	 * @return	array		a list of language keys (may be empty)
	 *
	 * @access	private
	 */
	function getAvailableLanguages() {
		if ($this->availableLanguages === null) {
			$this->availableLanguages = array();

			if (!empty($this->LLkey)) {
				$this->availableLanguages[] = $this->LLkey;
			}
			if (!empty($this->altLLkey)) {
				$this->availableLanguages[] = $this->altLLkey;
			}
			// The key for English is "default", not "en".
			$this->availableLanguages = preg_replace(
				'/en/', 'default', $this->availableLanguages
			);
			// Remove duplicates in case the default language is the same as the
			// fall-back language.
			$this->availableLanguages = array_unique($this->availableLanguages);

			// Now check that we only keep languages for which we have
			// translations.
			foreach ($this->availableLanguages as $index => $code) {
				if (!isset($this->LOCAL_LANG[$code])) {
					unset($this->availableLanguages[$index]);
				}
			}
		}

		return $this->availableLanguages;
	}

	/**
	 * Gets an ordered list of language label suffixes that should be tried to
	 * get localizations in the preferred order of formality.
	 *
	 * @return	array		ordered list of suffixes from "", "_formal" and "_informal", will not be empty
	 *
	 * @access	private
	 */
	function getSuffixesToTry() {
		if ($this->suffixesToTry === null) {
			$this->suffixesToTry = array();

			if (isset($this->conf['salutation'])
				&& ($this->conf['salutation'] == 'informal')) {
				$this->suffixesToTry[] = '_informal';
			}
			$this->suffixesToTry[] = '_formal';
			$this->suffixesToTry[] = '';
		}

		return $this->suffixesToTry;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_salutationswitcher.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_salutationswitcher.php']);
}

?>
