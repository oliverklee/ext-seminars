<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Seo;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Seo\SlugGenerator;
use OliverKlee\Seminars\Tests\Unit\Seo\Fixtures\TestingSlugEventDispatcher;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

        GeneralUtility::addInstance(EventDispatcherInterface::class, new TestingSlugEventDispatcher());

        $this->subject = new SlugGenerator();
    }

    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function getPrefixAlwaysReturnsAnEmptyString(): void
    {
        self::assertSame('', $this->subject->getPrefix());
    }
}
