<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Mapper;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

/**
 * Test case.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class BackEndUserGroupMapperTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var \Tx_Seminars_Mapper_BackEndUserGroup
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();

        $this->subject = new \Tx_Seminars_Mapper_BackEndUserGroup();
    }

    /**
     * @test
     */
    public function findReturnsBackEndUserGroup()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/BackEndUsers.xml');

        $userGroup = $this->subject->find(1);

        self::assertInstanceOf(\Tx_Seminars_Model_BackEndUserGroup::class, $userGroup);
    }

    /**
     * @test
     */
    public function loadForExistingUserGroupLoadsUserGroupData()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/BackEndUsers.xml');

        $userGroup = $this->subject->find(1);

        $this->subject->load($userGroup);

        self::assertSame('Content people', $userGroup->getTitle());
    }
}
