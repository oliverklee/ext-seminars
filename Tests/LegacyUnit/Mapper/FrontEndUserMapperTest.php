<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Mapper;

use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;

/**
 * Test case.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class FrontEndUserMapperTest extends TestCase
{
    /**
     * @var \Tx_Seminars_Mapper_FrontEndUser
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    protected function setUp()
    {
        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = MapperRegistry::get(\Tx_Seminars_Mapper_FrontEndUser::class);
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    // Tests for the basic functionality

    /**
     * @test
     */
    public function mapperForGhostReturnsSeminarsFrontEndUserInstance()
    {
        self::assertInstanceOf(\Tx_Seminars_Model_FrontEndUser::class, $this->subject->getNewGhost());
    }

    // Tests concerning the relations

    /**
     * @test
     */
    public function relationToRegistrationIsReadFromRegistrationMapper()
    {
        $registration = MapperRegistry::get(\Tx_Seminars_Mapper_Registration::class)->getNewGhost();

        $model = $this->subject->getLoadedTestingModel(
            ['tx_seminars_registration' => $registration->getUid()]
        );

        self::assertSame(
            $registration,
            $model->getRegistration()
        );
    }
}
