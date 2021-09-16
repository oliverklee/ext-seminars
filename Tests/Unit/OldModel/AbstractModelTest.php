<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\OldModel;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\OldModel\AbstractModel;
use OliverKlee\Seminars\Tests\Unit\OldModel\Fixtures\TestingModel;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class AbstractModelTest extends UnitTestCase
{
    /**
     * @var TestingModel
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new TestingModel();
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
    public function comesFromDatabaseInitiallyIsFalse()
    {
        self::assertFalse($this->subject->comesFromDatabase());
    }

    /**
     * @test
     */
    public function fromDataCreatesInstanceOfSubclass()
    {
        $result = TestingModel::fromData([]);

        self::assertInstanceOf(TestingModel::class, $result);
    }

    /**
     * @test
     */
    public function fromDataProvidesModelWithGivenData()
    {
        $result = TestingModel::fromData(['test' => true]);

        self::assertTrue($result->getBooleanTest());
    }

    /**
     * @test
     */
    public function fromDataResultsInOkay()
    {
        $subject = TestingModel::fromData(['title' => 'Foo']);

        self::assertTrue($subject->isOk());
    }

    /**
     * @test
     */
    public function comesFromDatabaseAfterFromDataWithEmptyDataIsFalse()
    {
        $subject = TestingModel::fromData(['title' => 'Foo']);

        self::assertFalse($subject->comesFromDatabase());
    }

    /**
     * @test
     */
    public function comesFromDatabaseAfterFromDataWithNonEmptyDataIsFalse()
    {
        $subject = TestingModel::fromData(['title' => 'Foo']);

        self::assertFalse($subject->comesFromDatabase());
    }

    /**
     * @test
     */
    public function getRecordPropertyStringByDefaultReturnsEmptyString()
    {
        self::assertSame('', $this->subject->getRecordPropertyString('title'));
    }

    /**
     * @test
     */
    public function getRecordPropertyStringReturnsValue()
    {
        $value = 'A nice object!';
        $subject = TestingModel::fromData(['something' => $value]);

        self::assertSame($value, $subject->getRecordPropertyString('something'));
    }

    /**
     * @test
     */
    public function getRecordPropertyStringReturnsTrimmedValue()
    {
        $value = 'A nice object!';
        $subject = TestingModel::fromData(['something' => ' ' . $value . ' ']);

        self::assertSame($value, $subject->getRecordPropertyString('something'));
    }

    /**
     * @test
     */
    public function hasRecordPropertyStringForInexistentKeyReturnsFalse()
    {
        self::assertFalse($this->subject->hasRecordPropertyString('something'));
    }

    /**
     * @test
     */
    public function hasRecordPropertyStringForEmptyStringReturnsFalse()
    {
        $subject = TestingModel::fromData(['something' => '']);

        self::assertFalse($subject->hasRecordPropertyString('something'));
    }

    /**
     * @test
     */
    public function hasRecordPropertyStringForWhitespaceOnlyStringReturnsFalse()
    {
        $subject = TestingModel::fromData(['something' => ' ']);

        self::assertFalse($subject->hasRecordPropertyString('something'));
    }

    /**
     * @test
     */
    public function hasRecordPropertyStringForNonEmptyStringReturnsTrue()
    {
        $subject = TestingModel::fromData(['something' => 'The cake is a lie.']);

        self::assertTrue($subject->hasRecordPropertyString('something'));
    }

    /**
     * @test
     */
    public function getRecordPropertyDecimalByDefaultReturnsZeroWithDecimals()
    {
        self::assertSame('0.00', $this->subject->getRecordPropertyDecimal('something'));
    }

    /**
     * @test
     */
    public function getRecordPropertyDecimalReturnsValue()
    {
        $value = '12.34';
        $subject = TestingModel::fromData(['something' => $value]);

        self::assertSame($value, $subject->getRecordPropertyDecimal('something'));
    }

    /**
     * @test
     */
    public function getRecordPropertyDecimalReturnsTrimmedValue()
    {
        $value = '12.34';
        $subject = TestingModel::fromData(['something' => ' ' . $value . ' ']);

        self::assertSame($value, $subject->getRecordPropertyDecimal('something'));
    }

    /**
     * @test
     */
    public function hasRecordPropertyDecimalForInexistentKeyReturnsFalse()
    {
        self::assertFalse($this->subject->hasRecordPropertyDecimal('something'));
    }

    /**
     * @test
     */
    public function hasRecordPropertyDecimalForEmptyDecimalReturnsFalse()
    {
        $subject = TestingModel::fromData(['something' => '']);

        self::assertFalse($subject->hasRecordPropertyDecimal('something'));
    }

    /**
     * @test
     */
    public function hasRecordPropertyDecimalForZeroDecimalReturnsFalse()
    {
        $subject = TestingModel::fromData(['something' => '0.00']);

        self::assertFalse($subject->hasRecordPropertyDecimal('something'));
    }

    /**
     * @test
     */
    public function hasRecordPropertyDecimalForZeroWithoutDecimalsReturnsFalse()
    {
        $subject = TestingModel::fromData(['something' => '0']);

        self::assertFalse($subject->hasRecordPropertyDecimal('something'));
    }

    /**
     * @test
     */
    public function hasRecordPropertyDecimalForPositiveDecimalReturnsTrue()
    {
        $subject = TestingModel::fromData(['something' => '12.00']);

        self::assertTrue($subject->hasRecordPropertyDecimal('something'));
    }

    /**
     * @test
     */
    public function getRecordPropertyIntegerByDefaultReturnsZero()
    {
        self::assertSame(0, $this->subject->getRecordPropertyInteger('something'));
    }

    /**
     * @test
     */
    public function getRecordPropertyIntegerReturnsValueFromInteger()
    {
        $value = 42;
        $subject = TestingModel::fromData(['something' => $value]);

        self::assertSame($value, $subject->getRecordPropertyInteger('something'));
    }

    /**
     * @test
     */
    public function getRecordPropertyIntegerReturnsValueFromString()
    {
        $value = 42;
        $subject = TestingModel::fromData(['something' => (string)$value]);

        self::assertSame($value, $subject->getRecordPropertyInteger('something'));
    }

    /**
     * @test
     */
    public function hasRecordPropertyIntegerForInexistentKeyReturnsFalse()
    {
        self::assertFalse($this->subject->hasRecordPropertyInteger('something'));
    }

    /**
     * @test
     */
    public function hasRecordPropertyIntegerForZeroIntegerReturnsFalse()
    {
        $subject = TestingModel::fromData(['something' => 0]);

        self::assertFalse($subject->hasRecordPropertyInteger('something'));
    }

    /**
     * @test
     */
    public function hasRecordPropertyIntegerForZeroStringReturnsFalse()
    {
        $subject = TestingModel::fromData(['something' => '0']);

        self::assertFalse($subject->hasRecordPropertyInteger('something'));
    }

    /**
     * @test
     */
    public function hasRecordPropertyIntegerForPositiveIntegerReturnsTrue()
    {
        $subject = TestingModel::fromData(['something' => 12]);

        self::assertTrue($subject->hasRecordPropertyInteger('something'));
    }

    /**
     * @test
     */
    public function hasRecordPropertyIntegerForNegativeIntegerReturnsTrue()
    {
        $subject = TestingModel::fromData(['something' => -12]);

        self::assertTrue($subject->hasRecordPropertyInteger('something'));
    }

    /**
     * @test
     */
    public function getRecordPropertyBooleanByDefaultReturnsFalse()
    {
        self::assertFalse($this->subject->getRecordPropertyBoolean('something'));
    }

    /**
     * @return bool[][]
     */
    public function booleanDataProvider(): array
    {
        return [
            'false' => [false],
            'true' => [true],
        ];
    }

    /**
     * @test
     *
     * @param bool $value
     *
     * @dataProvider booleanDataProvider
     */
    public function getRecordPropertyBooleanReturnsValueFromString(bool $value)
    {
        $subject = TestingModel::fromData(['something' => (string)(int)$value]);

        self::assertSame($value, $subject->getRecordPropertyBoolean('something'));
    }

    /**
     * @test
     *
     * @param bool $value
     *
     * @dataProvider booleanDataProvider
     */
    public function getRecordPropertyBooleanReturnsValueFromInt(bool $value)
    {
        $subject = TestingModel::fromData(['something' => (int)$value]);

        self::assertSame($value, $subject->getRecordPropertyBoolean('something'));
    }

    /**
     * @test
     */
    public function createMmRecordsForEmptyTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1333292359);

        $this->subject->createMmRecords('', []);
    }

    /**
     * @test
     */
    public function createMmRecordsOnObjectWithoutUidThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionCode(1333292371);

        $this->subject->createMmRecords('tx_seminars_test_test_mm', []);
    }
}
