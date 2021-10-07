<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\OldModel;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Seminars\Mapper\FrontEndUserMapper;
use OliverKlee\Seminars\Model\FrontEndUser;
use OliverKlee\Seminars\OldModel\AbstractModel;
use OliverKlee\Seminars\OldModel\LegacySpeaker;

/**
 * @covers \OliverKlee\Seminars\OldModel\LegacySpeaker
 */
final class LegacySpeakerTest extends UnitTestCase
{
    /**
     * @var \OliverKlee\Seminars\OldModel\LegacySpeaker
     */
    private $subject = null;

    protected function setUp(): void
    {
        $this->subject = new LegacySpeaker();
    }

    /**
     * @test
     */
    public function isAbstractModel(): void
    {
        self::assertInstanceOf(AbstractModel::class, $this->subject);
    }

    /**
     * @test
     */
    public function fromDataCreatesInstanceOfSubclass(): void
    {
        $result = LegacySpeaker::fromData([]);

        self::assertInstanceOf(LegacySpeaker::class, $result);
    }

    /**
     * @test
     */
    public function hasOrganizationWithNoOrganizationReturnsFalse(): void
    {
        $subject = LegacySpeaker::fromData(['organization' => '']);

        self::assertFalse($subject->hasOrganization());
    }

    /**
     * @test
     */
    public function hasOrganizationWithOrganizationReturnsTrue(): void
    {
        $organization = 'Foo inc.';
        $subject = LegacySpeaker::fromData(['organization' => $organization]);

        self::assertTrue($subject->hasOrganization());
    }

    /**
     * @test
     */
    public function hasHomepageWithNoHomepageReturnsFalse(): void
    {
        $subject = LegacySpeaker::fromData(['homepage' => '']);

        self::assertFalse($subject->hasHomepage());
    }

    /**
     * @test
     */
    public function hasHomepageWithHomepageReturnsTrue(): void
    {
        $homepage = 'Foo inc.';
        $subject = LegacySpeaker::fromData(['homepage' => $homepage]);

        self::assertTrue($subject->hasHomepage());
    }

    /**
     * @test
     */
    public function hasDescriptionWithNoDescriptionReturnsFalse(): void
    {
        $subject = LegacySpeaker::fromData(['description' => '']);

        self::assertFalse($subject->hasDescription());
    }

    /**
     * @test
     */
    public function hasDescriptionWithDescriptionReturnsTrue(): void
    {
        $description = 'Foo inc.';
        $subject = LegacySpeaker::fromData(['description' => $description]);

        self::assertTrue($subject->hasDescription());
    }

    /**
     * @test
     */
    public function hasSkillsInitiallyIsFalse(): void
    {
        $subject = new LegacySpeaker();

        self::assertFalse($subject->hasSkills());
    }

    /**
     * @test
     */
    public function getSkillsShortWithNoSkillReturnsEmptyString(): void
    {
        $subject = new LegacySpeaker();

        self::assertSame('', $subject->getSkillsShort());
    }

    /**
     * @test
     */
    public function getNumberOfSkillsReturnsNumberOfSkills(): void
    {
        $subject = LegacySpeaker::fromData(['skills' => 2]);

        self::assertSame(2, $subject->getNumberOfSkills());
    }

    /**
     * @test
     */
    public function getGenderForNoGenderSetReturnsUnknownGenderValue(): void
    {
        $subject = new LegacySpeaker();

        self::assertSame(LegacySpeaker::GENDER_UNKNOWN, $subject->getGender());
    }

    /**
     * @test
     */
    public function getGenderForKnownGenderReturnsGender(): void
    {
        $subject = new LegacySpeaker();
        $subject->setGender(LegacySpeaker::GENDER_MALE);

        self::assertSame(LegacySpeaker::GENDER_MALE, $subject->getGender());
    }

    /**
     * @test
     */
    public function hasCancelationPeriodWithoutCancelationPeriodReturnsFalse(): void
    {
        $subject = new LegacySpeaker();

        self::assertFalse($subject->hasCancelationPeriod());
    }

    /**
     * @test
     */
    public function hasCancelationPeriodWithCancelationPeriodReturnsTrue(): void
    {
        $subject = new LegacySpeaker();
        $subject->setCancelationPeriod(42);

        self::assertTrue($subject->hasCancelationPeriod());
    }

    /**
     * @test
     */
    public function getOwnerWithoutOwnerReturnsNull(): void
    {
        $subject = new LegacySpeaker();
        self::assertNull($subject->getOwner());
    }

    /**
     * @test
     */
    public function getOwnerWithOwnerReturnsOwner(): void
    {
        $subject = new LegacySpeaker();
        /** @var FrontEndUser $frontEndUser */
        $frontEndUser = MapperRegistry::get(FrontEndUserMapper::class)->getNewGhost();
        $subject->setOwner($frontEndUser);

        self::assertSame($frontEndUser, $subject->getOwner());
    }

    /**
     * @test
     */
    public function hasImageWithoutImageReturnsFalse(): void
    {
        $subject = new LegacySpeaker();

        self::assertFalse($subject->hasImage());
    }

    /**
     * @test
     */
    public function hasImageWithImageReturnsTrue(): void
    {
        $subject = LegacySpeaker::fromData(['image' => 1]);

        self::assertTrue($subject->hasImage());
    }
}
