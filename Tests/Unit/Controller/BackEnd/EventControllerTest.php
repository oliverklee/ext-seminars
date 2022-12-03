<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Controller\BackEnd;

use Nimut\TestingFramework\MockObject\AccessibleMockObjectInterface;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\BackEnd\Permissions;
use OliverKlee\Seminars\Controller\BackEnd\EventController;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Fluid\View\TemplateView;

/**
 * @covers \OliverKlee\Seminars\Controller\BackEnd\EventController
 */
final class EventControllerTest extends UnitTestCase
{
    /**
     * @var EventController&MockObject&AccessibleMockObjectInterface
     */
    private $subject;

    /**
     * @var TemplateView&MockObject
     */
    private $viewMock;

    /**
     * @var Permissions&MockObject
     */
    private $permissionsMock;

    protected function setUp(): void
    {
        /** @var EventController&AccessibleMockObjectInterface&MockObject $subject */
        $subject = $this->getAccessibleMock(
            EventController::class,
            ['redirect', 'forward', 'redirectToUri']
        );
        $this->subject = $subject;

        $this->viewMock = $this->createMock(TemplateView::class);
        $this->subject->_set('view', $this->viewMock);
        $this->permissionsMock = $this->createMock(Permissions::class);
        $this->subject->injectPermissions($this->permissionsMock);
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['_GET']['id'], $GLOBALS['_POST']['id']);
        parent::tearDown();
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
    public function pageUidIsTakenFromGetId(): void
    {
        $pageUid = 15;
        $GLOBALS['_GET']['id'] = (string)$pageUid;

        self::assertSame($pageUid, $this->subject->getPageUid());
    }

    /**
     * @test
     */
    public function pageUidIsTakenFromPostId(): void
    {
        $pageUid = 15;
        $GLOBALS['_POST']['id'] = (string)$pageUid;

        self::assertSame($pageUid, $this->subject->getPageUid());
    }

    /**
     * @test
     */
    public function pageUidForNoIdInRequestIsZero(): void
    {
        self::assertSame(0, $this->subject->getPageUid());
    }

    /**
     * @test
     */
    public function indexActionPassesPermissionsToView(): void
    {
        $this->viewMock->expects(self::exactly(2))->method('assign')
            ->withConsecutive(
                ['permissions', $this->permissionsMock],
                ['pageUid', self::anything()]
            );

        $this->subject->indexAction();
    }

    /**
     * @test
     */
    public function indexActionPassesPageUidToView(): void
    {
        $pageUid = 8;
        $GLOBALS['_GET']['id'] = (string)$pageUid;

        $this->viewMock->expects(self::exactly(2))->method('assign')
            ->withConsecutive(
                ['permissions', self::anything()],
                ['pageUid', $pageUid]
            );

        $this->subject->indexAction();
    }
}
