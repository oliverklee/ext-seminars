<?php
/***************************************************************
* Copyright notice
*
* (c) 2010-2012 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for the tx_seminars_Service_SingleViewLinkBuilder class in the
 * "seminars" extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_Service_SingleViewLinkBuilderTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * backup of $_POST
	 *
	 * @var array
	 */
	private $postBackup;

	/**
	 * backup of $_GET
	 *
	 * @var array
	 */
	private $getBackup;

	/**
	 * backup of $GLOBALS['TYPO3_CONF_VARS']
	 *
	 * @var array
	 */
	private $typo3confVarsBackup;


	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');

		$this->postBackup = $_POST;
		$this->getBackup = $_GET;
		$this->typo3confVarsBackup = $GLOBALS['TYPO3_CONF_VARS'];

		tx_oelib_ConfigurationRegistry::getInstance()
			->set('plugin.tx_seminars_pi1', new tx_oelib_Configuration());
	}

	public function tearDown() {
		$GLOBALS['TYPO3_CONF_VARS'] = $this->typo3confVarsBackup;
		$_GET = $this->getBackup;
		$_POST = $this->postBackup;

		$this->testingFramework->cleanUp();
		unset($this->testingFramework);
	}


	///////////////////////////////////////////////
	// Tests concerning getSingleViewPageForEvent
	///////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getSingleViewPageForEventForEventWithSingleViewPageAndNoConfigurationReturnsSingleViewPageFromEvent() {
		$event = $this->getMock(
			'tx_seminars_Model_Event',
			array('hasCombinedSingleViewPage', 'getCombinedSingleViewPage')
		);
		$event->expects($this->any())->method('hasCombinedSingleViewPage')
			->will($this->returnValue(TRUE));
		$event->expects($this->any())->method('getCombinedSingleViewPage')
			->will($this->returnValue('42'));

		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_Service_TestingSingleViewLinkBuilder',
			array(
				'configurationHasSingleViewPage',
				'getSingleViewPageFromConfiguration',
			)
		);
		$fixture->expects($this->any())->method('configurationHasSingleViewPage')
			->will($this->returnValue(FALSE));
		$fixture->expects($this->any())
			->method('getSingleViewPageFromConfiguration')
			->will($this->returnValue(0));

		$this->assertEquals(
			'42',
			$fixture->getSingleViewPageForEvent($event)
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function getSingleViewPageForEventForEventWithoutSingleViewPageReturnsSingleViewPageFromConfiguration() {
		$event = $this->getMock(
			'tx_seminars_Model_Event',
			array('hasCombinedSingleViewPage', 'getCombinedSingleViewPage')
		);
		$event->expects($this->any())->method('hasCombinedSingleViewPage')
			->will($this->returnValue(FALSE));
		$event->expects($this->any())->method('getCombinedSingleViewPage')
			->will($this->returnValue(''));

		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_Service_TestingSingleViewLinkBuilder',
			array(
				'configurationHasSingleViewPage',
				'getSingleViewPageFromConfiguration',
			)
		);
		$fixture->expects($this->any())->method('configurationHasSingleViewPage')
			->will($this->returnValue(TRUE));
		$fixture->expects($this->any())
			->method('getSingleViewPageFromConfiguration')
			->will($this->returnValue(91));

		$this->assertEquals(
			'91',
			$fixture->getSingleViewPageForEvent($event)
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function getSingleViewPageForEventForEventAndConfigurationWithSingleViewPageReturnsSingleViewPageFromEvent() {
		$event = $this->getMock(
			'tx_seminars_Model_Event',
			array('hasCombinedSingleViewPage', 'getCombinedSingleViewPage')
		);
		$event->expects($this->any())->method('hasCombinedSingleViewPage')
			->will($this->returnValue(TRUE));
		$event->expects($this->any())->method('getCombinedSingleViewPage')
			->will($this->returnValue('42'));

		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_Service_TestingSingleViewLinkBuilder',
			array(
				'configurationHasSingleViewPage',
				'getSingleViewPageFromConfiguration',
			)
		);
		$fixture->expects($this->any())->method('configurationHasSingleViewPage')
			->will($this->returnValue(TRUE));
		$fixture->expects($this->any())
			->method('getSingleViewPageFromConfiguration')
			->will($this->returnValue(91));

		$this->assertEquals(
			'42',
			$fixture->getSingleViewPageForEvent($event)
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function getSingleViewPageForEventForEventWithoutSingleViewPageAndConfigurationWithoutSettingReturnsEmptyString() {
		$event = $this->getMock(
			'tx_seminars_Model_Event',
			array('hasCombinedSingleViewPage', 'getCombinedSingleViewPage')
		);
		$event->expects($this->any())->method('hasCombinedSingleViewPage')
			->will($this->returnValue(FALSE));
		$event->expects($this->any())->method('getCombinedSingleViewPage')
			->will($this->returnValue(''));

		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_Service_TestingSingleViewLinkBuilder',
			array(
				'configurationHasSingleViewPage',
				'getSingleViewPageFromConfiguration',
			)
		);
		$fixture->expects($this->any())->method('configurationHasSingleViewPage')
			->will($this->returnValue(FALSE));
		$fixture->expects($this->any())
			->method('getSingleViewPageFromConfiguration')
			->will($this->returnValue(0));

		$this->assertEquals(
			'',
			$fixture->getSingleViewPageForEvent($event)
		);

		$fixture->__destruct();
	}


	////////////////////////////////////////////////////
	// Tests concerning configurationHasSingleViewPage
	////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function configurationHasSingleViewPageForZeroPageFromConfigurationReturnsFalse() {
		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_Service_TestingSingleViewLinkBuilder',
			array('getSingleViewPageFromConfiguration')
		);
		$fixture->expects($this->any())
			->method('getSingleViewPageFromConfiguration')
			->will($this->returnValue(0));

		$this->assertFalse(
			$fixture->configurationHasSingleViewPage()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function configurationHasSingleViewPageForNonZeroPageFromConfigurationReturnsTrue() {
		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_Service_TestingSingleViewLinkBuilder',
			array('getSingleViewPageFromConfiguration')
		);
		$fixture->expects($this->any())
			->method('getSingleViewPageFromConfiguration')
			->will($this->returnValue(42));

		$this->assertTrue(
			$fixture->configurationHasSingleViewPage()
		);

		$fixture->__destruct();
	}


	////////////////////////////////////////////////////////
	// Tests concerning getSingleViewPageFromConfiguration
	////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getSingleViewPageFromConfigurationForPluginSetReturnsPageUidFromPluginConfiguration() {
		$plugin = $this->getMock(
			'tx_oelib_templatehelper',
			array('hasConfValueInteger', 'getConfValueInteger')
		);
		$plugin->expects($this->any())->method('hasConfValueInteger')
			->will($this->returnValue(TRUE));
		$plugin->expects($this->any())->method('getConfValueInteger')
			->with('detailPID')->will($this->returnValue(42));

		$fixture = new tx_seminars_tests_fixtures_Service_TestingSingleViewLinkBuilder();
		$fixture->setPlugin($plugin);

		$this->assertEquals(
			42,
			$fixture->getSingleViewPageFromConfiguration()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function getSingleViewPageFromConfigurationForNoPluginSetReturnsPageUidFromTypoScriptSetup() {
		tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars_pi1')
			->set('detailPID', 91);

		$fixture = new tx_seminars_tests_fixtures_Service_TestingSingleViewLinkBuilder();

		$this->assertEquals(
			91,
			$fixture->getSingleViewPageFromConfiguration()
		);

		$fixture->__destruct();
	}


	/////////////////////////////////////////////////////////////////////////////
	// Tests concerning createAbsoluteUrlForEvent and createRelativeUrlForEvent
	/////////////////////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function createAbsoluteUrlForEventReturnsRelativeUrlMadeAbsolute() {
		$relativeUrl = 'index.php?id=42&tx_seminars%5BshowUid%5D=17';
		$event = $this->getMock('tx_seminars_Model_Event');

		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_Service_TestingSingleViewLinkBuilder',
			array('createRelativeUrlForEvent')
		);
		$fixture->expects($this->any())->method('createRelativeUrlForEvent')
			->will($this->returnValue($relativeUrl));

		$this->assertEquals(
			t3lib_div::locationHeaderUrl($relativeUrl),
			$fixture->createAbsoluteUrlForEvent($event)
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function createRelativeUrlForEventCreatesUrlViaTslibContent() {
		$eventUid = 19;
		$singleViewPageUid = 42;

		$event = $this->getMock('tx_seminars_Model_Event', array('getUid'));
		$event->expects($this->any())->method('getUid')
			->will($this->returnValue($eventUid));

		$contentObject = $this->getMock('tslib_cObj', array('typoLink_URL'));
		$contentObject->expects($this->once())->method('typoLink_URL')
			->with(array(
				'parameter' => (string) $singleViewPageUid,
				'additionalParams' => '&tx_seminars_pi1%5BshowUid%5D=' . $eventUid
			));

		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_Service_TestingSingleViewLinkBuilder',
			array('getContentObject', 'getSingleViewPageForEvent')
		);
		$fixture->expects($this->any())->method('getContentObject')
			->will($this->returnValue($contentObject));
		$fixture->expects($this->any())
			->method('getSingleViewPageForEvent')
			->will($this->returnValue($singleViewPageUid));

		$fixture->createRelativeUrlForEvent($event);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function createRelativeUrlReturnsUrlFromTypolinkUrl() {
		$relativeUrl = 'index.php?id=42&tx_seminars%5BshowUid%5D=17';

		$contentObject = $this->getMock('tslib_cObj', array('typoLink_URL'));
		$contentObject->expects($this->once())->method('typoLink_URL')
			->will($this->returnValue($relativeUrl));

		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_Service_TestingSingleViewLinkBuilder',
			array('getContentObject', 'getSingleViewPageForEvent')
		);
		$fixture->expects($this->any())->method('getContentObject')
			->will($this->returnValue($contentObject));

		$event = $this->getMock('tx_seminars_Model_Event');

		$this->assertEquals(
			$relativeUrl,
			$fixture->createRelativeUrlForEvent($event)
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function createAbsoluteUrlForEventWithExternalDetailsPageAddsProtocolAndNoSeminarParameter() {
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('details_page' => 'www.example.com'));

		$fixture = new tx_seminars_tests_fixtures_Service_TestingSingleViewLinkBuilder();

		$this->assertEquals(
			'http://www.example.com',
			$fixture->createAbsoluteUrlForEvent($event)
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function createAbsoluteUrlForEventWithInternalDetailsPageAddsSeminarParameter() {
		$pageUid = $this->testingFramework->createFrontEndPage();

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('details_page' => $pageUid));

		$fixture = new tx_seminars_tests_fixtures_Service_TestingSingleViewLinkBuilder();

		$this->assertContains(
			'?id=' . $pageUid . '&tx_seminars_pi1%5BshowUid%5D=' . $event->getUid(),
			$fixture->createAbsoluteUrlForEvent($event)
		);

		$fixture->__destruct();
	}


	//////////////////////////////////////
	// Tests concerning getContentObject
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function getContentObjectForAvailableFrontEndReturnsFrontEndContentObject() {
		$this->testingFramework->createFakeFrontEnd();

		$fixture = new tx_seminars_tests_fixtures_Service_TestingSingleViewLinkBuilder();

		$this->assertSame(
			$GLOBALS['TSFE']->cObj,
			$fixture->getContentObject()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function getContentObjectForNoFrontEndReturnsContentObject() {
		$fixture = new tx_seminars_tests_fixtures_Service_TestingSingleViewLinkBuilder();

		$this->assertTrue(
			$fixture->getContentObject() instanceof tslib_cObj
		);

		$fixture->__destruct();
	}
}
?>