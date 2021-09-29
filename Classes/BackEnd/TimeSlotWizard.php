<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\BackEnd;

use Recurr\Rule;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Form\Exception as FormException;
use TYPO3\CMS\Backend\Template\Components\Buttons\InputButton;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Form engine wizard that renders a wizard for creating a series of time slots.
 */
class TimeSlotWizard extends AbstractFormElement
{
    /**
     * @var string
     */
    private const LABEL_KEY_PREFIX = 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.time_slot_wizard.';

    /**
     * @var array<int, string>
     */
    private const FREQUENCIES = ['daily', 'weekly', 'monthly', 'yearly'];

    /**
     * @var string
     */
    private const DEFAULT_FREQUENCY = 'weekly';

    public function render(): array
    {
        $result = $this->initializeResultArray();
        // The time-slot wizard uses a Composer-provided library and hence is a Composer-only feature.
        if (!\class_exists(Rule::class)) {
            $result['html'] = '';
            return $result;
        }

        $result['requireJsModules'] = ['TYPO3/CMS/Seminars/TimeSlotWizard'];
        $result['stylesheetFiles'] = ['EXT:seminars/Resources/Public/CSS/BackEnd/TimeSlotWizard.css'];

        $html = $this->buildToggleButtons() .
            '<div class="t3-form-field-item t3js-formengine-timeslotwizard-toggleable ' .
            't3js-formengine-timeslotwizard-wrapper hidden">' .
            $this->buildTwoColumnLayout($this->buildDatePicker('first_start'), $this->buildDatePicker('first_end')) .
            $this->buildTwoColumnLayoutWithHeading($this->buildFrequencyInput(), $this->buildRecurrenceRadioButtons()) .
            $this->buildOneColumnLayout($this->buildDatePicker('until')) .
            $this->buildOneColumnLayout($this->buildSubmitButton()) .
            '</div>';

        $result['html'] = $html;

        return $result;
    }

    private function buildToggleButtons(): string
    {
        return '<span class="input-group-btn">' .
            $this->buildSingleToggleButton('show', 'actions-document-new') .
            $this->buildSingleToggleButton('hide', 'actions-edit-hide', 'hidden') .
            '</span>';
    }

    private function buildSingleToggleButton(string $labelKey, string $icon, string $additionalClass = ''): string
    {
        /** @var IconFactory $iconFactory */
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);

        $allClasses = 'btn btn-default ' .
            't3js-formengine-timeslotwizard-toggle t3js-formengine-timeslotwizard-toggleable ' . $additionalClass;
        return '<button class="' . $allClasses . '" type="button">' .
            $iconFactory->getIcon($icon, Icon::SIZE_SMALL) . ' ' .
            $this->createEncodedLabel($labelKey) .
            '</button>';
    }

    private function createFormLabel(string $labelKey): string
    {
        return '<label class="t3js-formengine-label">' . $this->createEncodedLabel($labelKey) . '</label>';
    }

    private function createEncodedLabel(string $labelKey): string
    {
        $languageService = $this->getLanguageService();
        return \htmlspecialchars($languageService->sL(self::LABEL_KEY_PREFIX . $labelKey), ENT_HTML5 | ENT_QUOTES);
    }

    /**
     * @throws FormException
     */
    private function buildDatePicker(string $fieldKey): string
    {
        $originalConfiguration = $this->data['parameterArray'];
        $configuration = [
            'renderType' => 'inputDateTime',
            'tableName' => $this->data['tableName'],
            'fieldName' => 'time_slot_wizard_' . $fieldKey,
            'parameterArray' => [
                'fieldConf' => [
                    'config' => [
                        'eval' => 'datetime',
                    ],
                ],
                'itemFormElName' => $originalConfiguration['itemFormElName'] . '[' . $fieldKey . ']',
                'itemFormElID' => $originalConfiguration['itemFormElID'] . '_' . $fieldKey,
                'itemFormElValue' => '',
            ],
        ];
        $formElement = $this->nodeFactory->create($configuration)->render();
        $label = $this->createFormLabel($fieldKey);

        return $label . $formElement['html'];
    }

    private function buildRecurrenceHeader(): string
    {
        return $this->createFormLabel('reccurrence');
    }

    private function buildFrequencyInput(): string
    {
        $fieldKey = 'all';

        $originalConfiguration = $this->data['parameterArray'];
        $configuration = [
            'renderType' => 'input',
            'tableName' => $this->data['tableName'],
            'fieldName' => 'time_slot_wizard_' . $fieldKey,
            'parameterArray' => [
                'fieldConf' => [
                    'config' => [
                        'type' => 'input',
                        'size' => 10,
                        'max' => 3,
                        'eval' => 'int,trim',
                        'range' => [
                            'lower' => 1,
                            'upper' => 999,
                        ],
                    ],
                ],
                'itemFormElName' => $originalConfiguration['itemFormElName'] . '[' . $fieldKey . ']',
                'itemFormElID' => $originalConfiguration['itemFormElID'] . '_' . $fieldKey,
                'itemFormElValue' => '1',
            ],
        ];
        $formElement = $this->nodeFactory->create($configuration)->render();
        $label = $this->createEncodedLabel('reccurrence.all');

        return $label . $formElement['html'];
    }

    private function buildRecurrenceRadioButtons(): string
    {
        $fieldKey = 'frequency';

        $items = [];
        foreach (self::FREQUENCIES as $frequency) {
            $items[] = [$this->createEncodedLabel('reccurrence.' . $frequency), $frequency];
        }

        $originalConfiguration = $this->data['parameterArray'];
        $configuration = [
            'renderType' => 'radio',
            'tableName' => $this->data['tableName'],
            'fieldName' => 'time_slot_wizard_' . $fieldKey,
            'parameterArray' => [
                'fieldConf' => [
                    'config' => [
                        'type' => 'radio',
                        'items' => $items,
                    ],
                ],
                'itemFormElName' => $originalConfiguration['itemFormElName'] . '[' . $fieldKey . ']',
                'itemFormElID' => $originalConfiguration['itemFormElID'] . '_' . $fieldKey,
                'itemFormElValue' => self::DEFAULT_FREQUENCY,
                'fieldChangeFunc' => [],
            ],
        ];
        $formElement = $this->nodeFactory->create($configuration)->render();

        return $formElement['html'];
    }

    private function buildSubmitButton(): string
    {
        /** @var IconFactory $iconFactory */
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);

        /** @var InputButton $button */
        $button = GeneralUtility::makeInstance(InputButton::class);
        $button->setTitle($this->getLanguageService()->sL(self::LABEL_KEY_PREFIX . 'create'))
            ->setName('_savedok')
            ->setValue('1')
            ->setForm('EditDocumentController')
            ->setIcon($iconFactory->getIcon('actions-document-save', Icon::SIZE_SMALL));

        return $button->setShowLabelText(true)->render();
    }

    private function buildOneColumnLayout(string $content): string
    {
        return '<div class="form-timeslotwizard-section">' . $content .
            '   </div>';
    }

    private function buildTwoColumnLayoutWithHeading(string $leftContent, string $rightContent): string
    {
        return '<div class="form-timeslotwizard-section">' .
            $this->buildRecurrenceHeader() .
            '    <div class="form-timeslotwizard-multicolumn-wrap">' .
            $this->buildSingleColumn($leftContent) . $this->buildSingleColumn($rightContent) .
            '    </div>' .
            '</div>';
    }

    private function buildTwoColumnLayout(string $leftContent, string $rightContent): string
    {
        return '<div class="form-timeslotwizard-multicolumn-wrap form-timeslotwizard-section">' .
            $this->buildSingleColumn($leftContent) . $this->buildSingleColumn($rightContent) .
            '</div>';
    }

    private function buildSingleColumn(string $content): string
    {
        return '<div class="form-timeslotwizard-multicolumn-column">' . $content . '</div>';
    }
}
