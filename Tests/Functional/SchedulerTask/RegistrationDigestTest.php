<?php
namespace OliverKlee\Seminars\Tests\Functional\SchedulerTask;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\SchedulerTask\RegistrationDigest;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class RegistrationDigestTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = ['scheduler'];

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/seminars'];

    /**
     * @var RegistrationDigest
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = new RegistrationDigest();
    }

    protected function tearDown()
    {
        \Tx_Oelib_MapperRegistry::purgeInstance();
        \Tx_Oelib_ConfigurationRegistry::purgeInstance();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function isInitializedInitiallyReturnsFalse()
    {
        self::assertFalse($this->subject->isInitialized());
    }

    /**
     * @test
     */
    public function isInitializedAfterInitializeObjectReturnsTrue()
    {
        $this->subject->initializeObject();

        self::assertTrue($this->subject->isInitialized());
    }

    /**
     * @test
     */
    public function initializeObjectCanBeCalledTwice()
    {
        $this->subject->initializeObject();
        $this->subject->initializeObject();

        self::assertTrue($this->subject->isInitialized());
    }

    /**
     * @test
     */
    public function initializeObjectInitializesConfiguration()
    {
        $this->subject->initializeObject();

        self::assertInstanceOf(\Tx_Oelib_Configuration::class, $this->subject->getConfiguration());
    }

    /**
     * @test
     */
    public function initializeObjectInitializesEventMapper()
    {
        $this->subject->initializeObject();

        self::assertInstanceOf(\Tx_Seminars_Mapper_Event::class, $this->subject->getEventMapper());
    }

    /**
     * @test
     */
    public function objectManagerInitializesObject()
    {
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $subject = $objectManager->get(RegistrationDigest::class);

        self::assertTrue($subject->isInitialized());
    }
}
