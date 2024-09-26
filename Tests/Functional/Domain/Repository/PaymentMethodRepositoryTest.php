<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Domain\Repository;

use OliverKlee\Seminars\Domain\Model\PaymentMethod;
use OliverKlee\Seminars\Domain\Repository\PaymentMethodRepository;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\PaymentMethod
 * @covers \OliverKlee\Seminars\Domain\Repository\PaymentMethodRepository
 */
final class PaymentMethodRepositoryTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    private PaymentMethodRepository $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->get(PaymentMethodRepository::class);
    }

    /**
     * @test
     */
    public function mapsAllModelFields(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/PaymentMethodRepository/PaymentMethodWithAllFields.xml');

        $result = $this->subject->findByUid(1);

        self::assertInstanceOf(PaymentMethod::class, $result);
        self::assertSame('invoice', $result->getTitle());
    }
}
