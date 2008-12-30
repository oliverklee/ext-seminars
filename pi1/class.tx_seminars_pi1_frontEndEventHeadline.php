<?php
/***************************************************************
* Copyright notice
*
* (c) 2008 Bernd Schönbach <bernd@oliverklee.de>
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
require_once(t3lib_extMgm::extPath('seminars') . 'pi1/class.tx_seminars_frontEndView.php');

/**
 * Class 'eventHeadline' for the 'seminars' extension.
 *
 * This class displayes an event headline consisting of the event title and
 * date.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_seminars_pi1_frontEndEventHeadline extends tx_seminars_frontEndView {
	/**
	 * @var string path to this script relative to the extension dir
	 */
	public $scriptRelPath = 'pi1/class.tx_seminars_pi1_frontEndEventHeadline.php';

	/**
	 * Creates the event headline, consisting of the event title and date.
	 *
	 * @return string HTML code of the event headline, will be empty if
	 *                an invalid or no event ID was set in piVar 'uid'
	 */
	public function render() {
		$eventId = intval($this->piVars['uid']);
		if ($eventId <= 0) {
			return '';
		}

		$seminarClassName = t3lib_div::makeInstanceClassName(
			'tx_seminars_seminar'
		);
		$seminar = new $seminarClassName($eventId);
		if (!$seminar->isOk()) {
			return '';
		}

		$this->setMarker('title_and_date', $seminar->getTitleAndDate());
		$result = $this->getSubpart('VIEW_HEADLINE');

		$this->setErrorMessage(
			$seminar->checkConfiguration(true)
		);

		$seminar->__destruct();
		unset($seminar);

		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1_frontEndEventHeadline.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1_frontEndEventHeadline.php']);
}
?>
