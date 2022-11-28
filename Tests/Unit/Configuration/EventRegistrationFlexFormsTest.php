<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Configuration;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Configuration\EventRegistrationFlexForms;

/**
 * @covers \OliverKlee\Seminars\Configuration\EventRegistrationFlexForms
 */
final class EventRegistrationFlexFormsTest extends UnitTestCase
{
    /**
     * @var non-empty-string
     */
    private const LOCALLANG_FILE_PREFIX = 'LLL:EXT:seminars/Resources/Private/Language/locallang.xlf:';

    /**
     * @var non-empty-string
     */
    private const LABEL_KEY_PREFIX = 'plugin.eventRegistration.settings.fieldsToShow.';

    /**
     * @var EventRegistrationFlexForms
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new EventRegistrationFlexForms();
    }

    /**
     * @return array<string, array<int, non-empty-string>>
     */
    public function inputFieldKeysDataProvider(): array
    {
        return [
            'seats' => ['seats'],
            'registeredThemselves' => ['registeredThemselves'],
            'attendeesNames' => ['attendeesNames'],
            'interests' => ['interests'],
            'expectations' => ['expectations'],
            'backgroundKnowledge' => ['backgroundKnowledge'],
            'knownFrom' => ['knownFrom'],
            'comments' => ['comments'],
            'priceCode' => ['priceCode'],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $fieldKey
     *
     * @dataProvider inputFieldKeysDataProvider
     */
    public function buildFieldsCreatesArrayWithInputFieldLabelsAndFieldKeys(string $fieldKey): void
    {
        $configuration = [];
        $this->subject->buildFields($configuration);

        self::assertArrayHasKey('items', $configuration);
        $items = $configuration['items'];
        self::assertIsArray($items);
        $expected = [
            self::LOCALLANG_FILE_PREFIX . self::LABEL_KEY_PREFIX . $fieldKey,
            $fieldKey,
            '',
            'inputFields',
        ];
        self::assertContains($expected, $items);
    }

    /**
     * @return array<string, array<int, non-empty-string>>
     */
    public function billingAddressFieldKeysDataProvider(): array
    {
        return [
            'separateBillingAddress' => ['separateBillingAddress'],
            'billingCompany' => ['billingCompany'],
            'billingFullName' => ['billingFullName'],
            'billingStreetAddress' => ['billingStreetAddress'],
            'billingZipCode' => ['billingZipCode'],
            'billingCity' => ['billingCity'],
            'billingCountry' => ['billingCountry'],
            'billingPhoneNumber' => ['billingPhoneNumber'],
            'billingEmailAddress' => ['billingEmailAddress'],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $fieldKey
     *
     * @dataProvider billingAddressFieldKeysDataProvider
     */
    public function buildFieldsCreatesArrayWithBillingAddressFieldsLabelsAndFieldKeys(string $fieldKey): void
    {
        $configuration = [];
        $this->subject->buildFields($configuration);

        self::assertArrayHasKey('items', $configuration);
        $items = $configuration['items'];
        self::assertIsArray($items);
        $expected = [
            self::LOCALLANG_FILE_PREFIX . self::LABEL_KEY_PREFIX . $fieldKey,
            $fieldKey,
            '',
            'billingAddress',
        ];
        self::assertContains($expected, $items);
    }

    /**
     * @return array<string, array<int, non-empty-string>>
     */
    public function confirmationPageKeysDataProvider(): array
    {
        return [
            'personalData' => ['personalData'],
            'consentedToTermsAndConditions' => ['consentedToTermsAndConditions'],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $fieldKey
     *
     * @dataProvider confirmationPageKeysDataProvider
     */
    public function buildFieldsCreatesArrayWithConfirmationPageLabelsAndFieldKeys(string $fieldKey): void
    {
        $configuration = [];
        $this->subject->buildFields($configuration);

        self::assertArrayHasKey('items', $configuration);
        $items = $configuration['items'];
        self::assertIsArray($items);
        $expected = [
            self::LOCALLANG_FILE_PREFIX . self::LABEL_KEY_PREFIX . $fieldKey,
            $fieldKey,
            '',
            'confirmationPage',
        ];
        self::assertContains($expected, $items);
    }
}
