<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BackEnd;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\BackEnd\FlexForms;
use PHPUnit\Framework\TestCase;

final class FlexFormsTest extends TestCase
{
    /**
     * @var FlexForms
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    protected function setUp(): void
    {
        $this->testingFramework = new TestingFramework('tx_seminars');
        $this->subject = new FlexForms();
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();
    }

    /**
     * @test
     */
    public function classCanBeInstantiated(): void
    {
        self::assertInstanceOf(FlexForms::class, $this->subject);
    }
}
