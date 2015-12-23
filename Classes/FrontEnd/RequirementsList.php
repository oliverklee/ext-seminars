<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class is a view which creates the requirements lists for the front end.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_FrontEnd_RequirementsList extends Tx_Seminars_FrontEnd_AbstractView {
	/**
	 * @var tx_seminars_seminar the event to build the requirements list for
	 */
	private $event = NULL;

	/**
	 * @var bool whether to limit the requirements to the events the user
	 *              still needs to register
	 */
	private $limitRequirementsToMissing = FALSE;

	/**
	 * a link builder instance
	 *
	 * @var tx_seminars_Service_SingleViewLinkBuilder
	 */
	private $linkBuilder = NULL;

	/**
	 * The destructor.
	 */
	public function __destruct() {
		unset($this->linkBuilder, $this->event);
		parent::__destruct();
	}

	/**
	 * Sets the event to which this view relates.
	 *
	 * @param tx_seminars_seminar $event the event to build the requirements list for
	 *
	 * @return void
	 */
	public function setEvent(tx_seminars_seminar $event) {
		$this->event = $event;
	}

	/**
	 * Limits the requirements list to the requirements the user still needs to register to.
	 *
	 * @return void
	 */
	public function limitToMissingRegistrations() {
		if (!tx_oelib_FrontEndLoginManager::getInstance()->isLoggedIn()) {
			throw new BadMethodCallException(
				'No FE user is currently logged in. Please call this function only when a FE user is logged in.', 1333293236
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
			throw new BadMethodCallException('No event was set, please set an event before calling render.', 1333293250);
		}

		if ($this->linkBuilder == NULL) {
			/** @var tx_seminars_Service_SingleViewLinkBuilder $linkBuilder */
			$linkBuilder = GeneralUtility::makeInstance('tx_seminars_Service_SingleViewLinkBuilder');
			$this->injectLinkBuilder($linkBuilder);
		}
		$this->linkBuilder->setPlugin($this);

		$output = '';

		/** @var tx_seminars_Mapper_Event $eventMapper */
		$eventMapper = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event');
		$requirements = $this->getRequirements();
		/** @var tx_seminars_seminar $requirement */
		foreach ($requirements as $requirement) {
			/** @var tx_seminars_Model_Event $event */
			$event = $eventMapper->find($requirement->getUid());

			$singleViewUrl = $this->linkBuilder->createRelativeUrlForEvent($event);
			$this->setMarker(
				'requirement_url', htmlspecialchars($singleViewUrl)
			);

			$this->setMarker(
				'requirement_title', htmlspecialchars($event->getTitle())
			);
			$output .= $this->getSubpart('SINGLE_REQUIREMENT');
		}
		$this->setSubpart('SINGLE_REQUIREMENT', $output);

		return $this->getSubpart('FIELD_WRAPPER_REQUIREMENTS');
	}

	/**
	 * Returns the requirements which should be displayed.
	 *
	 * @return Tx_Seminars_Bag_Event the requirements still to be displayed,
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
	 *
	 * @return void
	 */
	public function injectLinkBuilder(
		tx_seminars_Service_SingleViewLinkBuilder $linkBuilder
	) {
		$this->linkBuilder = $linkBuilder;
	}
}