<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\FrontEnd;

use OliverKlee\Oelib\Authentication\FrontEndLoginManager;
use OliverKlee\Oelib\Http\HeaderProxyFactory;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\Mapper\FrontEndUserMapper;
use OliverKlee\Seminars\Model\Event;
use OliverKlee\Seminars\Model\FrontEndUser;
use OliverKlee\Seminars\OldModel\LegacyEvent;
use OliverKlee\Seminars\OldModel\LegacyRegistration;
use OliverKlee\Seminars\Service\RegistrationManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class is a controller which allows to create registrations on the FE.
 *
 * @deprecated #1545 will be removed in seminars 5.0
 */
class RegistrationForm extends AbstractEditor
{
    /**
     * the same as the class name
     *
     * @var string
     */
    public $prefixId = 'tx_seminars_registration_editor';

    /**
     * the number of the current page of the form (starting with 0 for the first page)
     *
     * @var int
     */
    public $currentPageNumber = 0;

    /**
     * @var LegacyEvent seminar object
     */
    private $seminar;

    /**
     * @var LegacyRegistration|null
     */
    protected $registration;

    /**
     * @param string $action action for which to create the form, must be either "register" or "unregister",
     *        must not be empty
     */
    public function setAction(string $action): void
    {
        $this->initializeAction($action);
    }

    /**
     * Sets the seminar for which to create the form.
     *
     * @param LegacyEvent $event the event for which to create the form
     */
    public function setSeminar(LegacyEvent $event): void
    {
        $this->seminar = $event;
    }

    /**
     * Returns the configured seminar object.
     *
     * @return LegacyEvent the seminar instance
     *
     * @throws \BadMethodCallException if no seminar has been set yet
     */
    public function getSeminar(): LegacyEvent
    {
        if ($this->seminar === null) {
            throw new \BadMethodCallException(
                'Please set a proper seminar object via $this->setSeminar().',
                1333293187
            );
        }

        return $this->seminar;
    }

    /**
     * Returns the event for this registration form.
     */
    public function getEvent(): Event
    {
        return MapperRegistry::get(EventMapper::class)->find($this->getSeminar()->getUid());
    }

    /**
     * Sets the registration for which to create the unregistration form.
     *
     * @param LegacyRegistration $registration the registration to use
     */
    public function setRegistration(LegacyRegistration $registration): void
    {
        $this->registration = $registration;
    }

    /**
     * Returns the current registration object.
     */
    private function getRegistration(): ?LegacyRegistration
    {
        return $this->registration;
    }

    /**
     * Sets the form configuration to use.
     *
     * @param string $action action to perform, may be either "register" or "unregister", must not be empty
     */
    protected function initializeAction(string $action = 'register'): void
    {
        switch ($action) {
            case 'unregister':
                $formConfiguration = (array)$this->conf['form.']['unregistration.'];
                break;
            case 'register':
                // The fall-through is intended.
            default:
                // The current page number will be 1 if a 3-click registration
                // is configured and the first page was submitted successfully.
                // It will be 2 for a 3-click registration and the second page
                // submitted successfully. It will also be 2 for a 2-click
                // registration and the first page submitted successfully.
                // Note that to display the second page, this function is called
                // two times in a row if the current page number is higher than
                // zero. It is only the second page, that can process the
                // registration.
                if (($this->currentPageNumber == 1) || ($this->currentPageNumber == 2)) {
                    $formConfiguration = (array)$this->conf['form.']['registration.']['step2.'];
                } else {
                    $formConfiguration = (array)$this->conf['form.']['registration.']['step1.'];
                }
        }

        $this->setFormConfiguration($formConfiguration);
    }

    /**
     * Creates the HTML output.
     *
     * @return string HTML of the create/edit form
     */
    public function render(): string
    {
        $rawForm = parent::render();
        // For the confirmation page, we need to reload the whole thing. Yet,
        // the previous rendering still is necessary for processing the data.
        if ($this->currentPageNumber > 0) {
            $this->discardRenderedForm();
            $this->initializeAction();
            // This will produce a new form to which no data can be provided.
            $rawForm = $this->makeFormCreator()->render();
        }

        // Remove empty label tags that have been created due to a bug in FORMidable.
        $rawForm = preg_replace('/<label[^>]*><\\/label>/', '', $rawForm);
        $this->processTemplate($rawForm);
        $this->setLabels();

        return $this->getSubpart();
    }

    /**
     * Discards the rendered FORMIdable form from the page, including any header data.
     */
    private function discardRenderedForm(): void
    {
        $frontEndController = $this->getFrontEndController();
        // A mayday would be returned without unsetting the form ID.
        unset(
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ameos_formidable']['context']
            ['forms']['tx_seminars_pi1_registration_editor']
        );
        if (!\is_array($frontEndController->additionalHeaderData)) {
            return;
        }

        foreach ($frontEndController->additionalHeaderData as $key => $content) {
            if (\strpos((string)$content, 'FORMIDABLE:') !== false) {
                unset($frontEndController->additionalHeaderData[$key]);
            }
        }
    }

    /**
     * Selects the confirmation page (the second step of the registration form) for display. This affects $this->render().
     *
     * This method is used only in the unit tests.
     *
     * @param array $parameters the entered form data with the field names as array keys (including the submit button)
     */
    public function setPage(array $parameters): void
    {
        $this->currentPageNumber = $parameters['next_page'];
    }

    /**
     * Checks whether the "travelling terms" checkbox (ie. the second "terms" checkbox) is enabled in the event record
     * *and* via TS setup.
     */
    public function isTerms2Enabled(): bool
    {
        return $this->getSeminar()->hasTerms2();
    }

    /**
     * Gets the URL of the page that should be displayed after a user has signed up for an event,
     * but only if the form has been submitted from stage 2 (the confirmation page).
     *
     * @return string complete URL of the FE page with a message
     */
    public function getThankYouAfterRegistrationUrl(): string
    {
        $pageUid = $this->getConfValueInteger('thankYouAfterRegistrationPID', 's_registration');
        $sendParameters = $this->getConfValueBoolean(
            'sendParametersToThankYouAfterRegistrationPageUrl',
            's_registration'
        );

        return $this->createUrlForRedirection($pageUid, $sendParameters);
    }

    /**
     * Gets the URL of the page that should be displayed after a user has unregistered from an event.
     *
     * @return string complete URL of the FE page with a message
     */
    public function getPageToShowAfterUnregistrationUrl(): string
    {
        $pageUid = $this->getConfValueInteger('pageToShowAfterUnregistrationPID', 's_registration');
        $sendParameters = $this->getConfValueBoolean(
            'sendParametersToPageToShowAfterUnregistrationUrl',
            's_registration'
        );

        return $this->createUrlForRedirection($pageUid, $sendParameters);
    }

    /**
     * Creates a URL for redirection.
     *
     * @param bool $sendParameters whether GET parameters should be added to the URL
     *
     * @return string complete URL of the FE page with a message
     */
    private function createUrlForRedirection(int $pageUid, bool $sendParameters = true): string
    {
        // On freshly updated sites, the configuration value might not be set yet.
        // To avoid breaking the site, we use the event list in this case.
        if ($pageUid === 0) {
            $pageUid = $this->getConfValueInteger('listPID');
        }

        $linkConfiguration = ['parameter' => $pageUid];
        if ($sendParameters) {
            $linkConfiguration['additionalParams'] = GeneralUtility::implodeArrayForUrl(
                'tx_seminars_pi1',
                ['showUid' => $this->getSeminar()->getUid()],
                '',
                false,
                true
            );
        }

        return GeneralUtility::locationHeaderUrl($this->cObj->typoLink_URL($linkConfiguration));
    }

    /**
     * Checks whether the methods of payment should be displayed at all,
     * i.e., whether they are enable in the setup and the current event actually
     * has any payment methods assigned and has at least one price.
     *
     * @return bool TRUE if the payment methods should be displayed, FALSE otherwise
     */
    public function showMethodsOfPayment(): bool
    {
        return $this->getSeminar()->hasPaymentMethods();
    }

    /**
     * Ensures that the parameter is an array. If it is no array yet, it will be changed to an empty array.
     *
     * @param mixed $data variable that should be ensured to be an array
     */
    protected function ensureArray(&$data): void
    {
        if (!is_array($data)) {
            $data = [];
        }
    }

    /**
     * Checks whether our current event has any option checkboxes AND the
     * checkboxes should be displayed at all.
     *
     * @return bool TRUE if we have a non-empty list of checkboxes AND this list should be displayed, FALSE otherwise
     */
    public function hasCheckboxes(): bool
    {
        return $this->getSeminar()->hasCheckboxes();
    }

    /**
     * Checks whether our current event has any lodging options and the
     * lodging options should be displayed at all.
     *
     * @return bool TRUE if we have a non-empty list of lodging options and this list should be displayed, FALSE otherwise
     */
    public function hasLodgings(): bool
    {
        return $this->getSeminar()->hasLodgings();
    }

    /**
     * Checks whether our current event has any food options and the food
     * options should be displayed at all.
     *
     * @return bool TRUE if we have a non-empty list of food options and this list should be displayed, FALSE otherwise
     */
    public function hasFoods(): bool
    {
        return $this->getSeminar()->hasFoods();
    }

    /**
     * @throws \BadMethodCallException if this method is called without a logged-in FE user
     */
    protected function getLoggedInUser(): FrontEndUser
    {
        $userUid = FrontEndLoginManager::getInstance()->getLoggedInUserUid();
        $user = $userUid > 0 ? MapperRegistry::get(FrontEndUserMapper::class)->find($userUid) : null;
        if (!$user instanceof FrontEndUser) {
            throw new \BadMethodCallException('No user logged in.', 1633436053);
        }

        return $user;
    }

    /**
     * Processes the registration that should be removed.
     */
    public function processUnregistration(): void
    {
        /** @var \formidable_mainrenderlet $cancelButtonRenderlet */
        $cancelButtonRenderlet = $this->getFormCreator()->aORenderlets['button_cancel'];
        if ($cancelButtonRenderlet->hasThrown('click')) {
            $redirectUrl = GeneralUtility::locationHeaderUrl(
                $this->pi_getPageLink(
                    $this->getConfValueInteger(
                        'myEventsPID'
                    )
                )
            );
            HeaderProxyFactory::getInstance()->getHeaderProxy()->addHeader('Location:' . $redirectUrl);
            exit;
        }

        $this->getRegistrationManager()->removeRegistration($this->getRegistration()->getUid(), $this);
    }

    private function getRegistrationManager(): RegistrationManager
    {
        return RegistrationManager::getInstance();
    }
}
