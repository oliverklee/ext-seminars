<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\OldModel;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\OldModel\AbstractModel;

/**
 * Test case.
 *
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
}
