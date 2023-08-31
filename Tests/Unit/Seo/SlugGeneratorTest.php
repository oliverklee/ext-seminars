<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Seo;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Seo\SlugGenerator;
use OliverKlee\Seminars\Tests\Unit\Seo\Fixtures\TestingSlugEventDispatcher;

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

        $slugEventDispatcher = new TestingSlugEventDispatcher();
        $this->subject = new SlugGenerator($slugEventDispatcher);
    }

    /**
     * @test
     */
    public function getPrefixAlwaysReturnsAnEmptyString(): void
    {
        self::assertSame('', $this->subject->getPrefix());
    }
}
