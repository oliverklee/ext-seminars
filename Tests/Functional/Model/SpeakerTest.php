<?php

namespace OliverKlee\Seminars\Tests\Functional\Model;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Tests\Functional\Traits\FalHelper;
use TYPO3\CMS\Core\Resource\FileReference;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class SpeakerTest extends FunctionalTestCase
{
    use FalHelper;

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var \Tx_Seminars_Mapper_Speaker
     */
    private $speakerMapper = null;

    protected function setUp()
    {
        parent::setUp();

        $this->provideAdminBackEndUserForFal();

        $this->speakerMapper = new \Tx_Seminars_Mapper_Speaker();
    }

    /**
     * @test
     */
    public function getImageWithoutImageReturnsNull()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Speakers.xml');
        /** @var \Tx_Seminars_Mapper_Speaker $subject */
        $subject = $this->speakerMapper->find(1);

        self::assertNull($subject->getImage());
    }

    /**
     * @test
     */
    public function getImageWithPositiveImageCountWithoutFileReferenceReturnsNull()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Speakers.xml');
        /** @var \Tx_Seminars_Mapper_Speaker $subject */
        $subject = $this->speakerMapper->find(2);

        self::assertNull($subject->getImage());
    }

    /**
     * @test
     */
    public function getImageWithFileReferenceReturnsFileReference()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Speakers.xml');
        /** @var \Tx_Seminars_Mapper_Speaker $subject */
        $subject = $this->speakerMapper->find(3);

        $result = $subject->getImage();

        self::assertInstanceOf(FileReference::class, $result);
    }
}
