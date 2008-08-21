<?php
/***************************************************************
* Copyright notice
*
* (c) 2008 Niels Pardon (mail@niels-pardon.de)
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
 * Testcase for the registrationEditorChild class in the 'seminars' extensions.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 *
 * @author		Niels Pardon <mail@niels-pardon.de>
 */

require_once(PATH_tslib . 'class.tslib_content.php');
require_once(PATH_tslib . 'class.tslib_feuserauth.php');
require_once(PATH_t3lib . 'class.t3lib_timetrack.php');

require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars') . 'tests/fixtures/class.tx_seminars_registrationEditorChild.php');
require_once(t3lib_extMgm::extPath('seminars') . 'pi1/class.tx_seminars_pi1.php');

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_testingFramework.php');

class tx_seminars_registrationEditorChild_testcase extends tx_phpunit_testcase {
	private $fixture;

	/** our instance of the testing framework */
	private $testingFramework;

	/** the UID of a dummy front end page */
	private $frontEndPageUid;

	/** the instance of tx_seminars_pi1*/
	private $pi1;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->frontEndPageUid = $this->testingFramework->createFrontEndPage();
		$this->testingFramework->createFakeFrontEnd($this->frontEndPageUid);

		$seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);

		$this->pi1 = new tx_seminars_pi1();
		$this->pi1->createSeminar($seminarUid);
		$this->pi1->init(
			array(
				'isStaticTemplateLoaded' => 1,
				'pageToShowAfterUnregistrationPID' => $this->frontEndPageUid,
				'sendParametersToThankYouAfterRegistrationPageUrl' => 1,
				'thankYouAfterRegistrationPID' => $this->frontEndPageUid,
				'sendParametersToPageToShowAfterUnregistrationUrl' => 1,
				'templateFile' => 'EXT:seminars/pi1/seminars_pi1.tmpl',
			)
		);
		$this->pi1->getTemplateCode();
		$this->pi1->setLabels();

		$this->fixture = new tx_seminars_registrationEditorChild($this->pi1);
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();
		unset($this->testingFramework, $this->fixture, $this->pi1);
	}


	////////////////////////////////////////////////////////////////
	// Tests for getting the page-to-show-after-unregistration URL
	////////////////////////////////////////////////////////////////

	public function testGetPageToShowAfterUnregistrationUrlReturnsUrlStartingWithHttp() {
		$this->assertRegExp(
			'/^http:\/\/./',
			$this->fixture->getPageToShowAfterUnregistrationUrl()
		);
	}

	public function testGetPageToShowAfterUnregistrationUrlReturnsUrlWithEncodedBrackets() {
		$this->assertContains(
			'%5BshowUid%5D',
			$this->fixture->getPageToShowAfterUnregistrationUrl()
		);

		$this->assertNotContains(
			'[showUid]',
			$this->fixture->getPageToShowAfterUnregistrationUrl()
		);
	}


	///////////////////////////////////////////////////////////
	// Tests for getting the thank-you-after-registration URL
	///////////////////////////////////////////////////////////

	public function testGetThankYouAfterRegistrationUrlReturnsUrlStartingWithHttp() {
		$this->assertRegExp(
			'/^http:\/\/./',
			$this->fixture->getThankYouAfterRegistrationUrl(array())
		);
	}

	public function testGetThankYouAfterRegistrationUrlReturnsUrlWithEncodedBrackets() {
		$this->assertContains(
			'%5BshowUid%5D',
			$this->fixture->getThankYouAfterRegistrationUrl(array())
		);

		$this->assertNotContains(
			'[showUid]',
			$this->fixture->getThankYouAfterRegistrationUrl(array())
		);
	}


	/////////////////////////////////////
	// Test concerning getAllFeUserData
	/////////////////////////////////////

	public function testGetAllFeUserContainsNonEmptyNameOfFrontEndUser() {
		$this->testingFramework->loginFrontEndUser(
			$this->testingFramework->createFrontEndUser(
				$this->testingFramework->createFrontEndUserGroup(),
				array('name' => 'John Doe')
			)
		);

		$this->assertContains(
			'John Doe',
			$this->fixture->getAllFeUserData()
		);
	}

	public function testGetAllFeUserDoesNotContainEmptyLinesForMissingCompanyName() {
		$this->testingFramework->loginFrontEndUser(
			$this->testingFramework->createFrontEndUser(
				$this->testingFramework->createFrontEndUserGroup(),
				array('name' => 'John Doe')
			)
		);

		$this->assertNotRegExp(
			'/<br \/>\s*<br \/>/',
			$this->fixture->getAllFeUserData()
		);
	}
}
?>