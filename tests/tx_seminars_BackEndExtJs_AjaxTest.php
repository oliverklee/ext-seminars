<?php
/***************************************************************
* Copyright notice
*
* (c) 2010 Niels Pardon (mail@niels-pardon.de)
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

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');

/**
 * Testcase for the tx_seminars_BackEndExtJs_Ajax class in the "seminars"
 * extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_BackEndExtJs_AjaxTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_BackEndExtJs_Ajax
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_seminars_BackEndExtJs_Ajax();
	}

	public function tearDown() {
		unset($this->fixture);
	}


	/////////////////////////////////
	// Tests regarding getEvents().
	/////////////////////////////////

	/**
	 * @test
	 */
	public function getEventsSetsResponseContentFormatToJson() {
		$ajaxObject = $this->getMock(
			'TYPO3AJAX',
			array('setContentFormat', 'setContent')
		);

		$ajaxObject->expects($this->once())
			->method('setContentFormat')
			->with($this->equalTo('json'));

		$this->fixture->getEvents(array(), $ajaxObject);
	}

	/**
	 * @test
	 */
	public function getEventsSetsResponseContent() {
		$ajaxObject = $this->getMock(
			'TYPO3AJAX',
			array('setContentFormat', 'setContent')
		);

		$ajaxObject->expects($this->once())
			->method('setContent');

		$this->fixture->getEvents(array(), $ajaxObject);
	}


	////////////////////////////////////////
	// Tests regarding getRegistrations().
	////////////////////////////////////////

	/**
	 * @test
	 */
	public function getRegistrationsSetsResponseContentFormatToJson() {
		$ajaxObject = $this->getMock(
			'TYPO3AJAX',
			array('setContentFormat', 'setContent')
		);

		$ajaxObject->expects($this->once())
			->method('setContentFormat')
			->with($this->equalTo('json'));

		$this->fixture->getRegistrations(array(), $ajaxObject);
	}

	/**
	 * @test
	 */
	public function getRegistrationsSetsResponseContent() {
		$ajaxObject = $this->getMock(
			'TYPO3AJAX',
			array('setContentFormat', 'setContent')
		);

		$ajaxObject->expects($this->once())
			->method('setContent');

		$this->fixture->getRegistrations(array(), $ajaxObject);
	}


	///////////////////////////////////
	// Tests regarding getSpeakers().
	///////////////////////////////////

	/**
	 * @test
	 */
	public function getSpeakersSetsResponseContentFormatToJson() {
		$ajaxObject = $this->getMock(
			'TYPO3AJAX',
			array('setContentFormat', 'setContent')
		);

		$ajaxObject->expects($this->once())
			->method('setContentFormat')
			->with($this->equalTo('json'));

		$this->fixture->getSpeakers(array(), $ajaxObject);
	}

	/**
	 * @test
	 */
	public function getSpeakersSetsResponseContent() {
		$ajaxObject = $this->getMock(
			'TYPO3AJAX',
			array('setContentFormat', 'setContent')
		);

		$ajaxObject->expects($this->once())
			->method('setContent');

		$this->fixture->getSpeakers(array(), $ajaxObject);
	}


	/////////////////////////////////////
	// Tests regarding getOrganizers().
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function getOrganizersSetsResponseContentFormatToJson() {
		$ajaxObject = $this->getMock(
			'TYPO3AJAX',
			array('setContentFormat', 'setContent')
		);

		$ajaxObject->expects($this->once())
			->method('setContentFormat')
			->with($this->equalTo('json'));

		$this->fixture->getOrganizers(array(), $ajaxObject);
	}

	/**
	 * @test
	 */
	public function getOrganizersSetsResponseContent() {
		$ajaxObject = $this->getMock(
			'TYPO3AJAX',
			array('setContentFormat', 'setContent')
		);

		$ajaxObject->expects($this->once())
			->method('setContentFormat');

		$this->fixture->getOrganizers(array(), $ajaxObject);
	}
}
?>