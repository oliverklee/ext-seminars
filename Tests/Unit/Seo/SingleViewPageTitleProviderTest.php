<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Seo;

use OliverKlee\Seminars\Seo\SingleViewPageTitleProvider;
use TYPO3\CMS\Core\PageTitle\AbstractPageTitleProvider;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Seo\SingleViewPageTitleProvider
 */
final class SingleViewPageTitleProviderTest extends UnitTestCase
{
    private SingleViewPageTitleProvider $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new SingleViewPageTitleProvider();
    }

    /**
     * @test
     */
    public function isAbstractPageTitleProvider(): void
    {
        self::assertInstanceOf(AbstractPageTitleProvider::class, $this->subject);
    }

    /**
     * @test
     */
    public function setTitleSetsTitle(): void
    {
        $title = 'some nice event';

        $this->subject->setTitle($title);

        self::assertSame($title, $this->subject->getTitle());
    }
}
