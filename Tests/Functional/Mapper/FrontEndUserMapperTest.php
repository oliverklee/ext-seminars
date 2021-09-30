<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Mapper;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Seminars\Model\Registration;

/**
 * @covers \Tx_Seminars_Mapper_FrontEndUser
 */
final class FrontEndUserMapperTest extends FunctionalTestCase
{
    /**
     * @var array<int, string>
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var \Tx_Seminars_Mapper_FrontEndUser
     */
    private $subject = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new \Tx_Seminars_Mapper_FrontEndUser();
    }

    /**
     * @test
     */
    public function loadForExistingRecordLoadsScalarData(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/FrontEndUsers.xml');

        $model = $this->subject->find(1);

        $this->subject->load($model);

        self::assertSame('ben', $model->getUserName());
    }

    /**
     * @test
     */
    public function loadPopulatesUserGroupsAssociation(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/FrontEndUsers.xml');

        $model = $this->subject->find(1);

        $this->subject->load($model);

        /** @var \Tx_Seminars_Model_FrontEndUserGroup $firstGroup */
        $firstGroup = $model->getUserGroups()->first();
        self::assertInstanceOf(\Tx_Seminars_Model_FrontEndUserGroup::class, $firstGroup);
        self::assertSame(1, $firstGroup->getUid());
        self::assertSame('Editors', $firstGroup->getTitle());
    }

    /**
     * @test
     */
    public function loadPopulatesRegistrationsAssociation(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/FrontEndUsers.xml');

        $model = $this->subject->find(1);

        $this->subject->load($model);

        $registration = $model->getRegistration();
        self::assertInstanceOf(Registration::class, $registration);
        self::assertSame(1, $registration->getUid());
    }

    // Tests concerning findByUserName

    /**
     * @test
     */
    public function findByUserNameForEmptyUserNameThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$value must not be empty.');

        $this->subject->findByUserName('');
    }

    /**
     * @test
     */
    public function findByUserNameWithNameOfExistingUserReturnsFrontEndUserInstance(): void
    {
        $userName = 'max.doe';
        $this->getDatabaseConnection()->insertArray('fe_users', ['username' => $userName]);

        self::assertInstanceOf(
            \Tx_Seminars_Model_FrontEndUser::class,
            $this->subject->findByUserName($userName)
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithNameOfExistingUserReturnsModelWithThatUid(): void
    {
        $userName = 'max.doe';
        $this->getDatabaseConnection()->insertArray('fe_users', ['username' => $userName]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            $uid,
            $this->subject->findByUserName($userName)->getUid()
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithUppercasedNameOfExistingLowercasedUserReturnsModelWithThatUid(): void
    {
        $userName = 'max.doe';
        $this->getDatabaseConnection()->insertArray('fe_users', ['username' => $userName]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            $uid,
            $this->subject->findByUserName(strtoupper($userName))->getUid()
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithUppercasedNameOfExistingUppercasedUserReturnsModelWithThatUid(): void
    {
        $userName = 'MAX.DOE';
        $this->getDatabaseConnection()->insertArray('fe_users', ['username' => $userName]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            $uid,
            $this->subject->findByUserName($userName)->getUid()
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithLowercasedNameOfExistingUppercasedUserReturnsModelWithThatUid(): void
    {
        $userName = 'max.doe';
        $this->getDatabaseConnection()->insertArray('fe_users', ['username' => strtoupper($userName)]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            $uid,
            $this->subject->findByUserName($userName)->getUid()
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithNameOfNonExistentUserThrowsException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('No records found in the table "fe_users" matching: {"username":"max.doe"}');

        $userName = 'max.doe';
        $this->getDatabaseConnection()->insertArray('fe_users', ['username' => $userName, 'deleted' => 1]);

        $this->subject->findByUserName($userName);
    }
}
