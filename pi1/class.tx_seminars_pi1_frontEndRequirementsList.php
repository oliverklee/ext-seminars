<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2010 Bernd Schönbach <bernd@oliverklee.de>
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
 * Class 'tx_seminars_pi1_frontEndRequirementsList' for the 'seminars' extension.
 *
 * This class is a view which creates the requirements lists for the front end.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_seminars_pi1_frontEndRequirementsList extends tx_seminars_pi1_frontEndView {
	/**
	 * @var tx_seminars_seminar the event to build the requirements list for
	 */
	private $event = null;

	/**
	 * @var boolean whether to limit the requirements to the events the user
	 *              still needs to register
	 */
	private $limitRequirementsToMissing = FALSE;

	/**
	 * The destructor.
	 */
	public function __destruct() {
		unset($this->event);
		parent::__destruct();
	}

	/**
	 * Sets the event to which this view relates.
	 *
	 * @param tx_seminars_seminar the event to build the requirements list for
	 */
	public function setEvent(tx_seminars_seminar $event) {
		$this->event = $event;
	}

	/**
	 * Limits the requirements list to the requirements the user still needs to
	 * register to.
	 */
	public function limitToMissingRegistrations() {
		if (!tx_oelib_FrontEndLoginManager::getInstance()->isLoggedIn()) {
			throw new Exception(
				'No FE user is currently logged in. Please call ' .
				'this function only when a FE user is logged in.'
			);
		}
		$this->setMarker(
			'label_requirements',
			$this->translate('label_registration_requirements')
		);
		$this->limitRequirementsToMissing = TRUE;
	}

	/**
	 * Creates the list of required events.
	 *
	 * @return string HTML code of the list, will not be empty
	 */
	public function render() {
		if (!$this->event) {
			throw new Exception(
				'No event was set, please set an event before calling render.'
			);
		}

		$output = '';

		$requirements = $this->getRequirements();
		foreach ($requirements as $requirement) {
			$this->setMarker(
				'requirement_title',
				$requirement->getLinkedFieldValue($this, 'title')
			);
			$output .= $this->getSubpart('SINGLE_REQUIREMENT');
		}
		$requirements->__destruct();
		$this->setSubpart('SINGLE_REQUIREMENT', $output);

		return $this->getSubpart('FIELD_WRAPPER_REQUIREMENTS');
	}

	/**
	 * Returns the requirements which should be displayed.
	 *
	 * @return tx_seminars_seminarbag the requirements still to be displayed,
	 *                                may be empty
	 */
	private function getRequirements() {
		if ($this->limitRequirementsToMissing) {
			$result = tx_seminars_registrationmanager::getInstance()
				->getMissingRequiredTopics($this->event);
		} else {
			$result = $this->event->getRequirements();
		}

		return $result;
	}
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1_frontEndRequirementsList.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1_frontEndRequirementsList.php']);
}
?>