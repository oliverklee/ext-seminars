<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\OldModel;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\OldModel\AbstractModel;

final class CategoryTest extends UnitTestCase
{
    /**
     * @var \Tx_Seminars_OldModel_Category
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new \Tx_Seminars_OldModel_Category();
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
        $result = \Tx_Seminars_OldModel_Category::fromData([]);

        self::assertInstanceOf(\Tx_Seminars_OldModel_Category::class, $result);
    }

    /**
     * @test
     */
    public function getTitleReturnsTitle()
    {
        $title = 'Test category';
        $subject = \Tx_Seminars_OldModel_Category::fromData(['title' => $title]);

        self::assertSame($title, $subject->getTitle());
    }

    /**
     * @test
     */
    public function getIconReturnsIcon()
    {
        $icon = 'foo.gif';
        $subject = \Tx_Seminars_OldModel_Category::fromData(['icon' => $icon]);

        self::assertSame($icon, $subject->getIcon());
    }
}
