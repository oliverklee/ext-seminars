<?php
/***************************************************************
* Copyright notice
*
* (c) 2010 Oliver Klee <typo3-coding@oliverklee.de>
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
 * Class tx_seminars_tests_fixtures_Service_TestingSingleViewLinkBuilder for the "seminars"
 * extension.
 *
 * This class just makes some functions from the class
 * tx_seminars_Service_SingleViewLinkBuilder public for testing purposes.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_tests_fixtures_Service_TestingSingleViewLinkBuilder extends tx_seminars_Service_SingleViewLinkBuilder {
	/**
	 * Retrieves a content object to be used for creating typolinks.
	 *
	 * @return tslib_cObj a content object for creating typolinks
	 */
	public function getContentObject() {
		return parent::getContentObject();
	}

	/**
	 * Creates a content object.
	 *
	 * @return tslib_cObj
	 *         a created content object (will always be the same instance)
	 */
	public function createContentObject() {
		return parent::createContentObject();
	}

	/**
	 * Gets the single view page UID/URL from $event (if any single view page is set for
	 * the event) or from the configuration.
	 *
	 * @param $event the event for which to get the single view page
	 *
	 * @return string
	 *         the single view page UID/URL for $event, will be empty if neither
	 *         the event nor the configuration has any single view page set
	 */
	public function getSingleViewPageForEvent(tx_seminars_Model_Event $event) {
		return parent::getSingleViewPageForEvent($event);
	}

	/**
	 * Checks whether there is a single view page set in the configuration.
	 *
	 * @return boolean
	 *         TRUE if a single view page has been set in the configuration,
	 *         FALSE otherwise
	 */
	public function configurationHasSingleViewPage() {
		return parent::configurationHasSingleViewPage();
	}

	/**
	 * Retrieves the single view page UID from the flexforms/TS Setup
	 * configuration.
	 *
	 * @return integer
	 *         the single view page UID from the configuration, will be 0 if no
	 *         page UID has been set
	 */
	public function getSingleViewPageFromConfiguration() {
		return parent::getSingleViewPageFromConfiguration();
	}
}
?>