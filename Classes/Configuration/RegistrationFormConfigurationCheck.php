<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Configuration;

/**
 * Configuration check for the registration form.
 */
class RegistrationFormConfigurationCheck extends AbstractFrontEndConfigurationCheck
{
    protected function checkAllConfigurationValues(): void
    {
        $this->checkCommonFrontEndSettings();

        $this->checkRegistrationFlag();
        if (!$this->isRegistrationEnabled()) {
            $explanation = 'You are using the registration page although online registration is disabled.
                This will break the registration page and the automatic configuration check.';
            $this->addWarningAndRequestCorrection('enableRegistration', $explanation);
        }

        $this->checkRegistrationEditorTemplateFile();

        $this->checkNumberOfClicksForRegistration();
        $this->checkNumberOfFirstRegistrationPage();
        $this->checkNumberOfLastRegistrationPage();
        $this->checkRegistrationPageNumbers();
        $this->checkGeneralPriceInSingle();
        $this->checkEventFieldsOnRegistrationPage();
        $this->checkShowRegistrationFields();
        $this->checkShowFeUserFieldsInRegistrationForm();
        $this->checkShowFeUserFieldsInRegistrationFormWithLabel();
        $this->checkThankYouAfterRegistrationPID();
        $this->checkSendParametersToThankYouAfterRegistrationPageUrl();
        $this->checkPageToShowAfterUnregistrationPID();
        $this->checkSendParametersToPageToShowAfterUnregistrationUrl();

        $this->checkCreateAdditionalAttendeesAsFrontEndUsers();
        if ($this->configuration->getAsBoolean('createAdditionalAttendeesAsFrontEndUsers')) {
            $this->checkSysFolderForAdditionalAttendeeUsersPID();
            $this->checkUserGroupUidsForAdditionalAttendeesFrontEndUsers();
        }

        $this->checkListPid();
        $this->checkLoginPid();
        $this->checkLogOutOneTimeAccountsAfterRegistration();
        $this->checkMyEventsPid();
        $this->checkDetailPid();
    }

    private function checkRegistrationEditorTemplateFile(): void
    {
        $this->checkFileExists(
            'registrationEditorTemplateFile',
            'This specifies the HTML template for the registration form.
            If this file is not available, the registration form cannot  be used.'
        );
    }

    private function checkNumberOfClicksForRegistration(): void
    {
        $this->checkIfNonNegativeIntegerOrEmpty(
            'numberOfClicksForRegistration',
            'This specifies the number of clicks for registration'
        );

        $this->checkIfIntegerInRange(
            'numberOfClicksForRegistration',
            2,
            3,
            'This specifies the number of clicks for registration.'
        );
    }

    private function checkNumberOfFirstRegistrationPage(): void
    {
        $this->checkIfPositiveInteger(
            'numberOfFirstRegistrationPage',
            'This value specifies the number of the first registration page  (for the <em>Step x of y</em> heading).
            If this value is not set correctly, the number of the current  page will not be displayed correctly.'
        );
    }

    private function checkNumberOfLastRegistrationPage(): void
    {
        $this->checkIfPositiveInteger(
            'numberOfLastRegistrationPage',
            'This value specifies the number of the last registration page (for the <em>Step x of y</em> heading).
            If this value is not set correctly, the number of the last page will not be displayed correctly.'
        );
    }

    private function checkRegistrationPageNumbers(): void
    {
        $clicks = $this->configuration->getAsInteger('numberOfClicksForRegistration');
        $firstPage = $this->configuration->getAsInteger('numberOfFirstRegistrationPage');
        $lastPage = $this->configuration->getAsInteger('numberOfLastRegistrationPage');
        $calculatedSteps = $lastPage - $firstPage + 2;

        if ($calculatedSteps !== $clicks) {
            $warning = 'The specified number of clicks does not correspond
                to the number of the first and last registration page.
                Please correct the values of <strong>numberOfClicksForRegistration</strong>
                <strong>numberOfFirstRegistrationPage</strong> or <strong>numberOfLastRegistrationPage</strong>.
                A not properly configured setting will lead to a misleading number of steps,
                shown on the registration page.';
            $this->addWarning($warning);
        }
    }

    private function checkEventFieldsOnRegistrationPage(): void
    {
        $this->checkIfMultiInSetNotEmpty(
            'eventFieldsOnRegistrationPage',
            'This value specifies which data fields of the selected event will be displayed on the registration page.
            Incorrect values will cause those fields to not get displayed.',
            ['uid', 'title', 'price_regular', 'price_special', 'vacancies']
        );
    }

    private function checkShowRegistrationFields(): void
    {
        $this->checkIfMultiInSetNotEmpty(
            'showRegistrationFields',
            'This value specifies which registration fields will be displayed on the registration page.
            Incorrect values will cause those fields to not get displayed.',
            [
                'step_counter',
                'price',
                'method_of_payment',
                'billing_address',
                'company',
                'gender',
                'name',
                'first_name',
                'last_name',
                'address',
                'zip',
                'city',
                'country',
                'telephone',
                'email',
                'interests',
                'expectations',
                'background_knowledge',
                'accommodation',
                'food',
                'known_from',
                'seats',
                'registered_themselves',
                'attendees_names',
                'kids',
                'lodgings',
                'foods',
                'checkboxes',
                'notes',
                'total_price',
                'feuser_data',
                'registration_data',
                'terms',
                'terms_2',
            ]
        );
    }

    private function checkShowFeUserFieldsInRegistrationForm(): void
    {
        $this->checkIfMultiInTableColumnsOrEmpty(
            'showFeUserFieldsInRegistrationFormMail',
            'These values specify the FE user fields to show in the registration form.
            A mistyped field name will cause the field to not get included.',
            'fe_users'
        );
    }

    private function checkShowFeUserFieldsInRegistrationFormWithLabel(): void
    {
        $this->checkIfMultiInTableColumnsOrEmpty(
            'showFeUserFieldsInRegistrationFormWithLabel',
            'These values specify the FE user labels to show in the registration form.
            A mistyped field name will cause the label to not get displayed.',
            'fe_users'
        );
    }

    private function checkThankYouAfterRegistrationPID(): void
    {
        $this->checkIfPositiveInteger(
            'thankYouAfterRegistrationPID',
            'This value specifies the page that will be displayed after a user signed up for an event.
            If this value is not set correctly, the user will see the list of events instead.'
        );
    }

    private function checkSendParametersToThankYouAfterRegistrationPageUrl(): void
    {
        $this->checkIfBoolean(
            'sendParametersToThankYouAfterRegistrationPageUrl',
            'This value specifies whether the sending of parameters to the thank you page after a registration
            should be enabled or not.
            If this value is incorrect the sending of parameters will not be enabled or disabled correctly.'
        );
    }

    private function checkPageToShowAfterUnregistrationPID(): void
    {
        $this->checkIfPositiveInteger(
            'pageToShowAfterUnregistrationPID',
            'This value specifies the page that will be displayed after a user has unregistered from an event.
            If this value is not set correctly, the user will see the list of events instead.'
        );
    }

    private function checkSendParametersToPageToShowAfterUnregistrationUrl(): void
    {
        $this->checkIfBoolean(
            'sendParametersToPageToShowAfterUnregistrationUrl',
            'This value specifies whether the sending of parameters to the page which is shown after an unregistration
            should be enabled or not.
            If this value is incorrect the sending of parameters will not be enabled or disabled correctly.'
        );
    }

    private function checkCreateAdditionalAttendeesAsFrontEndUsers(): void
    {
        $this->checkIfBoolean(
            'createAdditionalAttendeesAsFrontEndUsers',
            'This value specifies whether additional attendees will be  stored as FE user record.
            If this value is incorrect, those records will no be created,
            and the registration form will look different than intended.'
        );
    }

    private function checkSysFolderForAdditionalAttendeeUsersPID(): void
    {
        $this->checkIfPositiveInteger(
            'sysFolderForAdditionalAttendeeUsersPID',
            'This value specifies the system folder in which the FE user records
            for additional attendees will be stored.
            If this value is not set correctly, those records will be dumped in the TYPO3 root page.'
        );
    }

    private function checkUserGroupUidsForAdditionalAttendeesFrontEndUsers(): void
    {
        $this->checkIfIntegerListNotEmpty(
            'userGroupUidsForAdditionalAttendeesFrontEndUsers',
            'This value specifies the FE user groups for the FE users created for additional attendees.
            If this value is not set correctly, those FE users might not be able to log in.'
        );
    }

    /**
     * @deprecated #1947 will be removed in seminars 5.0
     */
    private function checkLogOutOneTimeAccountsAfterRegistration(): void
    {
        $this->checkIfBoolean(
            'logOutOneTimeAccountsAfterRegistration',
            'This value specifies whether one-time FE user accounts will automatically
            be logged out after registering for an event.
            If this value is incorrect, the automatic logout will not work.'
        );
    }

    private function checkMyEventsPid(): void
    {
        $this->checkIfPositiveInteger(
            'myEventsPID',
            'This value specifies the page that contains the <em>my events</em> list.
            If this value is not set correctly, the redirection to the <em>my events</em> list
            after canceling the unregistration process will not work correctly.'
        );
    }
}
