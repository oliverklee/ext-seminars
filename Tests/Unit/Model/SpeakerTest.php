<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;

final class SpeakerTest extends UnitTestCase
{
    /**
     * @var \Tx_Seminars_Model_Speaker
     */
    private $subject = null;

    protected function setUp(): void
    {
        $this->subject = new \Tx_Seminars_Model_Speaker();
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
