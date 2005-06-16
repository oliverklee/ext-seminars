<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Class 'tx_seminars_templatehelper' for the 'seminars' extension.
 * 
 * This utitity class provides some commonly-used functions for handling templates
 * (in addition to all functionality provided by the base classes).
 * 
 * This is an abstract class; don't instantiate it.
 *
 * @author	Oliver Klee <typo-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_dbplugin.php');

class tx_seminars_templatehelper extends tx_seminars_dbplugin {
	/** the HTML template subparts */
	var $templateCache = array();

	/** list of subpart names that shouldn't be displayed in the detailed view;
	    set a subpart key like '###FIELD_DATE###' and the value to '' to remove that subpart */
	var $subpartsToHide = array();

	/**
	 * Dummy constructor: Does nothing.
	 * 
	 * Call $this->init() instead.
	 * 
	 * @access public
	 */
	function tx_seminars_templatehelper() {
	}

	/**
	 * Retrieves the subparts from the plugin template and write them to $this->templateCache.
	 * 
	 * @param	array		array with strings for the subpart markers to retrieve,
	 * 						e.g. 'SIGN_IN_VIEW'
	 * 
	 * @access protected
	 */
	function getTemplateCode($subpartNames) {
		/** the whole template file as a string */
		$templateRawCode = $this->cObj->fileResource($this->conf['templateFile']);

		foreach ($subpartNames as $currentKey) {
			$this->templateCache[$currentKey] = $this->cObj->getSubpart($templateRawCode, '###'.$currentKey.'###');
		}
	} 

	/**
	 * Takes a comma-separated list of subpart names and writes them to $this->subpartsToHide.
	 * In the process, the names are changed from 'aname' to '###BLA_ANAME###' and used as keys.
	 * The corresponding values in the array are empty strings.
	 * 
	 * Example: If the prefix is "FIELD" and the list is "one,two", the array keys
	 * "###FIELD_ONE###" and "###FIELD_TWO###" will be written.
	 * 
	 * @param	String		prefix to the subpart names, must be uppercase
	 * @param	String		comma-separated list of subpart names to hide (case-insensitive)
	 * 
	 * @access protected
	 */
	function readSubpartsToHide($prefix, $subparts) {
		$subpartNames = explode(',', $subparts);
		
		foreach ($subpartNames as $currentSubpartName) {
			$this->subpartsToHide['###'.$prefix.'_'.strtoupper(trim($currentSubpartName)).'###'] = '';
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_templatehelper.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_templatehelper.php']);
}
