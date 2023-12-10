<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Seminars\Model\Speaker;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Model\Speaker
 */
final class SpeakerTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    protected $initializeDatabase = false;

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
