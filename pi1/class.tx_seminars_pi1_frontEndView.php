<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2009 Niels Pardon (mail@niels-pardon.de)
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
 * Class 'frontEndView' for the 'seminars' extension.
 *
 * This class represents a basic view.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
abstract class tx_seminars_pi1_frontEndView extends tx_oelib_templatehelper {
	/**
	 * @var string same as plugin name
	 */
	public $prefixId = 'tx_seminars_pi1';

	/**
	 * @var string the extension key
	 */
	public $extKey = 'seminars';

	/**
	 * The constructor. Initializes the TypoScript configuration, initializes
	 * the flex forms, gets the template HTML code, sets the localized labels
	 * and set the CSS classes from TypoScript.
	 *
	 * @param array TypoScript configuration for the plugin
	 * @param tslib_cObj the parent cObj content, needed for the flexforms
	 */
	public function __construct(array $configuration, tslib_cObj $cObj) {
		$this->cObj = $cObj;
		$this->init($configuration);
		$this->pi_initPIflexForm();

		$this->getTemplateCode();
		$this->setLabels();
		$this->setCSS();
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		parent::__destruct();
	}

	/**
	 * Renders the view and returns its content.
	 *
	 * @return string the view's content
	 */
	abstract public function render();
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1_frontEndView.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1_frontEndView.php']);
}
?>