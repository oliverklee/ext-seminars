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

/**
 * This class provides the access check for the CSV export of registrations in the front end.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Csv_FrontEndRegistrationAccessCheck implements Tx_Seminars_Interface_CsvAccessCheck {
	/**
	 * @var tx_seminars_seminar
	 */
	protected $event = NULL;

	/**
	 * Sets the event for the access check.
	 *
	 * @param tx_seminars_seminar $event
	 *
	 * @return void
	 */
	public function setEvent(tx_seminars_seminar $event) {
		$this->event = $event;
	}

	/**
	 * Returns the event for the access check.
	 *
	 * @return tx_seminars_seminar
	 */
	protected function getEvent() {
		return $this->event;
	}

	/**
	 * Checks whether the logged-in user (if any) in the current environment has access to a CSV export.
	 *
	 * @return bool whether the logged-in user (if any) in the current environment has access to a CSV export.
	 *
	 * @throws BadMethodCallException
	 */
	public function hasAccess() {
		if ($this->getEvent() === NULL) {
			throw new BadMethodCallException('Please set an event first.', 1389096647);
		}
		if (!Tx_Oelib_FrontEndLoginManager::getInstance()->isLoggedIn()) {
			return FALSE;
		}

		$configuration = Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars_pi1');
		if (!$configuration->getAsBoolean('allowCsvExportOfRegistrationsInMyVipEventsView')) {
			return FALSE;
		}

		$user = Tx_Oelib_FrontEndLoginManager::getInstance()->getLoggedInUser('tx_seminars_Mapper_FrontEndUser');
		$vipsGroupUid = $configuration->getAsInteger('defaultEventVipsFeGroupID');

		return $this->getEvent()->isUserVip($user->getUid(), $vipsGroupUid);
	}
}