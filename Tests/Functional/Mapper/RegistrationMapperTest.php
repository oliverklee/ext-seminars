<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Mapper;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

final class RegistrationMapperTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var \Tx_Seminars_Mapper_Registration
     */
    private $subject = null;

    /**
     * @var \Tx_Seminars_Mapper_FrontEndUser
     */
    private $userMapper = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userMapper = new \Tx_Seminars_Mapper_FrontEndUser();
        $this->subject = new \Tx_Seminars_Mapper_Registration();
    }

    /**
     * @test
     */
    public function countByFrontEndUserIgnoresRegistrationFromOtherUsers(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Registrations.xml');

        $user = $this->userMapper->find(1);

        self::assertSame(0, $this->subject->countByFrontEndUser($user));
    }

    /**
     * @test
     */
    public function countByFrontEndUserCountsRegistrationFromGivenUser(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Registrations.xml');

        $user = $this->userMapper->find(2);

        self::assertSame(1, $this->subject->countByFrontEndUser($user));
    }
}
