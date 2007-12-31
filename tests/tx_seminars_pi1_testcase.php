<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2008 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for the pi1 class in the 'seminars' extension.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'pi1/class.tx_seminars_pi1.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_registrationmanager.php');

class tx_seminars_pi1_testcase extends tx_phpunit_testcase {
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_seminars_pi1();
		$this->fixture->init(array());
		$this->fixture->createHelperObjects();
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'eventType', 'Workshop'
		);
	}

	public function tearDown() {
		unset($this->fixture);
	}


	public function testPi1MustBeInitialized() {
		$this->assertNotNull(
			$this->fixture
		);
		$this->assertTrue(
			$this->fixture->isInitialized()
		);
	}

	public function testEventTypeInConfigurationIsSet() {
		$this->assertEquals(
			'Workshop',
			$this->fixture->getConfigGetter()->getConfValueString('eventType')
		);
	}

	public function testSearchWhereCreatesAnEmptyStringForEmptySearchWords() {
		$this->assertEquals(
			'', $this->fixture->searchWhere('')
		);
	}

	public function testSearchWhereCreatesSomethingForNonEmptySearchWords() {
		$this->assertNotEquals(
			'', $this->fixture->searchWhere('foo')
		);
		$this->assertContains(
			'foo', $this->fixture->searchWhere('foo')
		);
	}

	public function testSearchWhereCreatesAndOnlyAtTheStartForOneSearchWord() {
		$this->assertTrue(
			strpos($this->fixture->searchWhere('foo'), ' AND ') === 0
		);
	}

	public function testSearchWhereForDefaultEventTypeContainsEventType0() {
		$this->assertContains(
			'.event_type=0', $this->fixture->searchWhere('Workshop')
		);
	}

	public function testSearchWhereForPartOfDefaultEventTypeContainsEventType0() {
		$this->assertContains(
			'.event_type=0', $this->fixture->searchWhere('Work')
		);
		$this->assertContains(
			'.event_type=0', $this->fixture->searchWhere('ork')
		);
		$this->assertContains(
			'.event_type=0', $this->fixture->searchWhere('hop')
		);
	}

	public function testSearchWhereForDifferenteFromEventTypeNotContainsEventType0() {
		$this->assertNotContains(
			'.event_type=0', $this->fixture->searchWhere('Seminar')
		);
	}

	public function testAddEmptyOptionIfNeededDisabled() {
		$allLanguages = array(
			'DE' => 'Deutsch',
			'FR' => 'French'
		);
		$this->fixture->addEmptyOptionIfNeeded($allLanguages);
		$this->assertEquals(
			array(
				'DE' => 'Deutsch',
				'FR' => 'French'
			),
			$allLanguages
		);
	}

	public function testAddEmptyOptionIfNeededActivated() {
		$this->fixture->setConfigurationValue(
			'showEmptyEntryInOptionLists', '1'
		);

		$allOptions = array(
			'DE' => 'Deutsch',
			'FR' => 'French'
		);
		$this->fixture->addEmptyOptionIfNeeded($allOptions);
		$this->assertEquals(
			array(
				'none' => '',
				'DE' => 'Deutsch',
				'FR' => 'French'
			),
			$allOptions
		);
	}
}

?>
