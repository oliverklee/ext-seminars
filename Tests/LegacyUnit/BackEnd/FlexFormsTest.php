<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BackEnd;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\BackEnd\FlexForms;

class FlexFormsTest extends TestCase
{
    /**
     * @var FlexForms
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    protected function setUp()
    {
        $this->testingFramework = new TestingFramework('tx_seminars');
        $this->subject = new FlexForms();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    /**
     * @test
     */
    public function classCanBeInstantiated()
    {
        self::assertInstanceOf(FlexForms::class, $this->subject);
    }
}
