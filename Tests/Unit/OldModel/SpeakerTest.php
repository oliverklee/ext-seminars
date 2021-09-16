<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\OldModel;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Seminars\OldModel\AbstractModel;

/**
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class SpeakerTest extends UnitTestCase
{
    /**
     * @var \Tx_Seminars_OldModel_Speaker
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new \Tx_Seminars_OldModel_Speaker();
    }

    /**
     * @test
     */
    public function isAbstractModel()
    {
        self::assertInstanceOf(AbstractModel::class, $this->subject);
    }

    /**
     * @test
     */
    public function fromDataCreatesInstanceOfSubclass()
    {
        $result = \Tx_Seminars_OldModel_Speaker::fromData([]);

        self::assertInstanceOf(\Tx_Seminars_OldModel_Speaker::class, $result);
    }

    /**
     * @test
     */
    public function hasOrganizationWithNoOrganizationReturnsFalse()
    {
        $subject = \Tx_Seminars_OldModel_Speaker::fromData(['organization' => '']);

        self::assertFalse($subject->hasOrganization());
    }

    /**
     * @test
     */
    public function hasOrganizationWithOrganizationReturnsTrue()
    {
        $organization = 'Foo inc.';
        $subject = \Tx_Seminars_OldModel_Speaker::fromData(['organization' => $organization]);

        self::assertTrue($subject->hasOrganization());
    }

    /**
     * @test
     */
    public function hasHomepageWithNoHomepageReturnsFalse()
    {
        $subject = \Tx_Seminars_OldModel_Speaker::fromData(['homepage' => '']);

        self::assertFalse($subject->hasHomepage());
    }

    /**
     * @test
     */
    public function hasHomepageWithHomepageReturnsTrue()
    {
        $homepage = 'Foo inc.';
        $subject = \Tx_Seminars_OldModel_Speaker::fromData(['homepage' => $homepage]);

        self::assertTrue($subject->hasHomepage());
    }

    /**
     * @test
     */
    public function hasDescriptionWithNoDescriptionReturnsFalse()
    {
        $subject = \Tx_Seminars_OldModel_Speaker::fromData(['description' => '']);

        self::assertFalse($subject->hasDescription());
    }

    /**
     * @test
     */
    public function hasDescriptionWithDescriptionReturnsTrue()
    {
        $description = 'Foo inc.';
        $subject = \Tx_Seminars_OldModel_Speaker::fromData(['description' => $description]);

        self::assertTrue($subject->hasDescription());
    }

    /**
     * @test
     */
    public function hasSkillsInitiallyIsFalse()
    {
        $subject = new \Tx_Seminars_OldModel_Speaker();

        self::assertFalse($subject->hasSkills());
    }

    /**
     * @test
     */
    public function getSkillsShortWithNoSkillReturnsEmptyString()
    {
        $subject = new \Tx_Seminars_OldModel_Speaker();

        self::assertSame('', $subject->getSkillsShort());
    }

    /**
     * @test
     */
    public function getNumberOfSkillsReturnsNumberOfSkills()
    {
        $subject = \Tx_Seminars_OldModel_Speaker::fromData(['skills' => 2]);

        self::assertSame(2, $subject->getNumberOfSkills());
    }

    /**
     * @test
     */
    public function getGenderForNoGenderSetReturnsUnknownGenderValue()
    {
        $subject = new \Tx_Seminars_OldModel_Speaker();

        self::assertSame(\Tx_Seminars_OldModel_Speaker::GENDER_UNKNOWN, $subject->getGender());
    }

    /**
     * @test
     */
    public function getGenderForKnownGenderReturnsGender()
    {
        $subject = new \Tx_Seminars_OldModel_Speaker();
        $subject->setGender(\Tx_Seminars_OldModel_Speaker::GENDER_MALE);

        self::assertSame(\Tx_Seminars_OldModel_Speaker::GENDER_MALE, $subject->getGender());
    }

    /**
     * @test
     */
    public function hasCancelationPeriodWithoutCancelationPeriodReturnsFalse()
    {
        $subject = new \Tx_Seminars_OldModel_Speaker();

        self::assertFalse($subject->hasCancelationPeriod());
    }

    /**
     * @test
     */
    public function hasCancelationPeriodWithCancelationPeriodReturnsTrue()
    {
        $subject = new \Tx_Seminars_OldModel_Speaker();
        $subject->setCancelationPeriod(42);

        self::assertTrue($subject->hasCancelationPeriod());
    }

    /**
     * @test
     */
    public function getOwnerWithoutOwnerReturnsNull()
    {
        $subject = new \Tx_Seminars_OldModel_Speaker();
        self::assertNull($subject->getOwner());
    }

    /**
     * @test
     */
    public function getOwnerWithOwnerReturnsOwner()
    {
        $subject = new \Tx_Seminars_OldModel_Speaker();
        /** @var \Tx_Seminars_Model_FrontEndUser $frontEndUser */
        $frontEndUser = MapperRegistry::get(\Tx_Seminars_Mapper_FrontEndUser::class)->getNewGhost();
        $subject->setOwner($frontEndUser);

        self::assertSame($frontEndUser, $subject->getOwner());
    }

    /**
     * @test
     */
    public function hasImageWithoutImageReturnsFalse()
    {
        $subject = new \Tx_Seminars_OldModel_Speaker();

        self::assertFalse($subject->hasImage());
    }

    /**
     * @test
     */
    public function hasImageWithImageReturnsTrue()
    {
        $subject = \Tx_Seminars_OldModel_Speaker::fromData(['image' => 1]);

        self::assertTrue($subject->hasImage());
    }
}
