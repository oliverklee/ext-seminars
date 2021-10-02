<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Model\Speaker;

final class SpeakerTest extends UnitTestCase
{
    /**
     * @var Speaker
     */
    private $subject = null;

    protected function setUp(): void
    {
        $this->subject = new Speaker();
    }

    /**
     * @test
     */
    public function hasImageWithoutImageReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse($this->subject->hasImage());
    }

    /**
     * @test
     */
    public function hasImageWithImageReturnsTrue(): void
    {
        $this->subject->setData(['image' => 1]);

        self::assertTrue($this->subject->hasImage());
    }
}
