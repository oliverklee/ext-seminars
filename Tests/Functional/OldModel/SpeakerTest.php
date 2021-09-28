<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\OldModel;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Tests\Functional\Traits\FalHelper;
use TYPO3\CMS\Core\Resource\FileReference;

/**
 * @covers \Tx_Seminars_Model_Speaker
 */
final class SpeakerTest extends FunctionalTestCase
{
    use FalHelper;

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @test
     */
    public function fromUidMapsDataFromDatabase(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Speakers.xml');

        $subject = \Tx_Seminars_OldModel_Speaker::fromUid(1);

        self::assertSame('Max Delagio', $subject->getTitle());
        self::assertSame('Aperture Laboratories', $subject->getOrganization());
        self::assertSame('https://www.example.com/', $subject->getHomepage());
        self::assertSame('Lots of experience.', $subject->getDescriptionRaw());
        self::assertSame('speaker with details, but without associations', $subject->getNotes());
        self::assertSame('down the road', $subject->getAddress());
        self::assertSame('123', $subject->getPhoneWork());
        self::assertSame('456', $subject->getPhoneHome());
        self::assertSame('789', $subject->getPhoneMobile());
        self::assertSame('000', $subject->getFax());
        self::assertSame('max@example.com', $subject->getEmail());
        self::assertSame(4, $subject->getCancelationPeriodInDays());
    }

    /**
     * @test
     */
    public function canHaveOneSkill(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Speakers.xml');

        $subject = \Tx_Seminars_OldModel_Speaker::fromUid(4);

        self::assertTrue($subject->hasSkills());
    }

    /**
     * @test
     */
    public function getSkillsShortWithSingleSkillReturnsSingleSkill(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Speakers.xml');

        $subject = \Tx_Seminars_OldModel_Speaker::fromUid(4);

        self::assertTrue($subject->hasSkills());
        self::assertSame('Speaking', $subject->getSkillsShort());
    }

    /**
     * @test
     */
    public function getSkillsShortWithMultipleSkillsReturnsMultipleSkills(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Speakers.xml');

        $subject = \Tx_Seminars_OldModel_Speaker::fromUid(5);

        self::assertSame('Speaking, TYPO3 extension development', $subject->getSkillsShort());
    }

    /**
     * @test
     */
    public function getImageWithoutImageReturnsNull(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Speakers.xml');

        $subject = new \Tx_Seminars_OldModel_Speaker(1);

        self::assertNull($subject->getImage());
    }

    /**
     * @test
     */
    public function getImageWithPositiveImageCountWithoutFileReferenceReturnsNull(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Speakers.xml');

        $subject = new \Tx_Seminars_OldModel_Speaker(2);

        self::assertNull($subject->getImage());
    }

    /**
     * @test
     */
    public function getImageWithFileReferenceReturnsFileReference(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Speakers.xml');
        $this->provideAdminBackEndUserForFal();

        $subject = new \Tx_Seminars_OldModel_Speaker(3);

        self::assertInstanceOf(FileReference::class, $subject->getImage());
    }
}
