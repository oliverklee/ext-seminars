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
 * Class 'tx_seminars_templatehelper' for the 'seminars' extension.
 *
 * This utitity class provides some commonly-used functions for handling templates
 * (in addition to all functionality provided by the base classes).
 *
 * This is an abstract class; don't instantiate it.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 *
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_dbplugin.php');

class tx_seminars_templatehelper extends tx_seminars_dbplugin {
	/** the complete HTML template */
	var $templateCode = '';

	/**
	 * Associative array of all HTML template subparts, using the marker names
	 * without ### as keys, for example 'MY_MARKER'.
	 */
	var $templateCache = array();

	/**
	 * List of subpart names that shouldn't be displayed. Set a subpart key like
	 * "FIELD_DATE" (the value does not matter) to remove that subpart.
	 */
	var $subpartsToHide = array();

	/**
	 * Associative array of populated markers and their contents (with the keys
	 * being the marker names including the wrapping hash signs ###).
	 */
	var $markers = array();

	/** list of the names of all markers (and subparts) of a template */
	var $markerNames = '';

	/**
	 * Retrieves the plugin template file set in $this->conf['templateFile'] (or
	 * also via flexforms if TYPO3 mode is FE) and writes it to
	 * $this->templateCode. The subparts will be written to $this->templateCache.
	 *
	 * @param	boolean		whether the settings in the Flexform should be
	 * 						ignored
	 *
	 * @access	protected
	 */
	function getTemplateCode($ignoreFlexform = false) {
		// Trying to fetch the template code via $this->cObj in BE mode leads to
		// a non-catchable error in the tslib_content class because the cObj
		// configuration array is not initialized properly.
		// As flexforms can be used in FE mode only, $ignoreFlexform is set true
		// if we are in the BE mode. By this, $this->cObj->fileResource can be
		// sheltered from being called.
		if (TYPO3_MODE == 'BE') {
			$ignoreFlexform = true;
		}

		$templateFileName = $this->getConfValueString(
			'templateFile',
			's_template_special',
			true,
			$ignoreFlexform
		);

		if (!$ignoreFlexform) {
			$templateRawCode = $this->cObj->fileResource($templateFileName);
		} else {
			// If there is no need to care about flexforms, the template file is
			// fetched directly from the local configuration array.
			$templateRawCode = file_get_contents(
				t3lib_div::getFileAbsFileName($templateFileName)
			);
		}

		$this->processTemplate($templateRawCode);
	}

	/**
	 * Stores the given HTML template and retrieves all subparts, writing them
	 * to $this->templateCache.
	 *
	 * The subpart names are automatically retrieved from $templateRawCode and
	 * are used as array keys. For this, the ### are removed, but the names stay
	 * uppercase.
	 *
	 * Example: The subpart ###MY_SUBPART### will be stored with the array key
	 * 'MY_SUBPART'.
	 *
	 * @param	string		the content of the HTML template
	 *
	 * @access	protected
	 */
	function processTemplate($templateRawCode) {
		$this->templateCode = $templateRawCode;
		$this->markerNames = $this->findMarkers();

		$subpartNames = $this->findSubparts();

		foreach ($subpartNames as $subpartName) {
			$matches = array();
			preg_match(
				'/<!-- *###'.$subpartName.'### *-->(.*)'
					.'<!-- *###'.$subpartName.'### *-->/msSU',
				$templateRawCode,
				$matches
			);
			if (isset($matches[1])) {
				$this->templateCache[$subpartName] = $matches[1];
			}
		}
	}

	/**
	 * Finds all subparts within the current HTML template.
	 * The subparts must be within HTML comments.
	 *
	 * @return	array		a list of the subpart names (uppercase, without ###,
	 *						for example 'MY_SUBPART')
	 *
	 * @access	protected
	 */
	function findSubparts() {
		$matches = array();
		preg_match_all(
			'/<!-- *(###)([A-Z]([A-Z0-9_]*[A-Z0-9])?)(###)/',
			$this->templateCode,
			$matches
		);

		return array_unique($matches[2]);
	}

	/**
	 * Finds all markers within the current HTML template.
	 * Note: This also finds subpart names.
	 *
	 * The result is one long string that is easy to process using regular
	 * expressions.
	 *
	 * Example: If the markers ###FOO### and ###BAR### are found, the string
	 * "#FOO#BAR#" would be returned.
	 *
	 * @return	string		a list of markes as one long string, separated,
	 *						prefixed and postfixed by '#'
	 *
	 * @access	private
	 */
	function findMarkers() {
		$matches = array();
		preg_match_all(
			'/(###)(([A-Z0-9_]*[A-Z0-9])?)(###)/', $this->templateCode, $matches
		);

		$markerNames = array_unique($matches[2]);

		return '#'.implode('#', $markerNames).'#';
	}

	/**
	 * Gets a list of markers with a given prefix.
	 * Example: If the prefix is "WRAPPER" (or "wrapper", case is not relevant),
	 * the following array might be returned: ("WRAPPER_FOO", "WRAPPER_BAR")
	 *
	 * If there are no matches, an empty array is returned.
	 *
	 * The function <code>findMarkers</code> must be called before this function
	 * may be called.
	 *
	 * @param	string	case-insensitive prefix for the marker names to look for
	 *
	 * @return	array	array of matching marker names, might be empty
	 *
	 * @access	private
	 */
	function getPrefixedMarkers($prefix) {
		$matches = array();
		preg_match_all(
			'/(#)('.strtoupper($prefix).'_[^#]+)/',
			$this->markerNames, $matches
		);

		$result = array_unique($matches[2]);

		return $result;
	}

	/**
	 * Sets a marker's content.
	 *
	 * Example: If the prefix is "field" and the marker name is "one", the
	 * marker "###FIELD_ONE###" will be written.
	 *
	 * If the prefix is empty and the marker name is "one", the marker
	 * "###ONE###" will be written.
	 *
	 * @param	string		the marker's name without the ### signs,
	 * 						case-insensitive, will get uppercased, must not be
	 * 						empty
	 * @param	string		the marker's content, may be empty
	 * @param	string		prefix to the marker name (may be empty,
	 * 						case-insensitive, will get uppercased)
	 *
	 * @access	protected
	 */
	function setMarker($markerName, $content, $prefix = '') {
		$unifiedMarkerName = $this->createMarkerName($markerName, $prefix);

		if ($this->isMarkerNameValidWithHashes($unifiedMarkerName)) {
			$this->markers[$unifiedMarkerName] = $content;
		}
	}

	/**
	 * Gets a marker's content.
	 *
	 * @param	string		the marker's name without the ### signs,
	 * 						case-insensitive, will get uppercased, must not be
	 * 						empty
	 *
	 * @return	string		the marker's content or an empty string if the
	 * 						marker has not been set before
	 *
	 * @access	protected
	 */
	function getMarker($markerName) {
		$unifiedMarkerName = $this->createMarkerName($markerName);
		if (!isset($this->markers[$unifiedMarkerName])) {
			return '';
		}

		return $this->markers[$unifiedMarkerName];
	}

	/**
	 * Sets a subpart's content.
	 *
	 * Example: If the prefix is "field" and the subpart name is "one", the
	 * subpart "###FIELD_ONE###" will be written.
	 *
	 * If the prefix is empty and the subpart name is "one", the subpart
	 * "###ONE###" will be written.
	 *
	 * @param	string		the subpart's name without the ### signs,
	 * 						case-insensitive, will get uppercased, must not be
	 * 						empty
	 * @param	string		the subpart's content, may be empty
	 * @param	string		prefix to the subpart name (may be empty,
	 * 						case-insensitive, will get uppercased)
	 */
	protected function setSubpart($subpartName, $content, $prefix = '') {
		$subpartName = $this->createMarkerNameWithoutHashes(
			$subpartName, $prefix
		);

		if ($this->isMarkerNameValidWithoutHashes($subpartName)) {
			$this->templateCache[$subpartName] = $content;
		}
	}

	/**
	 * Checks whether a subpart is visible.
	 *
	 * Note: If the subpart to check does not exist, this function will return
	 * false.
	 *
	 * @param	string		name of the subpart to check (without the ###), must
	 * 						not be empty
	 *
	 * @return	boolean		true if the subpart is visible, false otherwise
	 *
	 * @access	pulic
	 */
	function isSubpartVisible($subpartName) {
		if ($subpartName == '') {
			return false;
		}

		return (isset($this->templateCache[$subpartName])
			&& !isset($this->subpartsToHide[$subpartName]));
	}

	/**
	 * Takes a comma-separated list of subpart names and sets them to hidden. In
	 * the process, the names are changed from 'aname' to '###BLA_ANAME###' and
	 * used as keys.
	 *
	 * Example: If the prefix is "field" and the list is "one,two", the subparts
	 * "###FIELD_ONE###" and "###FIELD_TWO###" will be hidden.
	 *
	 * If the prefix is empty and the list is "one,two", the subparts
	 * "###ONE###" and "###TWO###" will be hidden.
	 *
	 * @param	string		comma-separated list of at least 1 subpart name to
	 * 						hide (case-insensitive, will get uppercased)
	 * @param	string		prefix to the subpart names (may be empty,
	 * 						case-insensitive, will get uppercased)
	 *
	 * @access	protected
	 */
	function hideSubparts($subparts, $prefix = '') {
		$subpartNames = explode(',', $subparts);

		foreach ($subpartNames as $currentSubpartName) {
			$fullSubpartName = $this->createMarkerNameWithoutHashes(
				$currentSubpartName,
				$prefix
			);

			$this->subpartsToHide[$fullSubpartName] = true;
		}
	}

	/**
	 * Takes a comma-separated list of subpart names and unhides them if they
	 * have been hidden beforehand.
	 *
	 * Note: All subpartNames that are provided with the second parameter will
	 * not be unhidden. This is to avoid unhiding subparts that are hidden by
	 * the configuration.
	 *
	 * In the process, the names are changed from 'aname' to '###BLA_ANAME###'.
	 *
	 * Example: If the prefix is "field" and the list is "one,two", the subparts
	 * "###FIELD_ONE###" and "###FIELD_TWO###" will be unhidden.
	 *
	 * If the prefix is empty and the list is "one,two", the subparts
	 * "###ONE###" and "###TWO###" will be unhidden.
	 *
	 * @param	string		comma-separated list of at least 1 subpart name to
	 * 						unhide (case-insensitive, will get uppercased),
	 * 						must not be empty
	 * @param	string		comma-separated list of subpart names that
	 * 						shouldn't get unhidden
	 * @param	string		prefix to the subpart names (may be empty,
	 * 						case-insensitive, will get uppercased)
	 *
	 * @access	protected
	 */
	function unhideSubparts(
		$subparts, $permanentlyHiddenSubparts = '', $prefix = ''
	) {
		$subpartNames = explode(',', $subparts);
		if ($permanentlyHiddenSubparts != '') {
			$hiddenSubpartNames = explode(',', $permanentlyHiddenSubparts);
		} else {
			$hiddenSubpartNames = array();
		}

		foreach ($subpartNames as $currentSubpartName) {
			// Only unhide the current subpart if it is not on the list of
			// permanently hidden subparts (e.g. by configuration).
			if (!in_array($currentSubpartName, $hiddenSubpartNames)) {
				$currentMarkerName = $this->createMarkerNameWithoutHashes(
					$currentSubpartName, $prefix
				);
				unset($this->subpartsToHide[$currentMarkerName]);
			}
		}
	}

	/**
	 * Creates an uppercase marker (or subpart) name from a given name and an
	 * optional prefix, wrapping the result in three hash signs (###).
	 *
	 * Example: If the prefix is "field" and the marker name is "one", the
	 * result will be "###FIELD_ONE###".
	 *
	 * If the prefix is empty and the marker name is "one", the result will be
	 * "###ONE###".
	 *
	 * @access	private
	 */
	function createMarkerName($markerName, $prefix = '') {
		return '###'
			.$this->createMarkerNameWithoutHashes($markerName, $prefix).'###';
	}

	/**
	 * Creates an uppercase marker (or subpart) name from a given name and an
	 * optional prefix, but without wrapping it in hash signs.
	 *
	 * Example: If the prefix is "field" and the marker name is "one", the
	 * result will be "FIELD_ONE".
	 *
	 * If the prefix is empty and the marker name is "one", the result will be
	 * "ONE".
	 *
	 * @access	private
	 */
	function createMarkerNameWithoutHashes($markerName, $prefix = '') {
		// If a prefix is provided, uppercases it and separates it with an
		// underscore.
		if (!empty($prefix)) {
			$prefix .= '_';
		}

		return strtoupper($prefix.trim($markerName));
	}

	/**
	 * Retrieves a named subpart, recursively filling in its inner subparts
	 * and markers. Inner subparts that are marked to be hidden will be
	 * substituted with empty strings.
	 *
	 * This function either works on the subpart with the name $key or the
	 * complete HTML template if $key is an empty string.
	 *
	 * @param	string		key of an existing subpart, for example 'LIST_ITEM'
	 * 						(without the ###), or an empty string to use the
	 * 						complete HTML template
	 *
	 * @return	string		the subpart content or an empty string if the
	 * 						subpart is hidden or the subpart name is missing
	 *
	 * @access	protected
	 */
	function getSubpart($key = '') {
		if (($key != '') && !isset($this->templateCache[$key])) {
			$this->setErrorMessage('The subpart <strong>'.$key.'</strong> is '
				.'missing in the HTML template file <strong>'
				.$this->getConfValueString(
					'templateFile',
					's_template_special',
					true)
				.'</strong>. If you are using a modified HTML template, please '
				.'fix it. If you are using the original HTML template file, '
				.'please file a bug report in the '
				.'<a href="https://bugs.oliverklee.com/">bug tracker</a>.'
			);

			return '';
		}

		if (($key != '') && !$this->isSubpartVisible($key)) {
			return '';
		}

		$templateCode = ($key != '')
			? $this->templateCache[$key] : $this->templateCode;

		// recursively replaces subparts with their contents
		$noSubpartMarkers = preg_replace_callback(
			'/<!-- *###([^#]*)### *-->(.*)'
				.'<!-- *###\1### *-->/msSU',
			array(
				$this,
				'getSubpartForCallback'
			),
			$templateCode
		);

		// replaces markers with their contents
		return str_replace(
			array_keys($this->markers), $this->markers, $noSubpartMarkers
		);
	}

	/**
	 * Retrieves a subpart.
	 *
	 * @param	array		numeric array with matches from
	 * 						preg_replace_callback; the element #1 needs to
	 * 						contain the name of the subpart to retrieve (in
	 * 						uppercase without the surrounding ###)
	 *
	 * @return	string		the contents of the corresponding subpart or an
	 * 						empty string in case the subpart does not exist
	 *
	 * @access	private
	 */
	function getSubpartForCallback(array $matches) {
		return $this->getSubpart($matches[1]);
	}

	/**
	 * Writes all localized labels for the current template into their
	 * corresponding template markers.
	 *
	 * For this, the label markers in the template must be prefixed with
	 * "LABEL_" (e.g. "###LABEL_FOO###"), and the corresponding localization
	 * entry must have the same key, but lowercased and without the ###
	 * (e.g. "label_foo").
	 *
	 * @access	protected
	 */
	function setLabels() {
		$labels = $this->getPrefixedMarkers('label');

		foreach ($labels as $currentLabel) {
			$this->setMarker(
				$currentLabel, $this->translate(strtolower($currentLabel))
			);
		}
	}

	/**
	 * Sets the all CSS classes from TS for the template in $this->markers.
	 * The list of needed CSS classes will be extracted from the template file.
	 *
	 * Classes are set only if they are set via TS, else the marker will be an
	 * empty string.
	 *
	 * @access	protected
	 */
	function setCss() {
		$cssEntries = $this->getPrefixedMarkers('class');

		foreach ($cssEntries as $currentCssEntry) {
			$this->setMarker(
				$currentCssEntry,
				$this->createClassAttribute(
					$this->getConfValueString(strtolower($currentCssEntry))
				)
			);
		}
	}

	/**
	 * Creates an CSS class attribute. The parameter is the class name.
	 *
	 * Example: If the parameter is 'foo', our extension is named 'bar' and we are in p1,
	 * then the return value is 'class="tx-bar-pi1-foo"'.
	 *
	 * If the parameter is an emty string, the return value is an empty string as well
	 * (not an attribute with an empty value).
	 *
	 * @param	string		a CSS class name (may be empty)
	 *
	 * @return	string		a CSS class attribute (may be empty)
	 *
	 * @access	protected
	 */
	function createClassAttribute($className) {
		return !empty($className) ? $this->pi_classParam($className) : '';
	}

	/**
	 * Checks whether a marker name (or subpart name) is valid (including the
	 * leading and trailing hashes ###).
	 *
	 * A valid marker name must be a non-empty string, consisting of uppercase
	 * and lowercase letters ranging A to Z, digits and underscores. It must
	 * start with a lowercase or uppercase letter ranging from A to Z. It must
	 * not end with an underscore. In addition, it must be prefixed and suffixed
	 * with ###.
	 *
	 * @param	string		marker name to check (with the hashes), may be
	 * 						empty
	 *
	 * @return	boolean		true if the marker name is valid, false otherwise
	 *
	 * @access	private
	 */
	function isMarkerNameValidWithHashes($markerName) {
		return (boolean) preg_match(
			'/^###[a-zA-Z]([a-zA-Z0-9_]*[a-zA-Z0-9])?###$/', $markerName
		);
	}

	/**
	 * Checks whether a marker name (or subpart name) is valid (excluding the
	 * leading and trailing hashes ###).
	 *
	 * A valid marker name must be a non-empty string, consisting of uppercase
	 * and lowercase letters ranging A to Z, digits and underscores. It must
	 * start with a lowercase or uppercase letter ranging from A to Z. It must
	 * not end with an underscore.
	 *
	 * @param	string		marker name to check (without the hashes), may be
	 * 						empty
	 *
	 * @return	boolean		true if the marker name is valid, false otherwise
	 *
	 * @access	private
	 */
	function isMarkerNameValidWithoutHashes($markerName) {
		return $this->isMarkerNameValidWithHashes('###'.$markerName.'###');
	}

	/**
	 * Initializes '$GLOBALS['TSFE']->sys_page', '$GLOBALS['TT']' and
	 * '$this->cObj' as these objects are needed but only initialized
	 * automatically if TYPO3_MODE is 'FE'.
	 * This will allow the FE templating functions to be used even without the
	 * FE.
	 */
	 public function fakeFrontend() {
	 	if (!is_object($GLOBALS['TT'])) {
	 		$GLOBALS['TT'] = t3lib_div::makeInstance('t3lib_timeTrack');
	 	}

	 	if (!is_object($GLOBALS['TSFE']->sys_page)) {
	 		$GLOBALS['TSFE']->sys_page
	 			= t3lib_div::makeInstance('t3lib_pageSelect');
	 	}

		if (!is_object($this->cObj)) {
			$this->cObj = t3lib_div::makeInstance('tslib_cObj');
			$this->cObj->start('');
		}
	 }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_templatehelper.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_templatehelper.php']);
}
?>
