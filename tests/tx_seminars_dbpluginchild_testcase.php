<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2008 Mario Rimann (typo3-coding@rimann.org)
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
 * Testcase for the dbplugin class in the 'seminars' extensions.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 *
 * @author		Mario Rimann <typo3-coding@rimann.org>
 */

require_once(t3lib_extMgm::extPath('seminars')
	.'tests/fixtures/class.tx_seminars_dbpluginchild.php');

class tx_seminars_dbpluginchild_testcase extends tx_phpunit_testcase {
	private $fixture;

	protected function setUp() {
		$this->fixture = new tx_seminars_dbpluginchild(array());
	}

	protected function tearDown() {
		unset($this->fixture);
	}


	public function testGetBooleanAsTextWithBooleanValues() {
		$this->assertEquals(
			'yes',
			$this->fixture->getBooleanAsText(true)
		);
		$this->assertEquals(
			'no',
			$this->fixture->getBooleanAsText(false)
		);
	}

	public function testGetBooleanAsTextWithIntegerValues() {
		$this->assertEquals(
			'yes',
			$this->fixture->getBooleanAsText(1)
		);
		$this->assertEquals(
			'no',
			$this->fixture->getBooleanAsText(0)
		);
	}

	public function testGetBooleanAsTextWithStringValues() {
		$this->assertEquals(
			'no',
			$this->fixture->getBooleanAsText('0')
		);
		$this->assertEquals(
			'yes',
			$this->fixture->getBooleanAsText('1')
		);
		$this->assertEquals(
			'no',
			$this->fixture->getBooleanAsText('')
		);
	}

	public function testGetBooleanAsTextWithBooleanValuesForeignLanguage() {
		$this->fixture->setLanguage('de');
		$this->assertEquals(
			'ja',
			$this->fixture->getBooleanAsText(true)
		);
		$this->assertEquals(
			'nein',
			$this->fixture->getBooleanAsText(false)
		);
	}


	///////////////////////////////////////////////////////
	// Tests for setting and reading configuration values.
	///////////////////////////////////////////////////////

	public function testSetConfigurationValueStringNotEmpty() {
		$this->fixture->setConfigurationValue('test', 'This is a test.');
		$this->assertEquals(
			'This is a test.', $this->fixture->getConfValueString('test')
		);
	}
}
?>