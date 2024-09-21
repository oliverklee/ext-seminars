<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Seo;

use OliverKlee\Seminars\Seo\SlugGenerator;
use OliverKlee\Seminars\Tests\Unit\Seo\Fixtures\TestingSlugEventDispatcher;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Seo\SlugGenerator
 */
final class SlugGeneratorTest extends UnitTestCase
{
    /**
     * @var SlugGenerator
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new SlugGenerator(new TestingSlugEventDispatcher());
    }

    /**
     * @test
     */
    public function isSingleton(): void
    {
        self::assertInstanceOf(SingletonInterface::class, $this->subject);
    }

    /**
     * @test
     */
    public function getPrefixAlwaysReturnsAnEmptyString(): void
    {
        self::assertSame('', $this->subject->getPrefix());
    }
}
