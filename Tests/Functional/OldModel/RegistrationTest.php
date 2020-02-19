<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\OldModel;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class RegistrationTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @test
     */
    public function fromUidMapsDataFromDatabase()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Registrations.xml');

        $subject = \Tx_Seminars_OldModel_Registration::fromUid(1);

        self::assertSame(4, $subject->getSeats());
        self::assertSame(1, $subject->getUser());
        self::assertSame(1, $subject->getSeminar());
        self::assertTrue($subject->isPaid());
        self::assertSame('coding', $subject->getInterests());
        self::assertSame('good coffee', $subject->getExpectations());
        self::assertSame('latte art', $subject->getKnowledge());
        self::assertSame('word of mouth', $subject->getKnownFrom());
        self::assertSame('Looking forward to it!', $subject->getNotes());
        self::assertSame('Standard: 500.23€', $subject->getPrice());
        self::assertSame('vegetarian', $subject->getFood());
        self::assertSame('at home', $subject->getAccommodation());
        self::assertSame('Max Moe', $subject->getAttendeesNames());
        self::assertSame(2, $subject->getNumberOfKids());
        self::assertTrue($subject->hasRegisteredMySelf());
    }

    /**
     * @test
     */
    public function mapsFrontEndUser()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Registrations.xml');

        $subject = \Tx_Seminars_OldModel_Registration::fromUid(1);

        $user = $subject->getFrontEndUser();

        self::assertInstanceOf(\Tx_Seminars_Model_FrontEndUser::class, $user);
        self::assertSame(1, $user->getUid());
    }

    /**
     * @test
     */
    public function mapsEvent()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Registrations.xml');

        $subject = \Tx_Seminars_OldModel_Registration::fromUid(1);

        $event = $subject->getSeminarObject();

        self::assertInstanceOf(\Tx_Seminars_OldModel_Event::class, $event);
        self::assertSame(1, $event->getUid());
    }
}
