<?php

/**
 * This class represents a testing implementation of the AbstractEventMailForm class.
 *
 * @author Mario Rimann <mario@screenteam.com>
 */
class Tx_Seminars_Tests_Unit_Fixtures_BackEnd_TestingEventMailForm extends Tx_Seminars_BackEnd_AbstractEventMailForm
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
    protected function getSubmitButtonLabel()
    {
        return $GLOBALS['LANG']->getLL('eventMailForm_confirmButton');
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
    public function getInitialValue($fieldName)
    {
        return parent::getInitialValue($fieldName);
    }

    /**
     * Sets the date format for the event.
     *
     * @return void
     */
    public function setDateFormat()
    {
        $this->getOldEvent()->setConfigurationValue('dateFormatYMD', '%d.%m.%Y');
    }

    /**
     * Sets an error message.
     *
     * @param string $fieldName
     *        the field name to set the error message for, must be "messageBody"
     *        or "subject"
     * @param string $message the error message to set, may be empty
     *
     * @return void
     */
    public function setErrorMessage($fieldName, $message)
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
    public function getErrorMessage($fieldName)
    {
        return parent::getErrorMessage($fieldName);
    }
}
