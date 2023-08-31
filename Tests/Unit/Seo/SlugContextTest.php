<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Seo;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Seo\SlugContext;

/**
 * @covers \OliverKlee\Seminars\Seo\SlugContext
 */
final class SlugContextTest extends UnitTestCase
{
    /**
     * @test
     */
    public function getUidReturnsUidFromConstructor(): void
    {
        $uid = 42;
        $subject = new SlugContext($uid, '', '');

        self::assertSame($uid, $subject->getEventUid());
    }

    /**
     * @test
     */
    public function getDisplayTitleReturnsDisplayTitleFromConstructor(): void
    {
        $relevantTitle = 'Some nice event';
        $subject = new SlugContext(42, $relevantTitle, '');

        self::assertSame($relevantTitle, $subject->getDisplayTitle());
    }

    /**
     * @test
     */
    public function getSlugifiedTitleReturnsSlugifiedTitleFromConstructor(): void
    {
        $slugifiedTitle = 'some-nice-event';
        $subject = new SlugContext(42, '', $slugifiedTitle);

        self::assertSame($slugifiedTitle, $subject->getSlugifiedTitle());
    }
}
