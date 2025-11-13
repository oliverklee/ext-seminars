<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyFunctional\Model;

use OliverKlee\Seminars\Model\Place;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Model\Place
 */
final class PlaceTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'oliverklee/feuserextrafields',
        'oliverklee/oelib',
        'oliverklee/seminars',
    ];

    private Place $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Place();
    }

    /**
     * @test
     */
    public function getTitleWithNonEmptyTitleReturnsTitle(): void
    {
        $this->subject->setData(['title' => 'Nice place']);

        self::assertEquals(
            'Nice place',
            $this->subject->getTitle(),
        );
    }

    /**
     * @test
     */
    public function getFullAddressWithoutFullAddressReturnsAnEmptyString(): void
    {
        $this->subject->setData([]);

        self::assertSame('', $this->subject->getFullAddress());
    }

    /**
     * @test
     */
    public function getFullAddressWithNonEmptyFullAddressReturnsAddress(): void
    {
        $address = "Backstreet 42\n13373 Hicksville";
        $this->subject->setData(['address' => $address]);

        self::assertSame($address, $this->subject->getFullAddress());
    }

    /**
     * @test
     */
    public function getCityWithNonEmptyCityReturnsCity(): void
    {
        $this->subject->setData(['city' => 'Hicksville']);

        self::assertEquals(
            'Hicksville',
            $this->subject->getCity(),
        );
    }
}
