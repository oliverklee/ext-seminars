<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Mapper;

use OliverKlee\Seminars\Mapper\FrontEndUserMapper;
use OliverKlee\Seminars\Mapper\RegistrationMapper;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Mapper\RegistrationMapper
 */
final class RegistrationMapperTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    private RegistrationMapper $subject;

    private FrontEndUserMapper $userMapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userMapper = new FrontEndUserMapper();
        $this->subject = new RegistrationMapper();
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
