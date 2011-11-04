<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2011 Bernd Schönbach <bernd@oliverklee.de>
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
 * Class tx_seminars_FrontEnd_RequirementsList for the "seminars" extension.
 *
 * This class is a view which creates the requirements lists for the front end.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_FrontEnd_RequirementsList extends tx_seminars_FrontEnd_AbstractView {
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
	 * a link builder instance
	 *
	 * @var tx_seminars_Service_SingleViewLinkBuilder
	 */
	private $linkBuilder = null;

	/**
	 * The destructor.
	 */
	public function __destruct() {
		if ($this->linkBuilder !== null) {
			$this->linkBuilder->__destruct();
			unset($this->linkBuilder);
		}
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

		if ($this->linkBuilder == null) {
			$this->injectLinkBuilder(tx_oelib_ObjectFactory::make(
				'tx_seminars_Service_SingleViewLinkBuilder'
			));
		}
		$this->linkBuilder->setPlugin($this);

		$output = '';

		$eventMapper = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event');
		$requirements = $this->getRequirements();
		foreach ($requirements as $requirement) {
			$event = $eventMapper->find($requirement->getUid());

			$singleViewUrl = $this->linkBuilder->createRelativeUrlForEvent(
				$event
			);
			$this->setMarker(
				'requirement_url', htmlspecialchars($singleViewUrl)
			);

			$this->setMarker(
				'requirement_title', htmlspecialchars($event->getTitle())
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
	 * @return tx_seminars_Bag_Event the requirements still to be displayed,
	 *                               might be empty
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

	/**
	 * Injects a link builder.
	 *
	 * @param tx_seminars_Service_SingleViewLinkBuilder $linkBuilder
	 *        the link builder instance to use
	 */
	public function injectLinkBuilder(
		tx_seminars_Service_SingleViewLinkBuilder $linkBuilder
	) {
		$this->linkBuilder = $linkBuilder;
	}
}
if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/FrontEnd/RequirementsList.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/FrontEnd/RequirementsList.php']);
}
?>