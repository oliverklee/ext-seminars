<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BackEnd;

use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\BackEnd\ConfirmEventMailForm;
use OliverKlee\Seminars\Tests\LegacyUnit\Support\Traits\BackEndTestsTrait;

/**
 * Test case.
 *
 * @author Mario Rimann <mario@screenteam.com>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class ConfirmEventMailFormTest extends TestCase
{
    use BackEndTestsTrait;

    /**
     * @var ConfirmEventMailForm
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    protected function setUp()
    {
        $this->unifyTestingEnvironment();

        $this->testingFramework = new TestingFramework('tx_seminars');
        MapperRegistry::getInstance()->activateTestingMode($this->testingFramework);

        $dummySysFolderUid = $this->testingFramework->createSystemFolder();
        \Tx_Oelib_PageFinder::getInstance()->setPageUid($dummySysFolderUid);

        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            [
                'title' => 'Dummy Organizer',
                'email' => 'foo@example.com',
            ]
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $dummySysFolderUid,
                'title' => 'Dummy event',
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 86400,
                'organizers' => 0,
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $organizerUid,
            'organizers'
        );

        $this->subject = new ConfirmEventMailForm($eventUid);
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
        $this->restoreOriginalEnvironment();
    }

    ///////////////////////////////////////////////
    // Tests regarding the rendering of the form.
    ///////////////////////////////////////////////

    /**
     * @test
     */
    public function renderContainsSubmitButton()
    {
        self::assertContains(
            '<button class="submitButton confirmEvent"><p>' .
            $this->getLanguageService()->getLL('confirmMailForm_sendButton') .
            '</p></button>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderContainsPrefilledBodyFieldWithLocalizedSalutation()
    {
        self::assertContains('salutation', $this->subject->render());
    }

    /**
     * @test
     */
    public function renderContainsTheConfirmEventActionForThisForm()
    {
        self::assertContains(
            '<input type="hidden" name="action" value="confirmEvent" />',
            $this->subject->render()
        );
    }
}
