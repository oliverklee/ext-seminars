<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\FrontEnd;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\OldModel\LegacyEvent;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @covers \Tx_Seminars_FrontEnd_RequirementsList
 */
final class RequirementsListTest extends TestCase
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

    protected function setUp(): void
    {
        $this->testingFramework = new TestingFramework('tx_seminars');
        $pageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($pageUid);

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

    protected function tearDown(): void
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
    public function renderWithoutSetSeminarThrowsException(): void
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
    public function renderShowsHtmlspecialcharedTitleOfOneRequirement(): void
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
        $this->subject->setEvent(new LegacyEvent($this->seminarUid));

        self::assertStringContainsString(
            'required &amp; foo',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderLinksOneRequirementToTheSingleView(): void
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
        $this->subject->setEvent(new LegacyEvent($this->seminarUid));

        self::assertRegExp(
            '/<a href=.*' . $requiredEvent . '.*>required_foo<\\/a>/',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderShowsTitleOfTwoRequirements(): void
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
        $this->subject->setEvent(new LegacyEvent($this->seminarUid));

        self::assertRegExp(
            '/required_foo.*required_bar/s',
            $this->subject->render()
        );
    }

    // Tests for limiting the results

    /**
     * @test
     */
    public function limitToMissingRegistrationsWithNoLoggedInFeUserThrowsException(): void
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
    public function limitToMissingRegistrationsLimitsOutputToMissingRegistrationsOnly(): void
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
        $this->subject->setEvent(new LegacyEvent($this->seminarUid));
        $this->subject->limitToMissingRegistrations();

        self::assertStringNotContainsString(
            'required_bar',
            $this->subject->render()
        );
    }
}
