<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Bag;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Bag\OrganizerBag;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Bag\OrganizerBag
 */
final class OrganizerBagTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];
    /**
     * @var OrganizerBag
     */
    private $subject;

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->testingFramework->createRecord('tx_seminars_organizers');

        $this->subject = new OrganizerBag('is_dummy_record=1');
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();

        parent::tearDown();
    }

    ///////////////////////////////////////////
    // Tests for the basic bag functionality.
    ///////////////////////////////////////////

    /**
     * @test
     */
    public function bagCanHaveAtLeastOneElement(): void
    {
        self::assertFalse(
            $this->subject->isEmpty()
        );
    }
}
