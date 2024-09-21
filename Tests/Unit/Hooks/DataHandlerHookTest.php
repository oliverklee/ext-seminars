<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Hooks;

use OliverKlee\Seminars\Hooks\DataHandlerHook;
use OliverKlee\Seminars\Seo\SlugGenerator;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Hooks\DataHandlerHook
 */
final class DataHandlerHookTest extends UnitTestCase
{
    /**
     * @var DataHandlerHook
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new DataHandlerHook($this->createStub(SlugGenerator::class));
    }

    /**
     * @test
     */
    public function isSingleton(): void
    {
        self::assertInstanceOf(SingletonInterface::class, $this->subject);
    }
}
