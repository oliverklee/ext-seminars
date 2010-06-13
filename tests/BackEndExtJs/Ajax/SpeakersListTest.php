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
require_once(PATH_typo3 . 'classes/class.typo3ajax.php');

/**
 * Testcase for the tx_seminars_BackEndExtJs_Ajax_SpeakersList class in the
 * "seminars" extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_BackEndExtJs_Ajax_SpeakersListTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_tests_fixtures_BackEndExtJs_Ajax_TestingSpeakersList
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_seminars_tests_fixtures_BackEndExtJs_Ajax_TestingSpeakersList();
	}

	public function tearDown() {
		tx_oelib_MapperRegistry::purgeInstance();
		unset($this->fixture);
	}


	/**
	 * @test
	 */
	public function mapperNameIsSetToEventsMapper() {
		$this->assertEquals(
			'tx_seminars_Mapper_Speaker',
			$this->fixture->getMapperName()
		);
	}


	///////////////////////////////////////////
	// Tests regarding getAsArray().
	///////////////////////////////////////////

	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingSpeakerUid() {
		$speaker = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Speaker')
			->getLoadedTestingModel(array());

		$result = $this->fixture->getAsArray($speaker);

		$this->assertEquals(
			$speaker->getUid(),
			$result['uid']
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingSpeakerName() {
		$speaker = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Speaker')
			->getLoadedTestingModel(array('title' => 'testing speaker'));

		$result = $this->fixture->getAsArray($speaker);

		$this->assertEquals(
			$speaker->getName(),
			$result['title']
		);
	}
}
?>