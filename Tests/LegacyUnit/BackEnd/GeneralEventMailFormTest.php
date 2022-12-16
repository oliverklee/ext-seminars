<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BackEnd;

use OliverKlee\Oelib\Configuration\PageFinder;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\BackEnd\GeneralEventMailForm;
use OliverKlee\Seminars\Tests\LegacyUnit\Support\Traits\BackEndTestsTrait;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Information\Typo3Version;

final class GeneralEventMailFormTest extends TestCase
{
    use BackEndTestsTrait;

    /**
     * @var GeneralEventMailForm
     */
    private $subject;

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    protected function setUp(): void
    {
        if ((new Typo3Version())->getMajorVersion() >= 11) {
            self::markTestSkipped('Skipping because this code will be removed before adding 11LTS compatibility.');
        }

        $this->unifyTestingEnvironment();

        $this->testingFramework = new TestingFramework('tx_seminars');

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
                'organizers' => 1,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
                'title' => 'Dummy Event',
                'registrations' => 1,
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $organizerUid,
            'organizers'
        );

        $this->subject = new GeneralEventMailForm($eventUid);
    }

    protected function tearDown(): void
    {
        if ($this->testingFramework instanceof TestingFramework) {
            $this->testingFramework->cleanUp();
        }
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
            '<button class="submitButton sendEmail"><p>' .
            $this->translate('generalMailForm_sendButton') .
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
    public function renderContainsTheSendEmailActionForThisForm(): void
    {
        self::assertStringContainsString(
            '<input type="hidden" name="action" value="sendEmail" />',
            $this->subject->render()
        );
    }
}
