<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Seminars\Model\Speaker;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OliverKlee\Seminars\Model\Speaker
 */
final class SpeakerTest extends TestCase
{
    /**
     * @var Speaker
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Speaker();
    }

    /**
     * @test
     */
    public function getNameWithNonEmptyNameReturnsName(): void
    {
        $this->subject->setData(['title' => 'John Doe']);

        self::assertEquals(
            'John Doe',
            $this->subject->getName()
        );
    }

    /**
     * @test
     */
    public function getEmailAddressWithoutEmailAddressReturnsAnEmptyString(): void
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getEmailAddress()
        );
    }

    /**
     * @test
     */
    public function getEmailAddressWithNonEmptyEmailAddressReturnsEmailAddress(): void
    {
        $this->subject->setData(['email' => 'mail@example.com']);

        self::assertEquals(
            'mail@example.com',
            $this->subject->getEmailAddress()
        );
    }
}
