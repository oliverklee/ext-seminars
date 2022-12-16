<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\BackEnd\Fixtures;

use OliverKlee\Seminars\BackEnd\AbstractEventMailForm;

/**
 * This class represents a testing implementation of the AbstractEventMailForm class.
 */
final class TestingEventMailForm extends AbstractEventMailForm
{
    /**
     * the prefix for all locallang keys for prefilling the form, must not be empty
     *
     * @var non-empty-string
     */
    protected $formFieldPrefix = 'testForm_prefillField_';

    /**
     * Returns the label for the submit button.
     *
     * @return string label for the submit button, will not be empty
     */
    protected function getSubmitButtonLabel(): string
    {
        return $this->getLanguageService()->getLL('eventMailForm_confirmButton');
    }

    /**
     * Returns the initial value for a certain field.
     *
     * @param non-empty-string $fieldName the name of the field for which to get the initial value,
     *        must be either 'subject' or 'messageBody'
     *
     * @return string the initial value of the field, will be empty if no
     *                initial value is defined
     */
    public function getInitialValue(string $fieldName): string
    {
        return parent::getInitialValue($fieldName);
    }
}
