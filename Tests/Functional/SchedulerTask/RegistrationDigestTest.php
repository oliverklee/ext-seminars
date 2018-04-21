<?php
namespace OliverKlee\Seminars\Tests\Functional\SchedulerTask;

use OliverKlee\Seminars\SchedulerTask\RegistrationDigest;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class RegistrationDigestTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var RegistrationDigest
     */
    private $subject = null;

    protected function setUp()
    {
        if (!ExtensionManagementUtility::isLoaded('scheduler')) {
            self::markTestSkipped('This tests needs the scheduler extension.');
        }

        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');
        $this->subject = new RegistrationDigest();
    }

    protected function tearDown()
    {
        if ($this->testingFramework !== null) {
            $this->testingFramework->cleanUp();
        }
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
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $subject = $objectManager->get(RegistrationDigest::class);

        self::assertTrue($subject->isInitialized());
    }
}
