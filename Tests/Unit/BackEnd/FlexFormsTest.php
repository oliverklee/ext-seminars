<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\BackEnd;

use OliverKlee\Seminars\BackEnd\FlexForms;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

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
