<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BackEnd;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\BackEnd\FlexForms;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Information\Typo3Version;

final class FlexFormsTest extends TestCase
{
    /**
     * @var FlexForms
     */
    private $subject;

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    protected function setUp(): void
    {
        if ((new Typo3Version())->getMajorVersion() >= 11) {
            self::markTestSkipped('Skipping because this code will be removed before adding 11LTS compatibility.');
        }

        $this->testingFramework = new TestingFramework('tx_seminars');
        $this->subject = new FlexForms();
    }

    protected function tearDown(): void
    {
        if ($this->testingFramework instanceof TestingFramework) {
            $this->testingFramework->cleanUp();
        }
    }

    /**
     * @test
     */
    public function classCanBeInstantiated(): void
    {
        self::assertInstanceOf(FlexForms::class, $this->subject);
    }
}
