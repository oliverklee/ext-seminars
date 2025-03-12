<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Controller;

use OliverKlee\Seminars\Controller\MyRegistrationsController;
use OliverKlee\Seminars\Domain\Repository\Registration\RegistrationRepository;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Controller\MyRegistrationsController
 */
final class MyRegistrationsControllerTest extends UnitTestCase
{
    private MyRegistrationsController $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $contextStub = $this->createStub(Context::class);
        $registrationRepositoryStub = $this->createStub(RegistrationRepository::class);

        $this->subject = new MyRegistrationsController($contextStub, $registrationRepositoryStub);
    }

    /**
     * @test
     */
    public function isActionController(): void
    {
        self::assertInstanceOf(ActionController::class, $this->subject);
    }
}
