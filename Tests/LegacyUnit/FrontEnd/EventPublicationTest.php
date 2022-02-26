<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\FrontEnd;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\FrontEnd\EventPublication;
use OliverKlee\Seminars\Tests\Functional\Traits\LanguageHelper;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @covers \OliverKlee\Seminars\FrontEnd\EventPublication
 */
final class EventPublicationTest extends TestCase
{
    use LanguageHelper;

    /**
     * @var EventPublication
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    protected function setUp(): void
    {
        $this->testingFramework = new TestingFramework('tx_seminars');
        $rootPageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($rootPageUid);
        $this->subject = new EventPublication();
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();
    }

    ///////////////////////////////////
    // Tests concerning the rendering
    ///////////////////////////////////

    /**
     * @test
     */
    public function renderForNoPublicationHashSetInPiVarsReturnsPublishFailedMessage(): void
    {
        self::assertEquals(
            $this->getLanguageService()->getLL('message_publishingFailed'),
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEmptyPublicationHashSetInPiVarsReturnsPublishFailedMessage(): void
    {
        $this->subject->piVars['hash'] = '';

        self::assertEquals(
            $this->getLanguageService()->getLL('message_publishingFailed'),
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForInvalidPublicationHashSetInPiVarsReturnsPublishFailedMessage(): void
    {
        $this->subject->piVars['hash'] = 'foo';

        self::assertEquals(
            $this->getLanguageService()->getLL('message_publishingFailed'),
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForValidPublicationHashAndVisibleEventReturnsPublishFailedMessage(): void
    {
        $this->subject->init([]);
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['hidden' => 0, 'publication_hash' => '123456ABC']
        );

        $this->subject->piVars['hash'] = '123456ABC';

        self::assertEquals(
            $this->getLanguageService()->getLL('message_publishingFailed'),
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForValidPublicationHashAndHiddenEventReturnsPublishSuccessfulMessage(): void
    {
        $this->subject->init([]);
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['hidden' => 1, 'publication_hash' => '123456ABC']
        );

        $this->subject->piVars['hash'] = '123456ABC';

        self::assertEquals(
            $this->getLanguageService()->getLL('message_publishingSuccessful'),
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForValidPublicationHashUnhidesEventWithPublicationHash(): void
    {
        $this->subject->init([]);
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['hidden' => 1, 'publication_hash' => '123456ABC']
        );
        $this->subject->piVars['hash'] = '123456ABC';

        $this->subject->render();

        $connection = $this->getConnectionForTable('tx_seminars_seminars');

        self::assertGreaterThan(
            0,
            $connection->count('*', 'tx_seminars_seminars', ['uid' => $eventUid])
        );
    }

    /**
     * @test
     */
    public function renderForValidPublicationHashRemovesPublicationHashFromEvent(): void
    {
        $this->subject->init([]);
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['hidden' => 1, 'publication_hash' => '123456ABC']
        );
        $this->subject->piVars['hash'] = '123456ABC';

        $this->subject->render();
        $connection = $this->getConnectionForTable('tx_seminars_seminars');

        self::assertGreaterThan(
            0,
            $connection->count('*', 'tx_seminars_seminars', ['uid' => $eventUid, 'publication_hash' => ''])
        );
    }

    private function getConnectionForTable(string $table): Connection
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        return $connectionPool->getConnectionForTable($table);
    }
}
