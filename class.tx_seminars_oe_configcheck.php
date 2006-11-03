<?php
/***************************************************************
* Copyright notice
*
* (c) 2006 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Class 'tx_seminars_oe_confcheck' for the 'seminars' extension.
 * Note: This class will soon move to the 'oelib' extension. When this is done,
 * the check for the parent class (dbplugin) needs to be abjusted to the new
 * inheritance structure.
 *
 * This class checks the extension configuration (TS setup) and some data for
 * basic sanity. This works for FE plug-ins, BE modules and free-floating data
 * structures.
 *
 * Functions for checking a class (optionally with a flavor) must follow the
 * naming schema "check_classname" or "check_classname_flavor"
 * (if a flavor is used).
 *
 * Example: The check method for objects of the class "tx_seminars_seminarbag"
 * (without any special flavor) must be named "check_tx_seminars_seminarbag".
 * The check method for objects of the class "tx_seminars_pi1" with the flavor
 * "seminar_registration" needs to be named
 * "check_tx_seminars_pi1_seminar_registration".
 *
 * The correct functioning of this class does not rely on any HTML templates or
 * language files so it works even under the worst of circumstances.
 *
 * @author	Oliver Klee <typo3-coding@oliverklee.de>
 */

class tx_seminars_oe_configcheck {
	/** the object whose configuration should be checked */
	var $objectToCheck;
	/** the (cached) class name of $this->objectToCheck */
	var $className;

	/**
	 * A string describing the "flavor" of the object in case the class name
	 * does not to sufficiently indicate exactly which configuration values to
	 * check.
	 */
	var $flavor;

	/** the error to return (or an empty string if there is no error) */
	var $errorText;

	/**
	 * The constructor.
	 *
	 * @param	object		the object that shall be checked for configuration problems, must be of a subclass of tx_seminars_dbplugin
	 */
	function tx_seminars_oe_configcheck(&$objectToCheck) {
		if ($objectToCheck
			&& is_subclass_of($objectToCheck, 'tx_seminars_dbplugin')) {
			$this->objectToCheck =& $objectToCheck;
			$this->className = get_class($this->objectToCheck);

			$this->errorText = '';
		} else {
			trigger_error('tx_seminars_oe_configcheck: $objectToCheck must be '
				.'a subclass of tx_seminars_dbplugin, but actually is a '
				.get_class($objectToCheck).'.<br />');
		}
	}

	/**
	 * Sets the "flavor" of the object to check. The flavor is used to
	 * differentiate between different kinds of objects of the same class,
	 * e.g. the list view and the single view (which both are pi1 objects).
	 *
	 * @param	string		a short string identifying the "flavor" of the object to check (may be empty)
	 *
	 * @access	public
	 */
	function setFlavor($flavor) {
		$this->flavor = $flavor;

		return;
	}

	/**
	 * Detects the class of the object to check and performs the sanity checks.
	 * If everything is okay, an empty string is returned.
	 * If there are errors, the first error is returned (not wrapped).
	 * The error message always is in English.
	 *
	 * If there is more than one error message, the first error needs to be
	 * fixed before the second error can be seen. This is intended as some
	 * errors may cause a row of other errors which disappear when the first
	 * error has been fixed.
	 *
	 * Note: This function expected $this->checkByClassNameAndFlavor() to be defined!
	 *
	 * @return	string		an error message (or an empty string)
	 *
	 * @access	public
	 */
	function checkIt() {
		$this->checkByClassNameAndFlavor();

		return $this->getRawMessage();
	}

	/**
	 * Detects the class of the object to check and performs the sanity checks.
	 * If everything is okay, an empty string is returned.
	 * If there are errors, the first error is returned (wrapped by wrap()).
	 * The error message always is in English.
	 *
	 * If there is more than one error message, the first error needs to be
	 * fixed before the second error can be seen. This is intended as some
	 * errors may cause a row of other errors which disappear when the first
	 * error has been fixed.
	 *
	 * Note: This function expected $this->checkByClassNameAndFlavor() to be defined!
	 *
	 * @return	string		an error message wrapped by wrap() (or an empty string)
	 *
	 * @access	public
	 */
	function checkItAndWrapIt() {
		$this->checkByClassNameAndFlavor();

		return $this->getWrappedMessage();
	}

	/**
	 * Calls the correct configuration checks, depending on the class name of
	 * $this->objectToCheck and (if applicable) on $this->flavor.
	 *
	 * @access	protected
	 */
	function checkByClassNameAndFlavor() {
		$checkFunctionName = 'check_'.$this->className;
		if (!empty($this->flavor)) {
			$checkFunctionName .= '_'.$this->flavor;
		}

		// Check whether a check for the corresponding class exists.
		if (method_exists($this, $checkFunctionName)) {
			$this->$checkFunctionName();
		} else {
			trigger_error('No configuration check '.$checkFunctionName.' created yet.');
		}

		return;
	}

	/**
	 * If $this->errorText is empty, it will be set to $message.
	 *
	 * $message should explain what the problem is, what its negative effects
	 * are and what the user can do to fix the problem.
	 *
	 * If $this->errorText is non-empty or $message is empty,
	 * this function is a no-op.
	 *
	 * @param	string		error text to set (may be empty)
	 *
	 * @access	protected
	 */
	function setErrorMessage($message) {
		if (!empty($message) && empty($this->errorText)) {
			$this->errorText = $message;
		}

		return;
	}

	/**
	 * Returns an empty string if there are no errors.
	 * Otherwise, returns $this->errorText.
	 *
	 * Use this method if you want to process this message furether, e.g.
	 * for bubbling it up to other configcheck objects.
	 *
	 * @return	string		$this->errorText (or an empty string if there are not errors)
	 *
	 * @access	protected
	 */
	function getRawMessage() {
		return $this->errorText;
	}

	/**
	 * Returns an empty string if there are no errors.
	 * Otherwise, returns $this->errorText wrapped by $this->wrap().
	 *
	 * Use this method if you want to display this message pretty
	 * directly and it doesn't need to get handled to other configcheck
	 * objects.
	 *
	 * @return	string		$this->errorText wrapped by $this->wrap (or an empty string if there are not errors)
	 *
	 * @access	protected
	 */
	function getWrappedMessage() {
		$result = '';

		if (!empty($this->errorText)) {
			$result = $this->wrap($this->errorText);
		}

		return $result;
	}

	/**
	 * Wraps $message in (in this case) <p></p>, styled nicely alarming,
	 * with the lang attribe set to "en".
	 * In addition, the message is prepended by "Configuration check warning: "
	 * and followed by "When that is done, please empty the FE cache and
	 * reload this page."
	 *
	 * This wrapping method can be overwritten for other wrappings.
	 *
	 * @access	string		text to be wrapped (may be empty)
	 *
	 * @return	string		$message wrapped in <p></p>
	 *
	 * @access	protected
	 */
	function wrap($message) {
		return '<p lang="en" style="color: #000; background: #ff; padding: .4em; border: 3px solid #f00; clear: both;">'
			.'<strong>Configuration check warning:</strong><br />'
			.$message
			.'<br />When that is done, please empty the '
			.'<acronym title="front-end">FE</acronym> cache and reload '
			.'this page.'
			.'</p>';
	}

	/**
	 * Checks whether the static template has been included.
	 *
	 * @access	protected
	 */
	function checkStaticIncluded() {
		if (!$this->objectToCheck->getConfValueBoolean('isStaticTemplateLoaded')) {
			$this->setErrorMessage('The static template is not included. This has the effect that important default values do not get set. To fix this, please include this extension\'s template under <em>Include static (from extensions)</em> in your TS template.');
		}

		return;
	}

	/**
	 * Checks whether the HTML template is provided and the file exists.
	 *
	 * @param	boolean		whether the template can also be selected via flexforms
	 *
	 * @access	protected
	 */
	function checkTemplateFile($canUseFlexforms = false) {
		$this->checkForNonEmptyString('templateFile', $canUseFlexforms, 's_template_special', 'This value specifies the HTML template which is essential when creating any output from this extension.');

		if ($GLOBALS['TSFE']
			&& $this->objectToCheck->hasConfValueString('templateFile', 's_template_special')) {
			$rawFileName = $this->objectToCheck->getConfValueString('templateFile', 's_template_special', true);
			if (!is_file($GLOBALS['TSFE']->tmpl->getFileName($rawFileName))) {
				$message = 'The specified HTML template file <strong>'
					.htmlspecialchars($rawFileName)
					.'</strong> cannot be read. '
					.'The HTML template file is essential when creating any output from this extension. '
					.'Please either create the the file <strong>'.$rawFileName.'</strong> or select an existing file using the TS variable <strong>'.$this->getTSSetupPath().'templateFile</strong>';
				if ($canUseFlexforms) {
					$message .= ' or via FlexForms';
				}
				$message .= '.';
				$this->setErrorMessage($message);
			}
		}

		return;
	}

	/**
	 * Checks whether the CSS file (if a name is provided) actually is a file.
	 * If no file name is provided, no error will be displayed as this is
	 * perfectly allowed.
	 *
	 * @param	boolean		whether the css File can also be selected via flexforms
	 *
	 * @access	protected
	 */
	function checkCssFile($canUseFlexforms = false) {
		if ($this->objectToCheck->hasConfValueString('cssFile', 's_template_special')) {
			$fileName = $this->objectToCheck->getConfValueString('cssFile', 's_template_special', true);
			if (!is_file($fileName)) {
				$message = 'The specified CSS file <strong>'
					.htmlspecialchars($fileName)
					.'</strong> cannot be read. '
					.'If that variable does not point to an existing file, no '
					.'special CSS will be used for styling this extension\'s HTML. '
					.'Please either create the the file <strong>'.$fileName.'</strong> or select an existing file using the TS variable <strong>'.$this->getTSSetupPath().'cssFile</strong>';
				if ($canUseFlexforms) {
					$message .= ' or via FlexForms';
				}
				$message .= '. If you do not want to use any special CSS, you '
					.'can set that variable to an empty string.';
				$this->setErrorMessage($message);
			}
		}
		return;
	}

	/**
	 * Checks the CSS class names provided in the TS setup for validity.
	 * Empty values are considered as valid.
	 *
	 * @access	protected
	 */
	function checkCssClassNames() {
		$cssEntries = $this->objectToCheck->getPrefixedMarkers('class');

		foreach ($cssEntries as $currentCssEntry) {
			$setupVariable = strtolower($currentCssEntry);
			$cssClassName = $this->objectToCheck->getConfValueString($setupVariable);
			if (!preg_match('/^[A-Za-z0-9\-_\:\.]*$/', $cssClassName)) {
				$message = 'The specified CSS class name <strong>'
					.htmlspecialchars($cssClassName)
					.'</strong> is invalid. '
					.'This will cause the class to not get correctly applied '
					.'in web browsers. '
					.'Please set the TS setup variable <strong>'
					.$this->getTSSetupPath().$setupVariable
					.'</strong> to a valid CSS class or an empty string.';
				$this->setErrorMessage($message);
			};
		}

		return;
	}



	/**
	 * Checks whether a configuration value contains a non-empty-string.
	 *
	 * @param	string		TS setup field name to extract, must not be empty
	 * @param	boolean		whether the value can also be set via flexforms (this will be mentioned in the error message)
	 * @param	string		flexforms sheet pointer, eg. "sDEF", will be ignored if $canUseFlexforms is set to false
	 * @param	string		a sentence explaning what that configuration value is needed for and why it needs to be non-empty, must be non-empty
	 *
	 * @access	protected
	 */
	function checkForNonEmptyString($fieldName, $canUseFlexforms, $sheet, $explanation) {
		if (!$this->objectToCheck->hasConfValueString($fieldName, $sheet)) {
			$message = '';

			$message = 'The TS setup variable <strong>'.$this->getTSSetupPath().$fieldName.'</strong> currently is empty, but is required to contain a non-empty value. '
				.$explanation
				.' Please set the TS setup variable <strong>'.$this->getTSSetupPath().$fieldName.'</strong> in your TS template setup';
			if ($canUseFlexforms) {
				$message .= ' or via FlexForms';
			}
			$message .= '.';
			$this->setErrorMessage($message);
		}

		return;
	}

	/**
	 * Checks whether a configuration value is non-empty and lies within a set
	 * of allowed values.
	 *
	 * @param	string		TS setup field name to extract, must not be empty
	 * @param	boolean		whether the value can also be set via flexforms (this will be mentioned in the error message)
	 * @param	string		flexforms sheet pointer, eg. "sDEF", will be ignored if $canUseFlexforms is set to false
	 * @param	string		a sentence explaning what that configuration value is needed for, must be non-empty
	 * @param	array		array of allowed values (must not be empty)
	 *
	 * @access	protected
	 */
	function checkIfSingleInSetNotEmpty($fieldName, $canUseFlexforms, $sheet, $explanation, $allowedValues) {
		$this->checkForNonEmptyString($fieldName, $canUseFlexforms, $sheet, $explanation);
		$this->checkIfSingleInSetOrEmpty($fieldName, $canUseFlexforms, $sheet, $explanation, $allowedValues);
		return;
	}

	/**
	 * Checks whether a configuration value either is empty or lies within a
	 * set of allowed values.
	 *
	 * @param	string		TS setup field name to extract, must not be empty
	 * @param	boolean		whether the value can also be set via flexforms (this will be mentioned in the error message)
	 * @param	string		flexforms sheet pointer, eg. "sDEF", will be ignored if $canUseFlexforms is set to false
	 * @param	string		a sentence explaning what that configuration value is needed for, must be non-empty
	 * @param	array		array of allowed values (must not be empty)
	 *
	 * @access	protected
	 */
	function checkIfSingleInSetOrEmpty($fieldName, $canUseFlexforms, $sheet, $explanation, $allowedValues) {
		if ($this->objectToCheck->hasConfValueString($fieldName, $sheet)) {
			$value = $this->objectToCheck->getConfValueString($fieldName, $sheet);

			$overviewOfValues = '('.implode(', ', $allowedValues).')';

			if (!in_array($value, $allowedValues, true)) {
				$message = 'The TS setup variable <strong>'
					.$this->getTSSetupPath().$fieldName
					.'</strong> is set to the value <strong>'.htmlspecialchars($value).'</strong>, but only the following values are allowed: '
					.'<br /><strong>'.$overviewOfValues.'</strong><br />'
					.$explanation
					.' Please correct the TS setup variable <strong>'.$this->getTSSetupPath().$fieldName.'</strong> in your TS template setup';
				if ($canUseFlexforms) {
					$message .= ' or via FlexForms';
				}
				$message .= '.';
				$this->setErrorMessage($message);
			}
		}

		return;
	}

	/**
	 * Checks whether a configuration value has a boolean value.
	 *
	 * @param	string		TS setup field name to extract, must not be empty
	 * @param	boolean		whether the value can also be set via flexforms (this will be mentioned in the error message)
	 * @param	string		flexforms sheet pointer, eg. "sDEF", will be ignored if $canUseFlexforms is set to false
	 * @param	string		a sentence explaning what that configuration value is needed for, must be non-empty
	 *
	 * @access	protected
	 */
	function checkIfBoolean($fieldName, $canUseFlexforms, $sheet, $explanation) {
		$this->checkIfSingleInSetOrEmpty(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation,
			array('0', '1')
		);

		return;
	}

	/**
	 * Checks whether a configuration value has an integer value (or is empty).
	 *
	 * @param	string		TS setup field name to extract, must not be empty
	 * @param	boolean		whether the value can also be set via flexforms (this will be mentioned in the error message)
	 * @param	string		flexforms sheet pointer, eg. "sDEF", will be ignored if $canUseFlexforms is set to false
	 * @param	string		a sentence explaning what that configuration value is needed for, must be non-empty
	 *
	 * @access	protected
	 */
	function checkIfInteger($fieldName, $canUseFlexforms, $sheet, $explanation) {
		$value = $this->objectToCheck->getConfValueString($fieldName, $sheet);

		if (!preg_match('/^\d*$/', $value)) {
			$message = 'The TS setup variable <strong>'
				.$this->getTSSetupPath().$fieldName
				.'</strong> is set to the value <strong>'.htmlspecialchars($value).'</strong>, but only integers are allowed. '
				.$explanation
				.' Please correct the TS setup variable <strong>'.$this->getTSSetupPath().$fieldName.'</strong> in your TS template setup';
			if ($canUseFlexforms) {
				$message .= ' or via FlexForms';
			}
			$message .= '.';
			$this->setErrorMessage($message);
		}

		return;
	}

	/**
	 * Checks whether a configuration value has a positive (thus non-zero)
	 * integer value.
	 *
	 * @param	string		TS setup field name to extract, must not be empty
	 * @param	boolean		whether the value can also be set via flexforms (this will be mentioned in the error message)
	 * @param	string		flexforms sheet pointer, eg. "sDEF", will be ignored if $canUseFlexforms is set to false
	 * @param	string		a sentence explaning what that configuration value is needed for, must be non-empty
	 *
	 * @access	protected
	 */
	function checkIfPositiveInteger($fieldName, $canUseFlexforms, $sheet, $explanation) {
		$this->checkIfInteger($fieldName, $canUseFlexforms, $sheet, $explanation);

		if (!$this->objectToCheck->hasConfValueInteger($fieldName, $sheet)) {
			$message = 'The TS setup variable <strong>'
				.$this->getTSSetupPath().$fieldName
				.'</strong> is zero, but needs to be non-zero. '
				.$explanation
				.' Please correct the TS setup variable <strong>'.$this->getTSSetupPath().$fieldName.'</strong> in your TS template setup';
			if ($canUseFlexforms) {
				$message .= ' or via FlexForms';
			}
			$message .= '.';
			$this->setErrorMessage($message);
		}

		return;
	}
	/**
	 * Checks whether a configuration value is non-empty and its
	 * comma-separated values lie within a set of allowed values.
	 *
	 * @param	string		TS setup field name to extract, must not be empty
	 * @param	boolean		whether the value can also be set via flexforms (this will be mentioned in the error message)
	 * @param	string		flexforms sheet pointer, eg. "sDEF", will be ignored if $canUseFlexforms is set to false
	 * @param	string		a sentence explaning what that configuration value is needed for, must be non-empty
	 * @param	array		array of allowed values (must not be empty)
	 *
	 * @access	protected
	 */
	function checkIfMultiInSetNotEmpty($fieldName, $canUseFlexforms, $sheet, $explanation, $allowedValues) {
		$this->checkForNonEmptyString($fieldName, $canUseFlexforms, $sheet, $explanation);
		$this->checkIfMultiInSetOrEmpty($fieldName, $canUseFlexforms, $sheet, $explanation, $allowedValues);
		return;
	}

	/**
	 * Checks whether a configuration value either is empty or its
	 * comma-separated values lie within a set of allowed values.
	 *
	 * @param	string		TS setup field name to extract, must not be empty
	 * @param	boolean		whether the value can also be set via flexforms (this will be mentioned in the error message)
	 * @param	string		flexforms sheet pointer, eg. "sDEF", will be ignored if $canUseFlexforms is set to false
	 * @param	string		a sentence explaning what that configuration value is needed for, must be non-empty
	 * @param	array		array of allowed values (must not be empty)
	 *
	 * @access	protected
	 */
	function checkIfMultiInSetOrEmpty($fieldName, $canUseFlexforms, $sheet, $explanation, $allowedValues) {
		if ($this->objectToCheck->hasConfValueString($fieldName, $sheet)) {
			$allValues = explode(
				',',
				$this->objectToCheck->getConfValueString($fieldName, $sheet)
			);

			$overviewOfValues = '('.implode(', ', $allowedValues).')';
			foreach ($allValues as $currentRawValue) {
				$currentTrimmedValue = trim($currentRawValue);

				if (!in_array($currentTrimmedValue, $allowedValues, true)) {
					$message = 'The TS setup variable <strong>'
						.$this->getTSSetupPath().$fieldName
						.'</strong> contains the value <strong>'.htmlspecialchars($currentTrimmedValue).'</strong>, but only the following values are allowed: '
						.'<br /><strong>'.$overviewOfValues.'</strong><br />'
						.$explanation
						.' Please correct the TS setup variable <strong>'.$this->getTSSetupPath().$fieldName.'</strong> in your TS template setup';
					if ($canUseFlexforms) {
						$message .= ' or via FlexForms';
					}
					$message .= '.';
					$this->setErrorMessage($message);
				}
			}
		}

		return;
	}

	/**
	 * Checks whether a configuration value is non-empty and is one of the
	 * column names of a given DB table.
	 *
	 * @param	string		TS setup field name to extract, must not be empty
	 * @param	boolean		whether the value can also be set via flexforms (this will be mentioned in the error message)
	 * @param	string		flexforms sheet pointer, eg. "sDEF", will be ignored if $canUseFlexforms is set to false
	 * @param	string		a sentence explaning what that configuration value is needed for, must be non-empty
	 * @param	string		a DB table name (must not be empty)
	 *
	 * @access	protected
	 */
	function checkIfSingleInTableNotEmpty($fieldName, $canUseFlexforms, $sheet, $explanation, $tableName) {
		$this->checkIfSingleInSetNotEmpty(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation,
			$this->getDbColumnNames($tableName)
		);
		return;
	}

	/**
	 * Checks whether a configuration value either is empty or is one of the
	 * column names of a given DB table.
	 *
	 * @param	string		TS setup field name to extract, must not be empty
	 * @param	boolean		whether the value can also be set via flexforms (this will be mentioned in the error message)
	 * @param	string		flexforms sheet pointer, eg. "sDEF", will be ignored if $canUseFlexforms is set to false
	 * @param	string		a sentence explaning what that configuration value is needed for, must be non-empty
	 * @param	string		a DB table name (must not be empty)
	 *
	 * @access	protected
	 */
	function checkIfSingleInTableOrEmpty($fieldName, $canUseFlexforms, $sheet, $explanation, $tableName) {
		$this->checkIfSingleInSetOrEmpty(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation,
			$this->getDbColumnNames($tableName)
		);
		return;
	}

	/**
	 * Checks whether a configuration value is non-empty and its
	 * comma-separated values lie within a set of allowed values.
	 *
	 * @param	string		TS setup field name to extract, must not be empty
	 * @param	boolean		whether the value can also be set via flexforms (this will be mentioned in the error message)
	 * @param	string		flexforms sheet pointer, eg. "sDEF", will be ignored if $canUseFlexforms is set to false
	 * @param	string		a sentence explaning what that configuration value is needed for, must be non-empty
	 * @param	string		a DB table name (must not be empty)
	 *
	 * @access	protected
	 */
	function checkIfMultiInTableNotEmpty($fieldName, $canUseFlexforms, $sheet, $explanation, $tableName) {
		$this->checkIfMultiInSetNotEmpty(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation,
			$this->getDbColumnNames($tableName)
		);
		return;
	}

	/**
	 * Checks whether a configuration value either is empty or its
	 * comma-separated values is a column name of a given DB table.
	 *
	 * @param	string		TS setup field name to extract, must not be empty
	 * @param	boolean		whether the value can also be set via flexforms (this will be mentioned in the error message)
	 * @param	string		flexforms sheet pointer, eg. "sDEF", will be ignored if $canUseFlexforms is set to false
	 * @param	string		a sentence explaning what that configuration value is needed for, must be non-empty
	 * @param	string		a DB table name (must not be empty)
	 *
	 * @access	protected
	 */
	function checkIfMultiInTableOrEmpty($fieldName, $canUseFlexforms, $sheet, $explanation, $tableName) {
		$this->checkIfMultiInSetOrEmpty(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation,
			$this->getDbColumnNames($tableName)
		);
		return;
	}

	/**
	 * Checks whether the salutation mode is set correctly.
	 *
	 * @param	boolean		whether the value can also be set via flexforms (this will be mentioned in the error message)
	 *
	 * @access	protected
	 */
	function checkSalutationMode($canUseFlexforms = false) {
		$this->checkIfSingleInSetNotEmpty(
			'salutation',
			$canUseFlexforms,
			'sDEF',
			'This variable controls the salutation mode (formal or informal). If it is not set correctly, some output cannot be created at all.',
			array('formal', 'informal')
		);

		return;
	}

	/**
	 * Gets the path for TS setup where $this->objectToCheck's configuration is
	 * located. This includes the extension key, (possibly) something like pi1
	 * and the trailing dot.
	 *
	 * @return	string		the TS setup configuration path including the trailing dot, e.g. "plugin.tx_seminars_pi1."
	 *
	 * @access	protected
	 */
	function getTSSetupPath() {
		$result = 'plugin.tx_'.$this->objectToCheck->extKey;

		$matches = array();
		if (preg_match('/_pi[0-9]+$/', $this->className, $matches)) {
			$result .= $matches[0];
		}

		$result .= '.';

		return $result;
	}

	/**
	 * Retrieves the column names of a given DB table name.
	 *
	 * @param	string		the name of a existing DB table (must not be empty, must exist)
	 *
	 * @return	array		array with the column names as values
	 *
	 * @access	protected
	 */
	function getDbColumnNames($tableName) {
		$columns = $GLOBALS['TYPO3_DB']->admin_get_fields($tableName);

		return array_keys($columns);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_oe_configcheck.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_oe_configcheck.php']);
}

?>
