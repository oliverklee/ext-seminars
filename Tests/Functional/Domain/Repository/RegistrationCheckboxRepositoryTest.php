<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Domain\Repository;

use OliverKlee\Seminars\Domain\Model\RegistrationCheckbox;
use OliverKlee\Seminars\Domain\Repository\RegistrationCheckboxRepository;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\RegistrationCheckbox
 * @covers \OliverKlee\Seminars\Domain\Repository\RegistrationCheckboxRepository
 */
final class RegistrationCheckboxRepositoryTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var RegistrationCheckboxRepository
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->get(RegistrationCheckboxRepository::class);
    }

    /**
     * @test
     */
    public function mapsAllModelFields(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationCheckboxRepository/RegistrationCheckboxWithAllFields.xml');

        $result = $this->subject->findByUid(1);

        self::assertInstanceOf(RegistrationCheckbox::class, $result);
        self::assertSame('will contribute a session', $result->getTitle());
    }
}
