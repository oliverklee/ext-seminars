<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\OldModel;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Seminars\Mapper\FrontEndUserMapper;
use OliverKlee\Seminars\Model\FrontEndUser;
use OliverKlee\Seminars\OldModel\AbstractModel;

/**
 * @covers \Tx_Seminars_Model_Speaker
 */
final class SpeakerTest extends UnitTestCase
{
    /**
     * @var \Tx_Seminars_OldModel_Speaker
     */
    private $subject = null;

    protected function setUp(): void
    {
        $this->subject = new \Tx_Seminars_OldModel_Speaker();
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
        $result = \Tx_Seminars_OldModel_Speaker::fromData([]);

        self::assertInstanceOf(\Tx_Seminars_OldModel_Speaker::class, $result);
    }

    /**
     * @test
     */
    public function hasOrganizationWithNoOrganizationReturnsFalse(): void
    {
        $subject = \Tx_Seminars_OldModel_Speaker::fromData(['organization' => '']);

        self::assertFalse($subject->hasOrganization());
    }

    /**
     * @test
     */
    public function hasOrganizationWithOrganizationReturnsTrue(): void
    {
        $organization = 'Foo inc.';
        $subject = \Tx_Seminars_OldModel_Speaker::fromData(['organization' => $organization]);

        self::assertTrue($subject->hasOrganization());
    }

    /**
     * @test
     */
    public function hasHomepageWithNoHomepageReturnsFalse(): void
    {
        $subject = \Tx_Seminars_OldModel_Speaker::fromData(['homepage' => '']);

        self::assertFalse($subject->hasHomepage());
    }

    /**
     * @test
     */
    public function hasHomepageWithHomepageReturnsTrue(): void
    {
        $homepage = 'Foo inc.';
        $subject = \Tx_Seminars_OldModel_Speaker::fromData(['homepage' => $homepage]);

        self::assertTrue($subject->hasHomepage());
    }

    /**
     * @test
     */
    public function hasDescriptionWithNoDescriptionReturnsFalse(): void
    {
        $subject = \Tx_Seminars_OldModel_Speaker::fromData(['description' => '']);

        self::assertFalse($subject->hasDescription());
    }

    /**
     * @test
     */
    public function hasDescriptionWithDescriptionReturnsTrue(): void
    {
        $description = 'Foo inc.';
        $subject = \Tx_Seminars_OldModel_Speaker::fromData(['description' => $description]);

        self::assertTrue($subject->hasDescription());
    }

    /**
     * @test
     */
    public function hasSkillsInitiallyIsFalse(): void
    {
        $subject = new \Tx_Seminars_OldModel_Speaker();

        self::assertFalse($subject->hasSkills());
    }

    /**
     * @test
     */
    public function getSkillsShortWithNoSkillReturnsEmptyString(): void
    {
        $subject = new \Tx_Seminars_OldModel_Speaker();

        self::assertSame('', $subject->getSkillsShort());
    }

    /**
     * @test
     */
    public function getNumberOfSkillsReturnsNumberOfSkills(): void
    {
        $subject = \Tx_Seminars_OldModel_Speaker::fromData(['skills' => 2]);

        self::assertSame(2, $subject->getNumberOfSkills());
    }

    /**
     * @test
     */
    public function getGenderForNoGenderSetReturnsUnknownGenderValue(): void
    {
        $subject = new \Tx_Seminars_OldModel_Speaker();

        self::assertSame(\Tx_Seminars_OldModel_Speaker::GENDER_UNKNOWN, $subject->getGender());
    }

    /**
     * @test
     */
    public function getGenderForKnownGenderReturnsGender(): void
    {
        $subject = new \Tx_Seminars_OldModel_Speaker();
        $subject->setGender(\Tx_Seminars_OldModel_Speaker::GENDER_MALE);

        self::assertSame(\Tx_Seminars_OldModel_Speaker::GENDER_MALE, $subject->getGender());
    }

    /**
     * @test
     */
    public function hasCancelationPeriodWithoutCancelationPeriodReturnsFalse(): void
    {
        $subject = new \Tx_Seminars_OldModel_Speaker();

        self::assertFalse($subject->hasCancelationPeriod());
    }

    /**
     * @test
     */
    public function hasCancelationPeriodWithCancelationPeriodReturnsTrue(): void
    {
        $subject = new \Tx_Seminars_OldModel_Speaker();
        $subject->setCancelationPeriod(42);

        self::assertTrue($subject->hasCancelationPeriod());
    }

    /**
     * @test
     */
    public function getOwnerWithoutOwnerReturnsNull(): void
    {
        $subject = new \Tx_Seminars_OldModel_Speaker();
        self::assertNull($subject->getOwner());
    }

    /**
     * @test
     */
    public function getOwnerWithOwnerReturnsOwner(): void
    {
        $subject = new \Tx_Seminars_OldModel_Speaker();
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
        $subject = new \Tx_Seminars_OldModel_Speaker();

        self::assertFalse($subject->hasImage());
    }

    /**
     * @test
     */
    public function hasImageWithImageReturnsTrue(): void
    {
        $subject = \Tx_Seminars_OldModel_Speaker::fromData(['image' => 1]);

        self::assertTrue($subject->hasImage());
    }
}
