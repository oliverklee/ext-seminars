<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\FrontEnd;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * @covers \Tx_Seminars_FrontEnd_RegistrationForm
 */
final class RegistrationFormTest extends FunctionalTestCase
{
    /**
     * @var array<int, string>
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var \Tx_Seminars_FrontEnd_RegistrationForm
     */
    private $subject = null;

    /**
     * @var ContentObjectRenderer
     */
    private $contentObject = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contentObject = $this->prophesize(ContentObjectRenderer::class)->reveal();
        $this->subject = new \Tx_Seminars_FrontEnd_RegistrationForm([], $this->contentObject);
    }

    /**
     * @test
     */
    public function populateCheckboxesForEventWithoutCheckboxesReturnsEmptyArray(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationEditor/EventsWithCheckboxes.xml');

        $event = \Tx_Seminars_OldModel_Event::fromUid(1);
        $this->subject->setSeminar($event);

        $result = $this->subject->populateCheckboxes();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function populateCheckboxesForEventWithCheckboxesReturnsCheckboxes(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationEditor/EventsWithCheckboxes.xml');

        $event = \Tx_Seminars_OldModel_Event::fromUid(2);
        $this->subject->setSeminar($event);

        $result = $this->subject->populateCheckboxes();

        $expected = [['caption' => 'Checkbox 1', 'value' => 1]];
        self::assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function populateCheckboxesForEventWithCheckboxesReturnsCheckboxesOrderedBySorting(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationEditor/EventsWithCheckboxes.xml');

        $event = \Tx_Seminars_OldModel_Event::fromUid(3);
        $this->subject->setSeminar($event);

        $result = $this->subject->populateCheckboxes();

        $expected = [['caption' => 'Checkbox 2', 'value' => 2], ['caption' => 'Checkbox 1', 'value' => 1]];
        self::assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function hasCheckboxesForEventWithoutCheckboxesAndCheckboxesFieldDisabledReturnsFalse(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationEditor/EventsWithCheckboxes.xml');

        $configuration = ['showRegistrationFields' => ''];
        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm($configuration, $this->contentObject);

        $event = \Tx_Seminars_OldModel_Event::fromUid(1);
        $subject->setSeminar($event);

        self::assertFalse($subject->hasCheckboxes());
    }

    /**
     * @test
     */
    public function hasCheckboxesForEventWithoutCheckboxesAndCheckboxesFieldEnabledReturnsFalse(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationEditor/EventsWithCheckboxes.xml');

        $configuration = ['showRegistrationFields' => 'checkboxes'];
        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm($configuration, $this->contentObject);

        $event = \Tx_Seminars_OldModel_Event::fromUid(1);
        $subject->setSeminar($event);

        self::assertFalse($subject->hasCheckboxes());
    }

    /**
     * @test
     */
    public function hasCheckboxesForEventWithCheckboxesAndCheckboxesFieldDisabledReturnsFalse(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationEditor/EventsWithCheckboxes.xml');

        $configuration = ['showRegistrationFields' => ''];
        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm($configuration, $this->contentObject);

        $event = \Tx_Seminars_OldModel_Event::fromUid(2);
        $subject->setSeminar($event);

        self::assertFalse($subject->hasCheckboxes());
    }

    /**
     * @test
     */
    public function hasCheckboxesForEventWithCheckboxesAndCheckboxesFieldEnabledReturnsTrue(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationEditor/EventsWithCheckboxes.xml');

        $configuration = ['showRegistrationFields' => 'checkboxes'];
        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm($configuration, $this->contentObject);

        $event = \Tx_Seminars_OldModel_Event::fromUid(2);
        $subject->setSeminar($event);

        self::assertTrue($subject->hasCheckboxes());
    }
}
