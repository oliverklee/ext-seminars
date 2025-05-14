<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Controller;

use OliverKlee\Seminars\Controller\FrontEndEditorController;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Repository\CategoryRepository;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use OliverKlee\Seminars\Domain\Repository\EventTypeRepository;
use OliverKlee\Seminars\Domain\Repository\FrontendUserRepository;
use OliverKlee\Seminars\Domain\Repository\OrganizerRepository;
use OliverKlee\Seminars\Domain\Repository\SpeakerRepository;
use OliverKlee\Seminars\Domain\Repository\VenueRepository;
use OliverKlee\Seminars\Seo\SlugGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Controller\FrontEndEditorController
 */
final class FrontEndEditorControllerTest extends UnitTestCase
{
    use RedirectMockTrait;

    protected bool $resetSingletonInstances = true;

    /**
     * @var FrontEndEditorController&MockObject&AccessibleObjectInterface
     */
    private $subject;

    /**
     * @var TemplateView&Stub
     */
    private TemplateView $viewStub;

    /**
     * @var EventRepository&Stub
     */
    private EventRepository $eventRepositoryStub;

    /**
     * @var EventTypeRepository&Stub
     */
    private EventTypeRepository $eventTypeRepositoryStub;

    /**
     * @var OrganizerRepository&Stub
     */
    private OrganizerRepository $organizerRepositoryStub;

    /**
     * @var SpeakerRepository&Stub
     */
    private SpeakerRepository $speakerRepositoryStub;

    /**
     * @var VenueRepository&Stub
     */
    private VenueRepository $venueRepositoryStub;

    /**
     * @var CategoryRepository&Stub
     */
    private CategoryRepository $categoryRepositoryStub;

    /**
     * @var FrontendUserRepository&Stub
     */
    private FrontendUserRepository $userRepositoryStub;

    /**
     * @var SlugGenerator&Stub
     */
    private SlugGenerator $slugGeneratorStub;

    private Context $context;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventRepositoryStub = $this->createStub(EventRepository::class);
        $this->eventTypeRepositoryStub = $this->createStub(EventTypeRepository::class);
        $this->organizerRepositoryStub = $this->createStub(OrganizerRepository::class);
        $this->speakerRepositoryStub = $this->createStub(SpeakerRepository::class);
        $this->venueRepositoryStub = $this->createStub(VenueRepository::class);
        $this->categoryRepositoryStub = $this->createStub(CategoryRepository::class);
        $this->userRepositoryStub = $this->createStub(FrontendUserRepository::class);
        $this->slugGeneratorStub = $this->createStub(SlugGenerator::class);

        $methodsToMock = ['htmlResponse', 'redirect', 'redirectToUri'];
        /** @var FrontEndEditorController&AccessibleObjectInterface&MockObject $subject */
        $subject = $this->getAccessibleMock(
            FrontEndEditorController::class,
            $methodsToMock,
            [
                $this->eventRepositoryStub,
                $this->eventTypeRepositoryStub,
                $this->organizerRepositoryStub,
                $this->speakerRepositoryStub,
                $this->venueRepositoryStub,
                $this->categoryRepositoryStub,
                $this->userRepositoryStub,
                $this->slugGeneratorStub,
            ]
        );
        $this->subject = $subject;

        $responseStub = $this->createStub(HtmlResponse::class);
        $this->subject->method('htmlResponse')->willReturn($responseStub);

        $this->viewStub = $this->createStub(TemplateView::class);
        $this->subject->_set('view', $this->viewStub);

        $this->context = GeneralUtility::makeInstance(Context::class);
    }

    /**
     * @test
     */
    public function isActionController(): void
    {
        self::assertInstanceOf(ActionController::class, $this->subject);
    }

    /**
     * @test
     */
    public function updateSingleEventActionRedirectsToIndexAction(): void
    {
        $event = new SingleEvent();
        $event->_setProperty('uid', 1);

        $this->mockRedirect('index');

        $this->subject->updateSingleEventAction($event);
    }

    /**
     * @test
     */
    public function updateSingleEventActionWithEventFromOtherUserThrowsException(): void
    {
        $userAuthentication = new FrontendUserAuthentication();
        $userAuthentication->user = ['uid' => 1];
        $this->context->setAspect('frontend.user', new UserAspect($userAuthentication));

        $event = new SingleEvent();
        $event->setOwnerUid(2);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You do not have permission to edit this event.');
        $this->expectExceptionCode(1666954310);

        $this->subject->updateSingleEventAction($event);
    }

    /**
     * @test
     */
    public function updateSingleEventActionWithEventWithoutOwnerThrowsException(): void
    {
        $userAuthentication = new FrontendUserAuthentication();
        $userAuthentication->user = ['uid' => 1];
        $this->context->setAspect('frontend.user', new UserAspect($userAuthentication));

        $event = new SingleEvent();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You do not have permission to edit this event.');
        $this->expectExceptionCode(1666954310);

        $this->subject->updateSingleEventAction($event);
    }

    /**
     * @test
     */
    public function createSingleEventActionWithPageUidInConfigurationSetsProvidedPageUid(): void
    {
        $pageUid = 42;
        $event = new SingleEvent();
        $event->_setProperty('uid', 1);
        $this->subject->_set('settings', ['folderForCreatedEvents' => (string)$pageUid]);

        $this->stubRedirect();

        $this->subject->createSingleEventAction($event);

        self::assertSame($pageUid, $event->getPid());
    }

    /**
     * @test
     */
    public function createSingleEventActionWithoutPageUidInConfigurationSetsZeroPageUid(): void
    {
        $event = new SingleEvent();
        $event->_setProperty('uid', 1);

        $this->stubRedirect();

        $this->subject->createSingleEventAction($event);

        self::assertSame(0, $event->getPid());
    }

    /**
     * @test
     */
    public function createSingleEventActionRedirectsToIndexAction(): void
    {
        $event = new SingleEvent();
        $event->_setProperty('uid', 1);

        $this->mockRedirect('index');

        $this->subject->createSingleEventAction($event);
    }
}
