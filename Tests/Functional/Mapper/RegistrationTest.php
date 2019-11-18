<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Mapper;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class RegistrationTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var \Tx_Seminars_Mapper_Registration
     */
    private $subject = null;

    /**
     * @var \Tx_Seminars_Mapper_FrontEndUser
     */
    private $userMapper = null;

    protected function setUp()
    {
        parent::setUp();
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');

        $this->userMapper = new \Tx_Seminars_Mapper_FrontEndUser();

        $this->subject = new \Tx_Seminars_Mapper_Registration();
    }

    /**
     * @test
     */
    public function countByFrontEndUserForNoMatchingRegistrationsReturnsZero()
    {
        $userUid = $this->testingFramework->createFrontEndUser();
        /** @var \Tx_Seminars_Model_FrontEndUser $user */
        $user = $this->userMapper->find($userUid);

        self::assertSame(0, $this->subject->countByFrontEndUser($user));
    }

    /**
     * @test
     */
    public function countByFrontEndUserIgnoresRegistrationFromOtherUsers()
    {
        $otherUserUid = $this->testingFramework->createFrontEndUser();
        $this->testingFramework->createRecord('tx_seminars_attendances', ['user' => $otherUserUid]);

        $userUid = $this->testingFramework->createFrontEndUser();
        /** @var \Tx_Seminars_Model_FrontEndUser $user */
        $user = $this->userMapper->find($userUid);

        self::assertSame(0, $this->subject->countByFrontEndUser($user));
    }

    /**
     * @test
     */
    public function countByFrontEndUserCountsRegistrationFromGivenUser()
    {
        $userUid = $this->testingFramework->createFrontEndUser();
        $this->testingFramework->createRecord('tx_seminars_attendances', ['user' => $userUid]);
        /** @var \Tx_Seminars_Model_FrontEndUser $user */
        $user = $this->userMapper->find($userUid);

        self::assertSame(1, $this->subject->countByFrontEndUser($user));
    }
}
