<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\FrontEnd;

use OliverKlee\Oelib\Http\HeaderProxyFactory;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @covers \Tx_Seminars_FrontEnd_RegistrationsList
 */
final class RegistrationsListTest extends TestCase
{
    use LanguageHelper;

    /**
     * @var \Tx_Seminars_FrontEnd_RegistrationsList
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var int the UID of a seminar to which the fixture relates
     */
    private $seminarUid = 0;

    /**
     * @var int the UID of a front end user for testing purposes
     */
    private $feUserUid = 0;

    /**
     * @var int the UID of a registration for testing purposes
     */
    private $registrationUid = 0;

    protected function setUp()
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        HeaderProxyFactory::getInstance()->enableTestMode();

        $this->testingFramework = new TestingFramework('tx_seminars');
        $this->testingFramework->createFakeFrontEnd();

        $this->seminarUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'title' => 'Test event & more',
                'attendees_max' => 10,
                'needs_registration' => 1,
            ]
        );

        $this->subject = new \Tx_Seminars_FrontEnd_RegistrationsList(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'enableRegistration' => 1,
            ],
            'list_registrations',
            $this->seminarUid,
            $this->getFrontEndController()->cObj
        );
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();

        \Tx_Seminars_Service_RegistrationManager::purgeInstance();
    }

    ///////////////////////
    // Utility functions.
    ///////////////////////

    private function getFrontEndController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * Creates an FE user, registers them to the seminar with the UID in
     * $this->seminarUid and logs them in.
     *
     * Note: This function creates a registration record.
     *
     * @return void
     */
    private function createLogInAndRegisterFrontEndUser()
    {
        $this->feUserUid = $this->testingFramework->createAndLoginFrontEndUser(
            '',
            ['name' => 'Tom & Jerry']
        );
        $this->registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->seminarUid,
                'user' => $this->feUserUid,
            ]
        );
    }

    /////////////////////////////////////
    // Tests for the utility functions.
    /////////////////////////////////////

    /**
     * @test
     */
    public function createLogInAndRegisterFrontEndUserLogsInFrontEndUser()
    {
        $this->createLogInAndRegisterFrontEndUser();

        self::assertTrue(
            $this->testingFramework->isLoggedIn()
        );
    }

    /**
     * @test
     */
    public function createLogInAndRegisterFrontEndUserCreatesRegistrationRecord()
    {
        $this->createLogInAndRegisterFrontEndUser();
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

        $connection = $connectionPool->getConnectionForTable('tx_seminars_attendances');

        self::assertSame(
            1,
            $connection->count('*', 'tx_seminars_attendances', [])
        );
    }

    ////////////////////////////////////
    // Tests for creating the fixture.
    ////////////////////////////////////

    /**
     * @test
     */
    public function createFixtureWithInvalidWhatToDisplayThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The value "foo" of the first parameter $whatToDisplay is not valid.'
        );

        new \Tx_Seminars_FrontEnd_RegistrationsList(
            ['templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html'],
            'foo',
            0,
            $this->getFrontEndController()->cObj
        );
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function createFixtureWithListRegistrationsAsWhatToDisplayDoesNotThrowException()
    {
        new \Tx_Seminars_FrontEnd_RegistrationsList(
            ['templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html'],
            'list_registrations',
            0,
            $this->getFrontEndController()->cObj
        );
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function createFixtureWithListVipRegistrationsAsWhatToDisplayDoesNotThrowException()
    {
        new \Tx_Seminars_FrontEnd_RegistrationsList(
            ['templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html'],
            'list_vip_registrations',
            0,
            $this->getFrontEndController()->cObj
        );
    }

    ///////////////////////
    // Tests for render()
    ///////////////////////

    /**
     * @test
     */
    public function renderContainsHtmlspecialcharedEventTitle()
    {
        self::assertStringContainsString(
            'Test event &amp; more',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWithNegativeSeminarUidReturnsHeader404()
    {
        $subject = new \Tx_Seminars_FrontEnd_RegistrationsList(
            ['templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html'],
            'list_registrations',
            -1,
            $this->getFrontEndController()->cObj
        );
        $subject->render();

        self::assertEquals(
            'Status: 404 Not Found',
            HeaderProxyFactory::getInstance()->getHeaderCollector()->getLastAddedHeader()
        );
    }

    /**
     * @test
     */
    public function renderWithZeroSeminarUidReturnsHeader404()
    {
        $subject = new \Tx_Seminars_FrontEnd_RegistrationsList(
            ['templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html'],
            'list_registrations',
            0,
            $this->getFrontEndController()->cObj
        );
        $subject->render();

        self::assertEquals(
            'Status: 404 Not Found',
            HeaderProxyFactory::getInstance()->getHeaderCollector()->getLastAddedHeader()
        );
    }

    /**
     * @test
     */
    public function renderWithoutLoggedInFrontEndUserReturnsHeader403()
    {
        $this->subject->render();

        self::assertEquals(
            'Status: 403 Forbidden',
            HeaderProxyFactory::getInstance()->getHeaderCollector()->getLastAddedHeader()
        );
    }

    /**
     * @test
     */
    public function renderWithLoggedInAndNotRegisteredFrontEndUserReturnsHeader403()
    {
        $this->testingFramework->createFrontEndUser();
        $this->subject->render();

        self::assertEquals(
            'Status: 403 Forbidden',
            HeaderProxyFactory::getInstance()->getHeaderCollector()->getLastAddedHeader()
        );
    }

    /**
     * @test
     */
    public function renderWithLoggedInAndRegisteredFrontEndUserDoesNotReturnHeader403()
    {
        $this->createLogInAndRegisterFrontEndUser();
        $this->subject->render();

        self::assertStringNotContainsString(
            '403',
            HeaderProxyFactory::getInstance()->getHeaderCollector()->getLastAddedHeader()
        );
    }

    /**
     * @test
     */
    public function renderWithLoggedInAndRegisteredFrontEndUserCanContainHeaderForTheFrontEndUserUid()
    {
        $this->subject->setConfigurationValue(
            'showFeUserFieldsInRegistrationsList',
            'uid'
        );
        $this->createLogInAndRegisterFrontEndUser();

        self::assertStringContainsString(
            '<th scope="col">Number</th>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWithLoggedInAndRegisteredFrontEndUserCanContainTheFrontEndUserUid()
    {
        $this->subject->setConfigurationValue(
            'showFeUserFieldsInRegistrationsList',
            'uid'
        );
        $this->createLogInAndRegisterFrontEndUser();

        self::assertStringContainsString(
            '<td>' . $this->feUserUid . '</td>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWithLoggedInAndRegisteredFrontEndUserCanContainHeaderForTheFrontEndUserName()
    {
        $this->subject->setConfigurationValue(
            'showFeUserFieldsInRegistrationsList',
            'name'
        );
        $this->createLogInAndRegisterFrontEndUser();

        self::assertStringContainsString(
            '<th scope="col">Name:</th>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWithLoggedInAndRegisteredFrontEndUserCanContainTheFrontEndUserName()
    {
        $this->subject->setConfigurationValue(
            'showFeUserFieldsInRegistrationsList',
            'name'
        );
        $this->createLogInAndRegisterFrontEndUser();

        self::assertStringContainsString(
            '<td>Tom &amp; Jerry</td>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWithLoggedInAndRegisteredFrontEndUserCanContainHeaderForTheFrontEndUserUidAndName()
    {
        $this->subject->setConfigurationValue(
            'showFeUserFieldsInRegistrationsList',
            'uid,name'
        );
        $this->createLogInAndRegisterFrontEndUser();
        $result = $this->subject->render();

        self::assertStringContainsString(
            '<th scope="col">Number</th>',
            $result
        );
        self::assertStringContainsString(
            '<th scope="col">Name:</th>',
            $result
        );
    }

    /**
     * @test
     */
    public function renderWithLoggedInAndRegisteredFrontEndUserCanContainTheFrontEndUserUidAndName()
    {
        $this->subject->setConfigurationValue(
            'showFeUserFieldsInRegistrationsList',
            'uid,name'
        );
        $this->createLogInAndRegisterFrontEndUser();
        $this->testingFramework->changeRecord(
            'fe_users',
            $this->feUserUid,
            ['name' => 'Tom & Jerry']
        );
        $result = $this->subject->render();

        self::assertStringContainsString(
            '<td>' . $this->feUserUid . '</td>',
            $result
        );
        self::assertStringContainsString(
            '<td>Tom &amp; Jerry</td>',
            $result
        );
    }

    /**
     * @test
     */
    public function renderWithLoggedInAndRegisteredFrontEndUserCanContainHeaderForTheRegistrationUid()
    {
        $this->subject->setConfigurationValue(
            'showRegistrationFieldsInRegistrationList',
            'uid'
        );
        $this->createLogInAndRegisterFrontEndUser();

        self::assertStringContainsString(
            '<th scope="col">Ticket ID</th>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWithLoggedInAndRegisteredFrontEndUserCanContainTheRegistrationUid()
    {
        $this->subject->setConfigurationValue(
            'showRegistrationFieldsInRegistrationList',
            'uid'
        );
        $this->createLogInAndRegisterFrontEndUser();

        self::assertStringContainsString(
            '<td>' . $this->registrationUid . '</td>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWithLoggedInAndRegisteredFrontEndUserCanContainHeaderForTheRegistrationSeats()
    {
        $this->subject->setConfigurationValue(
            'showRegistrationFieldsInRegistrationList',
            'seats'
        );
        $this->createLogInAndRegisterFrontEndUser();

        self::assertStringContainsString(
            '<th scope="col">Seats</th>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWithLoggedInAndRegisteredFrontEndUserCanContainTheRegistrationSeats()
    {
        $this->subject->setConfigurationValue(
            'showRegistrationFieldsInRegistrationList',
            'seats'
        );
        $this->createLogInAndRegisterFrontEndUser();
        $this->testingFramework->changeRecord(
            'tx_seminars_attendances',
            $this->registrationUid,
            ['seats' => 42]
        );

        self::assertStringContainsString(
            '<td>42</td>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderCanContainTheRegistrationInterests()
    {
        $this->subject->setConfigurationValue(
            'showRegistrationFieldsInRegistrationList',
            'interests'
        );
        $this->createLogInAndRegisterFrontEndUser();
        $this->testingFramework->changeRecord(
            'tx_seminars_attendances',
            $this->registrationUid,
            ['interests' => 'everything practical & theoretical']
        );

        self::assertStringContainsString(
            '<td>everything practical &amp; theoretical</td>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWithLoggedInAndRegisteredFrontEndUserCanContainHeaderForTheRegistrationUidAndSeats()
    {
        $this->subject->setConfigurationValue(
            'showRegistrationFieldsInRegistrationList',
            'uid,seats'
        );
        $this->createLogInAndRegisterFrontEndUser();

        self::assertStringContainsString(
            '<th scope="col">Ticket ID</th>',
            $this->subject->render()
        );
        self::assertStringContainsString(
            '<th scope="col">Seats</th>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWithLoggedInAndRegisteredFrontEndUserCanContainTheRegistrationUidAndSeats()
    {
        $this->subject->setConfigurationValue(
            'showRegistrationFieldsInRegistrationList',
            'uid,seats'
        );
        $this->createLogInAndRegisterFrontEndUser();
        $this->testingFramework->changeRecord(
            'tx_seminars_attendances',
            $this->registrationUid,
            ['seats' => 42]
        );

        self::assertStringContainsString(
            '<td>' . $this->registrationUid . '</td>',
            $this->subject->render()
        );
        self::assertStringContainsString(
            '<td>42</td>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWithEmptyShowFeUserFieldsInRegistrationsListDoesNotContainUnresolvedLabel()
    {
        $this->createLogInAndRegisterFrontEndUser();
        $this->subject->setConfigurationValue(
            'showFeUserFieldsInRegistrationsList',
            ''
        );

        self::assertStringNotContainsString(
            'label_',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWithEmptyShowRegistrationFieldsInRegistrationListDoesNotContainUnresolvedLabel()
    {
        $this->createLogInAndRegisterFrontEndUser();
        $this->subject->setConfigurationValue(
            'showRegistrationFieldsInRegistrationList',
            ''
        );

        self::assertStringNotContainsString(
            'label_',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWithDeletedUserForRegistrationHidesUsersRegistration()
    {
        $this->subject->setConfigurationValue(
            'showRegistrationFieldsInRegistrationList',
            'uid'
        );

        $this->createLogInAndRegisterFrontEndUser();

        $this->testingFramework->changeRecord(
            'fe_users',
            $this->feUserUid,
            ['deleted' => 1]
        );

        self::assertStringNotContainsString(
            (string)$this->registrationUid,
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderSeparatesMultipleRegistrationsWithTableRows()
    {
        $this->subject->setConfigurationValue(
            'showRegistrationFieldsInRegistrationList',
            'uid'
        );
        $this->createLogInAndRegisterFrontEndUser();

        $feUserUid = $this->testingFramework->createFrontEndUser();
        $secondRegistration = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->seminarUid,
                'user' => $feUserUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'] + 500,
            ]
        );

        self::assertRegExp(
            '/' . $this->registrationUid . '<\\/td>.*<\\/tr>' .
            '.*<tr>.*<td>' . $secondRegistration . '/s',
            $this->subject->render()
        );
    }

    ///////////////////////////////////////////////////////
    // Tests concerning registrations on the waiting list
    ///////////////////////////////////////////////////////

    /**
     * @test
     */
    public function renderForNoWaitingListRegistrationsNotContainsWaitingListLabel()
    {
        self::assertStringNotContainsString(
            $this->getLanguageService()->getLL('label_waiting_list'),
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForWaitingListRegistrationsContainsWaitingListLabel()
    {
        $this->subject->setConfigurationValue(
            'showRegistrationFieldsInRegistrationList',
            'uid'
        );
        $this->createLogInAndRegisterFrontEndUser();

        $feUserUid = $this->testingFramework->createFrontEndUser();
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->seminarUid,
                'user' => $feUserUid,
                'registration_queue' => 1,
            ]
        );

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('label_waiting_list'),
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderCanContainWaitingListRegistrations()
    {
        $this->subject->setConfigurationValue(
            'showRegistrationFieldsInRegistrationList',
            'uid'
        );
        $this->createLogInAndRegisterFrontEndUser();

        $feUserUid = $this->testingFramework->createFrontEndUser();
        $secondRegistration = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->seminarUid,
                'user' => $feUserUid,
                'registration_queue' => 1,
            ]
        );

        self::assertRegExp(
            '/<td>' . $secondRegistration . '/s',
            $this->subject->render()
        );
    }
}
