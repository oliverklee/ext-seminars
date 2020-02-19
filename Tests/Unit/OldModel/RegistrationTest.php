<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\OldModel;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\OldModel\AbstractModel;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class RegistrationTest extends UnitTestCase
{
    /**
     * @var \Tx_Seminars_OldModel_Registration
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = \Tx_Seminars_OldModel_Registration::fromData([]);
    }

    /**
     * @test
     */
    public function isAbstractModel()
    {
        self::assertInstanceOf(AbstractModel::class, $this->subject);
    }

    /**
     * @test
     */
    public function fromDataCreatesInstanceOfSubclass()
    {
        $result = \Tx_Seminars_OldModel_Registration::fromData([]);

        self::assertInstanceOf(\Tx_Seminars_OldModel_Registration::class, $result);
    }

    /**
     * @test
     */
    public function getFrontEndUserWithoutUserUidReturnsNull()
    {
        $result = $this->subject->getFrontEndUser();

        self::assertNull($result);
    }

    /**
     * @test
     */
    public function setFrontEndUserSetsFrontEndUser()
    {
        $user = new \Tx_Seminars_Model_FrontEndUser();

        $this->subject->setFrontEndUser($user);

        self::assertSame($user, $this->subject->getFrontEndUser());
    }
}
