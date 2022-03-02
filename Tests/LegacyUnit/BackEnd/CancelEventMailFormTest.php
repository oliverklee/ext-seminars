<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BackEnd;

use OliverKlee\Oelib\Configuration\PageFinder;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\BackEnd\CancelEventMailForm;
use OliverKlee\Seminars\Model\Event;
use OliverKlee\Seminars\Tests\LegacyUnit\Support\Traits\BackEndTestsTrait;
use PHPUnit\Framework\TestCase;

final class CancelEventMailFormTest extends TestCase
{
    use BackEndTestsTrait;

    /**
     * @var CancelEventMailForm
     */
    private $subject;

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    protected function setUp(): void
    {
        $this->unifyTestingEnvironment();

        $this->testingFramework = new TestingFramework('tx_seminars');
        MapperRegistry::getInstance()->activateTestingMode($this->testingFramework);

        $dummySysFolderUid = $this->testingFramework->createSystemFolder();
        PageFinder::getInstance()->setPageUid($dummySysFolderUid);

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
                'object_type' => Event::TYPE_DATE,
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

        $this->subject = new CancelEventMailForm($eventUid);
    }

    protected function tearDown(): void
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
    public function renderContainsSubmitButton(): void
    {
        self::assertStringContainsString(
            '<button class="submitButton cancelEvent"><p>' .
            $this->translate('cancelMailForm_sendButton') .
            '</p></button>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderContainsPrefilledBodyFieldWithLocalizedSalutation(): void
    {
        self::assertStringContainsString('salutation', $this->subject->render());
    }

    /**
     * @test
     */
    public function renderContainsTheCancelEventActionForThisForm(): void
    {
        self::assertStringContainsString(
            '<input type="hidden" name="action" value="cancelEvent" />',
            $this->subject->render()
        );
    }
}
