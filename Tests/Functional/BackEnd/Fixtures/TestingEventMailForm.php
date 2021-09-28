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
     * @var string
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
     * @param string $fieldName
     *        the name of the field for which to get the initial value, must be
     *        either 'subject' or 'messageBody'
     *
     * @return string the initial value of the field, will be empty if no
     *                initial value is defined
     */
    public function getInitialValue(string $fieldName): string
    {
        return parent::getInitialValue($fieldName);
    }

    /**
     * Sets the date format for the event.
     */
    public function setDateFormat(): void
    {
        $this->getOldEvent()->setConfigurationValue('dateFormatYMD', '%d.%m.%Y');
    }

    /**
     * Sets an error message.
     *
     * @param string $fieldName the field name to set the error message for, must be "messageBody" or "subject"
     * @param string $message the error message to set, may be empty
     */
    public function setErrorMessage(string $fieldName, string $message): void
    {
        parent::setErrorMessage($fieldName, $message);
    }

    /**
     * Returns all error messages set via setErrorMessage for the given field
     * name.
     *
     * @param string $fieldName
     *        the field name for which the error message should be returned,
     *        must not be empty
     *
     * @return string the error message for the field, will be empty if there's
     *                no error message for this field
     */
    public function getErrorMessage(string $fieldName): string
    {
        return parent::getErrorMessage($fieldName);
    }
}
