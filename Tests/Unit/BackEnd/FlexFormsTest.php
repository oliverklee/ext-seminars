<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\BackEnd;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\BackEnd\FlexForms;

/**
 * @covers \OliverKlee\Seminars\BackEnd\FlexForms
 */
final class FlexFormsTest extends UnitTestCase
{
    /**
     * @test
     */
    public function classCanBeInstantiated(): void
    {
        self::assertInstanceOf(FlexForms::class, new FlexForms());
    }
}
