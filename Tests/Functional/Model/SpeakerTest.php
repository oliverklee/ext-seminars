<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Model;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Tests\Functional\Traits\FalHelper;
use TYPO3\CMS\Core\Resource\FileReference;

final class SpeakerTest extends FunctionalTestCase
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->provideAdminBackEndUserForFal();

        $this->speakerMapper = new \Tx_Seminars_Mapper_Speaker();
    }

    /**
     * @test
     */
    public function getImageWithoutImageReturnsNull(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Speakers.xml');

        $subject = $this->speakerMapper->find(1);

        self::assertNull($subject->getImage());
    }

    /**
     * @test
     */
    public function getImageWithPositiveImageCountWithoutFileReferenceReturnsNull(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Speakers.xml');

        $subject = $this->speakerMapper->find(2);

        self::assertNull($subject->getImage());
    }

    /**
     * @test
     */
    public function getImageWithFileReferenceReturnsFileReference(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Speakers.xml');

        $subject = $this->speakerMapper->find(3);

        $result = $subject->getImage();

        self::assertInstanceOf(FileReference::class, $result);
    }
}
