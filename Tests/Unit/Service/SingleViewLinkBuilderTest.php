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
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_Service_SingleViewLinkBuilderTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_TestingFramework
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


	protected function setUp() {
		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');

		$this->postBackup = $_POST;
		$this->getBackup = $_GET;
		$this->typo3confVarsBackup = $GLOBALS['TYPO3_CONF_VARS'];

		Tx_Oelib_ConfigurationRegistry::getInstance()
			->set('plugin.tx_seminars_pi1', new Tx_Oelib_Configuration());
	}

	protected function tearDown() {
		$GLOBALS['TYPO3_CONF_VARS'] = $this->typo3confVarsBackup;
		$_GET = $this->getBackup;
		$_POST = $this->postBackup;

		$this->testingFramework->cleanUp();
	}


	///////////////////////////////////////////////
	// Tests concerning getSingleViewPageForEvent
	///////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getSingleViewPageForEventForEventWithSingleViewPageAndNoConfigurationReturnsSingleViewPageFromEvent() {
		$event = $this->getMock(
			Tx_Seminars_Model_Event::class,
			array('hasCombinedSingleViewPage', 'getCombinedSingleViewPage')
		);
		$event->expects(self::any())->method('hasCombinedSingleViewPage')
			->will(self::returnValue(TRUE));
		$event->expects(self::any())->method('getCombinedSingleViewPage')
			->will(self::returnValue('42'));

		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_Service_TestingSingleViewLinkBuilder',
			array(
				'configurationHasSingleViewPage',
				'getSingleViewPageFromConfiguration',
			)
		);
		$fixture->expects(self::any())->method('configurationHasSingleViewPage')
			->will(self::returnValue(FALSE));
		$fixture->expects(self::any())
			->method('getSingleViewPageFromConfiguration')
			->will(self::returnValue(0));

		self::assertEquals(
			'42',
			$fixture->getSingleViewPageForEvent($event)
		);
	}

	/**
	 * @test
	 */
	public function getSingleViewPageForEventForEventWithoutSingleViewPageReturnsSingleViewPageFromConfiguration() {
		$event = $this->getMock(
			Tx_Seminars_Model_Event::class,
			array('hasCombinedSingleViewPage', 'getCombinedSingleViewPage')
		);
		$event->expects(self::any())->method('hasCombinedSingleViewPage')
			->will(self::returnValue(FALSE));
		$event->expects(self::any())->method('getCombinedSingleViewPage')
			->will(self::returnValue(''));

		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_Service_TestingSingleViewLinkBuilder',
			array(
				'configurationHasSingleViewPage',
				'getSingleViewPageFromConfiguration',
			)
		);
		$fixture->expects(self::any())->method('configurationHasSingleViewPage')
			->will(self::returnValue(TRUE));
		$fixture->expects(self::any())
			->method('getSingleViewPageFromConfiguration')
			->will(self::returnValue(91));

		self::assertEquals(
			'91',
			$fixture->getSingleViewPageForEvent($event)
		);
	}

	/**
	 * @test
	 */
	public function getSingleViewPageForEventForEventAndConfigurationWithSingleViewPageReturnsSingleViewPageFromEvent() {
		$event = $this->getMock(
			Tx_Seminars_Model_Event::class,
			array('hasCombinedSingleViewPage', 'getCombinedSingleViewPage')
		);
		$event->expects(self::any())->method('hasCombinedSingleViewPage')
			->will(self::returnValue(TRUE));
		$event->expects(self::any())->method('getCombinedSingleViewPage')
			->will(self::returnValue('42'));

		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_Service_TestingSingleViewLinkBuilder',
			array(
				'configurationHasSingleViewPage',
				'getSingleViewPageFromConfiguration',
			)
		);
		$fixture->expects(self::any())->method('configurationHasSingleViewPage')
			->will(self::returnValue(TRUE));
		$fixture->expects(self::any())
			->method('getSingleViewPageFromConfiguration')
			->will(self::returnValue(91));

		self::assertEquals(
			'42',
			$fixture->getSingleViewPageForEvent($event)
		);
	}

	/**
	 * @test
	 */
	public function getSingleViewPageForEventForEventWithoutSingleViewPageAndConfigurationWithoutSettingReturnsEmptyString() {
		$event = $this->getMock(
			Tx_Seminars_Model_Event::class,
			array('hasCombinedSingleViewPage', 'getCombinedSingleViewPage')
		);
		$event->expects(self::any())->method('hasCombinedSingleViewPage')
			->will(self::returnValue(FALSE));
		$event->expects(self::any())->method('getCombinedSingleViewPage')
			->will(self::returnValue(''));

		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_Service_TestingSingleViewLinkBuilder',
			array(
				'configurationHasSingleViewPage',
				'getSingleViewPageFromConfiguration',
			)
		);
		$fixture->expects(self::any())->method('configurationHasSingleViewPage')
			->will(self::returnValue(FALSE));
		$fixture->expects(self::any())
			->method('getSingleViewPageFromConfiguration')
			->will(self::returnValue(0));

		self::assertEquals(
			'',
			$fixture->getSingleViewPageForEvent($event)
		);
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
		$fixture->expects(self::any())
			->method('getSingleViewPageFromConfiguration')
			->will(self::returnValue(0));

		self::assertFalse(
			$fixture->configurationHasSingleViewPage()
		);
	}

	/**
	 * @test
	 */
	public function configurationHasSingleViewPageForNonZeroPageFromConfigurationReturnsTrue() {
		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_Service_TestingSingleViewLinkBuilder',
			array('getSingleViewPageFromConfiguration')
		);
		$fixture->expects(self::any())
			->method('getSingleViewPageFromConfiguration')
			->will(self::returnValue(42));

		self::assertTrue(
			$fixture->configurationHasSingleViewPage()
		);
	}


	////////////////////////////////////////////////////////
	// Tests concerning getSingleViewPageFromConfiguration
	////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getSingleViewPageFromConfigurationForPluginSetReturnsPageUidFromPluginConfiguration() {
		$plugin = $this->getMock(
			'Tx_Oelib_TemplateHelper',
			array('hasConfValueInteger', 'getConfValueInteger')
		);
		$plugin->expects(self::any())->method('hasConfValueInteger')
			->will(self::returnValue(TRUE));
		$plugin->expects(self::any())->method('getConfValueInteger')
			->with('detailPID')->will(self::returnValue(42));

		$fixture = new tx_seminars_tests_fixtures_Service_TestingSingleViewLinkBuilder();
		$fixture->setPlugin($plugin);

		self::assertEquals(
			42,
			$fixture->getSingleViewPageFromConfiguration()
		);
	}

	/**
	 * @test
	 */
	public function getSingleViewPageFromConfigurationForNoPluginSetReturnsPageUidFromTypoScriptSetup() {
		Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars_pi1')
			->set('detailPID', 91);

		$fixture = new tx_seminars_tests_fixtures_Service_TestingSingleViewLinkBuilder();

		self::assertEquals(
			91,
			$fixture->getSingleViewPageFromConfiguration()
		);
	}


	/////////////////////////////////////////////////////////////////////////////
	// Tests concerning createAbsoluteUrlForEvent and createRelativeUrlForEvent
	/////////////////////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function createAbsoluteUrlForEventReturnsRelativeUrlMadeAbsolute() {
		$relativeUrl = 'index.php?id=42&tx_seminars%5BshowUid%5D=17';
		$event = $this->getMock(Tx_Seminars_Model_Event::class);

		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_Service_TestingSingleViewLinkBuilder',
			array('createRelativeUrlForEvent')
		);
		$fixture->expects(self::any())->method('createRelativeUrlForEvent')
			->will(self::returnValue($relativeUrl));

		self::assertEquals(
			GeneralUtility::locationHeaderUrl($relativeUrl),
			$fixture->createAbsoluteUrlForEvent($event)
		);
	}

	/**
	 * @test
	 */
	public function createRelativeUrlForEventCreatesUrlViaTslibContent() {
		$eventUid = 19;
		$singleViewPageUid = 42;

		$event = $this->getMock(Tx_Seminars_Model_Event::class, array('getUid'));
		$event->expects(self::any())->method('getUid')
			->will(self::returnValue($eventUid));

		$contentObject = $this->getMock(ContentObjectRenderer::class, array('typoLink_URL'));
		$contentObject->expects(self::once())->method('typoLink_URL')
			->with(array(
				'parameter' => (string) $singleViewPageUid,
				'additionalParams' => '&tx_seminars_pi1%5BshowUid%5D=' . $eventUid
			));

		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_Service_TestingSingleViewLinkBuilder',
			array('getContentObject', 'getSingleViewPageForEvent')
		);
		$fixture->expects(self::any())->method('getContentObject')
			->will(self::returnValue($contentObject));
		$fixture->expects(self::any())
			->method('getSingleViewPageForEvent')
			->will(self::returnValue($singleViewPageUid));

		$fixture->createRelativeUrlForEvent($event);
	}

	/**
	 * @test
	 */
	public function createRelativeUrlReturnsUrlFromTypolinkUrl() {
		$relativeUrl = 'index.php?id=42&tx_seminars%5BshowUid%5D=17';

		$contentObject = $this->getMock(ContentObjectRenderer::class, array('typoLink_URL'));
		$contentObject->expects(self::once())->method('typoLink_URL')
			->will(self::returnValue($relativeUrl));

		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_Service_TestingSingleViewLinkBuilder',
			array('getContentObject', 'getSingleViewPageForEvent')
		);
		$fixture->expects(self::any())->method('getContentObject')
			->will(self::returnValue($contentObject));

		$event = $this->getMock(Tx_Seminars_Model_Event::class);

		self::assertEquals(
			$relativeUrl,
			$fixture->createRelativeUrlForEvent($event)
		);
	}

	/**
	 * @test
	 */
	public function createAbsoluteUrlForEventWithExternalDetailsPageAddsProtocolAndNoSeminarParameter() {
		$event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
			->getLoadedTestingModel(array('details_page' => 'www.example.com'));

		$fixture = new tx_seminars_tests_fixtures_Service_TestingSingleViewLinkBuilder();

		self::assertEquals(
			'http://www.example.com',
			$fixture->createAbsoluteUrlForEvent($event)
		);
	}

	/**
	 * @test
	 */
	public function createAbsoluteUrlForEventWithInternalDetailsPageAddsSeminarParameter() {
		$pageUid = $this->testingFramework->createFrontEndPage();

		$event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
			->getLoadedTestingModel(array('details_page' => $pageUid));

		$fixture = new tx_seminars_tests_fixtures_Service_TestingSingleViewLinkBuilder();

		self::assertContains(
			'?id=' . $pageUid . '&tx_seminars_pi1%5BshowUid%5D=' . $event->getUid(),
			$fixture->createAbsoluteUrlForEvent($event)
		);
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

		self::assertSame(
			$GLOBALS['TSFE']->cObj,
			$fixture->getContentObject()
		);
	}

	/**
	 * @test
	 */
	public function getContentObjectForNoFrontEndReturnsContentObjectRenderer() {
		$fixture = new tx_seminars_tests_fixtures_Service_TestingSingleViewLinkBuilder();

		self::assertInstanceOf(ContentObjectRenderer::class, $fixture->getContentObject());
	}
}