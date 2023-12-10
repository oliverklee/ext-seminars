<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Model\FrontEndUser;
use OliverKlee\Seminars\Model\Registration;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Model\FrontEndUser
 */
final class FrontEndUserTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    protected $initializeDatabase = false;

    /**
     * @var FrontEndUser
     */
    private $subject;

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new FrontEndUser();
        $this->testingFramework = new TestingFramework('tx_seminars');
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUpWithoutDatabase();

        parent::tearDown();
    }

    // Tests concerning getRegistration and setRegistration

    /**
     * @test
     */
    public function getRegistrationReturnsRegistration(): void
    {
        $registration = new Registration();
        $this->subject->setData(
            ['tx_seminars_registration' => $registration]
        );

        self::assertSame(
            $registration,
            $this->subject->getRegistration()
        );
    }

    /**
     * @test
     */
    public function setRegistrationSetsRegistration(): void
    {
        $registration = new Registration();
        $this->subject->setRegistration($registration);

        self::assertSame(
            $registration,
            $this->subject->getRegistration()
        );
    }

    /**
     * @test
     */
    public function setRegistrationWithNullIsAllowed(): void
    {
        $this->subject->setRegistration();

        self::assertNull(
            $this->subject->getRegistration()
        );
    }
}
