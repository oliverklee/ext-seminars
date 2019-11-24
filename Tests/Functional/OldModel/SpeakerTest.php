<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\OldModel;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Tests\Functional\Traits\FalHelper;
use TYPO3\CMS\Core\Resource\FileReference;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class SpeakerTest extends FunctionalTestCase
{
    use FalHelper;

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var \Tx_Seminars_OldModel_Speaker
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();

        $this->provideAdminBackEndUserForFal();
    }

    ///////////////////////
    // Utility functions.
    ///////////////////////

    /**
     * Inserts a skill record into the database and creates a relation to it
     * from the fixture.
     *
     * @param array $skillData data of the skill to add, may be empty
     *
     * @return int the UID of the created record, will always be > 0
     */
    private function addSkillRelation(array $skillData): int
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_skills', $skillData);

        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_speakers',
            $this->subject->getUid(),
            $uid,
            'skills'
        );

        $this->subject = new \Tx_Seminars_OldModel_Speaker($this->subject->getUid());

        return $uid;
    }

    /////////////////////////////////////
    // Tests for the utility functions.
    /////////////////////////////////////

    /**
     * @test
     */
    public function addSkillRelationReturnsUid()
    {
        $this->createPersistedSubject();
        self::assertGreaterThan(0, $this->addSkillRelation([]));
    }

    /**
     * @test
     */
    public function addSkillRelationCreatesDifferentUids()
    {
        $this->createPersistedSubject();
        self::assertNotSame(
            $this->addSkillRelation([]),
            $this->addSkillRelation([])
        );
    }

    /**
     * @test
     */
    public function addSkillRelationIncreasesTheNumberOfSkills()
    {
        $this->createPersistedSubject();

        self::assertSame(0, $this->subject->getNumberOfSkills());

        $this->addSkillRelation([]);
        self::assertSame(1, $this->subject->getNumberOfSkills());

        $this->addSkillRelation([]);
        self::assertSame(2, $this->subject->getNumberOfSkills());
    }

    /**
     * @test
     */
    public function addSkillRelationCreatesRelations()
    {
        $this->createPersistedSubject();

        self::assertSame(
            0,
            $this->testingFramework->countRecords(
                'tx_seminars_speakers_skills_mm',
                'uid_local=' . $this->subject->getUid()
            )
        );

        $this->addSkillRelation([]);
        self::assertSame(
            1,
            $this->testingFramework->countRecords(
                'tx_seminars_speakers_skills_mm',
                'uid_local=' . $this->subject->getUid()
            )
        );

        $this->addSkillRelation([]);
        self::assertSame(
            2,
            $this->testingFramework->countRecords(
                'tx_seminars_speakers_skills_mm',
                'uid_local=' . $this->subject->getUid()
            )
        );
    }

    /**
     * @test
     */
    public function createFromUidMapsAllFields()
    {
        $this->createPersistedSubject();

        self::assertSame('Test speaker', $this->subject->getTitle());
        self::assertSame('Foo inc.', $this->subject->getOrganization());
        self::assertSame('https://www.example.com/', $this->subject->getHomepage());
        self::assertSame("foo\nbar", $this->subject->getDescriptionRaw());
        self::assertSame('test notes', $this->subject->getNotes());
        self::assertSame('test address', $this->subject->getAddress());
        self::assertSame('123', $this->subject->getPhoneWork());
        self::assertSame('456', $this->subject->getPhoneHome());
        self::assertSame('789', $this->subject->getPhoneMobile());
        self::assertSame('000', $this->subject->getFax());
        self::assertSame('maximal-foo@example.com', $this->subject->getEmail());
        self::assertSame(4, $this->subject->getCancelationPeriodInDays());
    }

    /**
     * @test
     */
    public function canHaveOneSkill()
    {
        $this->createPersistedSubject();

        $this->addSkillRelation([]);

        self::assertTrue($this->subject->hasSkills());
    }

    /**
     * @test
     */
    public function getSkillsShortWithSingleSkillReturnsSingleSkill()
    {
        $this->createPersistedSubject();

        $title = 'Test title';
        $this->addSkillRelation(['title' => $title]);

        self::assertSame($title, $this->subject->getSkillsShort());
    }

    /**
     * @test
     */
    public function getSkillsShortWithMultipleSkillsReturnsMultipleSkills()
    {
        $this->createPersistedSubject();

        $firstTitle = 'Skill 1';
        $secondTitle = 'Skill 2';
        $this->addSkillRelation(['title' => $firstTitle]);
        $this->addSkillRelation(['title' => $secondTitle]);

        self::assertSame($firstTitle . ', ' . $secondTitle, $this->subject->getSkillsShort());
    }

    /**
     * @return void
     */
    private function createPersistedSubject()
    {
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            [
                'title' => 'Test speaker',
                'organization' => 'Foo inc.',
                'homepage' => 'https://www.example.com/',
                'description' => 'foo' . LF . 'bar',
                'notes' => 'test notes',
                'address' => 'test address',
                'phone_work' => '123',
                'phone_home' => '456',
                'phone_mobile' => '789',
                'fax' => '000',
                'email' => 'maximal-foo@example.com',
                'cancelation_period' => 4,
            ]
        );
        $this->subject = new \Tx_Seminars_OldModel_Speaker($uid);
    }

    /**
     * @test
     */
    public function getImageWithoutImageReturnsNull()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Speakers.xml');
        $subject = new \Tx_Seminars_OldModel_Speaker(1);

        self::assertNull($subject->getImage());
    }

    /**
     * @test
     */
    public function getImageWithPositiveImageCountWithoutFileReferenceReturnsNull()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Speakers.xml');
        $subject = new \Tx_Seminars_OldModel_Speaker(2);

        self::assertNull($subject->getImage());
    }

    /**
     * @test
     */
    public function getImageWithFileReferenceReturnsFileReference()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Speakers.xml');

        $result = (new \Tx_Seminars_OldModel_Speaker(3))->getImage();

        self::assertInstanceOf(FileReference::class, $result);
    }
}
