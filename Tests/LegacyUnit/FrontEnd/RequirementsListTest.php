<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\FrontEnd;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class RequirementsListTest extends TestCase
{
    /**
     * @var \Tx_Seminars_FrontEnd_RequirementsList
     */
    private $subject = null;

    /**
     * @var int the UID of a seminar to which the plugin relates
     */
    private $seminarUid = 0;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    protected function setUp()
    {
        $this->testingFramework = new TestingFramework('tx_seminars');
        $this->testingFramework->createFakeFrontEnd();

        $systemFolderPid = $this->testingFramework->createSystemFolder();

        $this->seminarUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $systemFolderPid,
                'title' => 'Test event',
            ]
        );

        $this->subject = new \Tx_Seminars_FrontEnd_RequirementsList(
            ['templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html'],
            $this->getFrontEndController()->cObj
        );
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();

        \Tx_Seminars_Service_RegistrationManager::purgeInstance();
    }

    private function getFrontEndController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    // Tests for the render function

    /**
     * @test
     */
    public function renderWithoutSetSeminarThrowsException()
    {
        $this->expectException(
            \BadMethodCallException::class
        );
        $this->expectExceptionMessage(
            'No event was set, please set an event before calling render'
        );

        $this->subject->render();
    }

    /**
     * @test
     */
    public function renderShowsHtmlspecialcharedTitleOfOneRequirement()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $requiredEvent = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'required & foo',
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $requiredEvent,
            'requirements'
        );
        $this->subject->setEvent(new \Tx_Seminars_OldModel_Event($this->seminarUid));

        self::assertStringContainsString(
            'required &amp; foo',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderLinksOneRequirementToTheSingleView()
    {
        $this->subject->setConfigurationValue(
            'detailPID',
            $this->testingFramework->createFrontEndPage()
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $requiredEvent = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'required_foo',
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $requiredEvent,
            'requirements'
        );
        $this->subject->setEvent(new \Tx_Seminars_OldModel_Event($this->seminarUid));

        self::assertRegExp(
            '/<a href=.*' . $requiredEvent . '.*>required_foo<\\/a>/',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderShowsTitleOfTwoRequirements()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $requiredEvent1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'required_foo',
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $requiredEvent1,
            'requirements'
        );
        $requiredEvent2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'required_bar',
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $requiredEvent2,
            'requirements'
        );
        $this->subject->setEvent(new \Tx_Seminars_OldModel_Event($this->seminarUid));

        self::assertRegExp(
            '/required_foo.*required_bar/s',
            $this->subject->render()
        );
    }

    // Tests for limiting the results

    /**
     * @test
     */
    public function limitToMissingRegistrationsWithNoLoggedInFeUserThrowsException()
    {
        $this->expectException(
            \BadMethodCallException::class
        );
        $this->expectExceptionMessage(
            'No FE user is currently logged in. Please call this function only when a FE user is logged in.'
        );

        $this->subject->limitToMissingRegistrations();
    }

    /**
     * @test
     */
    public function limitToMissingRegistrationsLimitsOutputToMissingRegistrationsOnly()
    {
        $userUid = $this->testingFramework->createAndLoginFrontEndUser();
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $requiredEvent1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'required_foo',
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $requiredEvent1,
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $requiredEvent1,
            'requirements'
        );
        $requiredEvent2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'required_bar',
            ]
        );
        $requiredDate2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $requiredEvent2,
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $requiredEvent2,
            'requirements'
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $requiredDate2, 'user' => $userUid]
        );
        $this->subject->setEvent(new \Tx_Seminars_OldModel_Event($this->seminarUid));
        $this->subject->limitToMissingRegistrations();

        self::assertStringNotContainsString(
            'required_bar',
            $this->subject->render()
        );
    }
}
