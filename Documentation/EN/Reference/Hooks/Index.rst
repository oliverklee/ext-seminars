.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. ==================================================
.. DEFINE SOME TEXTROLES
.. --------------------------------------------------
.. role::   underline
.. role::   typoscript(code)
.. role::   ts(typoscript)
   :class:  typoscript
.. role::   php(code)


Hooks
^^^^^

.. warning::
    Using hooks requires in-depth knowledge of PHP classes, implementation of
    interfaces and seminars object internals.

Hooks allow extending the functionality of seminars without using XCLASSes. There
are hooks for these parts of seminars:

* :ref:`singleview_en`
* :ref:`listview_en`
* :ref:`selectorwidget_en`
* :ref:`registrationform_en`
* :ref:`notificationemail_en`
* :ref:`emailsalutation_en`
* :ref:`datatimespan_en`
* :ref:`backendemail_en`
* :ref:`backendregistrationlistview_en`
* :ref:`registrationlistcsv_en`
* :ref:`datasanitization_en`

Please contact us if you need additional hooks.

.. important::
    seminars is undergoing a major rewriting to keep up with modern TYPO3 programming
    techniques. We try to keep changes as small as possible. Please inform yourself about changes
    by reading CHANGELOG.md, the DocBlocks of interfaces you implement and this
    chapter of the documentation before updating to a new seminars major version.

.. _singleview_en:

Hooks for the single view
"""""""""""""""""""""""""

.. important::
    Using :php:`\Tx_Seminars_Interface_Hook_EventSingleView` is deprecated since
    seminars 3. It will be removed in seminars 4. Please update to
    :php:`\OliverKlee\Seminars\Hooks\Interfaces\SeminarSingleView`.

There is a hook into the single view. It is executed just before the template
gets rendered to HTML. You may set custom markers or change existing values for
markers. See also :file:`Classes/Frontend/DefaultController.php` for available
properties and methods.

Register your class that implements :php:`\OliverKlee\Seminars\Hooks\Interfaces\SeminarSingleView`
like this in :file:`ext_localconf.php` of your extension:

.. code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][\OliverKlee\Seminars\Hooks\Interfaces\SeminarSingleView::class][]
        = \Tx_Seminarspaypal_Hooks_EventSingleView::class;

Implement the methods required by the interface:

.. code-block:: php

    use \OliverKlee\Seminars\Hooks\Interfaces\SeminarSingleView;

    class Tx_Seminarspaypal_Hooks_SingleView implements SeminarSingleView
    {
        /**
         * Modifies the seminar details view.
         *
         * This function will be called for all types of seminars (single events, topics, and dates).
         *
         * @param \Tx_Seminars_FrontEnd_DefaultController $controller the calling controller
         *
         * @return void
         */
        public function modifySingleView(\Tx_Seminars_FrontEnd_DefaultController $controller)
        {
            // Your code here
        }
    }

.. _listview_en:

Hooks for the list view
"""""""""""""""""""""""

.. important::
    Using :php:`\Tx_Seminars_Interface_Hook_EventListView` is deprecated since
    seminars 3. It will be removed in seminars 4. Please update to
    :php:`\OliverKlee\Seminars\Hooks\Interfaces\SeminarListView`.

There are 5 hooks into the list view(s). First hook is called just before the
seminar bag (the seminars to show in the list) or the registration bag (the
seminars a user is registered for) is build. It is always called, even when
there will be an empty list.

The other hooks are called during seminar list table creation:

* just before the table header is rendered to HTML
* just before a table row for a certain seminar or registration is rendered to HTML
* in case of a `my_event` list: right after the row hook mentioned above
* just before the table footer is rendered to HTML

In these hooks you may set custom markers or change existing values for markers. See also
:file:`Classes/Frontend/DefaultController.php` for available properties and methods.

The hook to the seminar or registration bag building process allows for changing
the seminars/registrations shown in the list. You may add more filters or remove
existing ones. See also :file:`Classes/BagBuilder/AbstractBagBuilder.php`,
:file:`Classes/BagBuilder/Event.php` and :file:`Classes/BagBuilder/Registration.php`
for available properties and methods.

There are 7 types of lists your implementation must handle:

* topic list (`topic_list`)
* seminar list (`seminar_list`)
* my seminars (`my_events`)
* my VIP seminars (`my_vip_events`)
* my entered events (`my_entered_events`)
* events next day (`events_next_day`)
* other dates (`other_dates`)

The last two list types (events next day and other dates) are part of the single
view, but handled as fully rendered seminar lists (including bag building).

Register your class that implements :php:`\OliverKlee\Seminars\Hooks\Interfaces\SeminarListView`
like this in :file:`ext_localconf.php` of your extension:

.. code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][\OliverKlee\Seminars\Hooks\Interfaces\SeminarListView::class][]
        = \Tx_Seminarspaypal_Hooks_ListView::class;

Implement the methods required by the interface:

.. code-block:: php

    use \OliverKlee\Seminars\Hooks\Interfaces\SeminarListView;

    class Tx_Seminarspaypal_Hooks_ListView implements SeminarListView
    {
        /**
         * Modifies the list view seminar bag builder (the item collection for a seminar list).
         *
         * Add or alter limitations for the selection of seminars to be shown in the
         * list.
         *
         * @see \OliverKlee\Seminars\BagBuilder\AbstractBagBuilder::getWhereClausePart()
         * @see \OliverKlee\Seminars\BagBuilder\AbstractBagBuilder::setWhereClausePart()
         *
         * This function will be called for these types of seminar lists: "topics", "seminars",
         * "my vip seminars", "my entered events", "events next day", "other dates".
         *
         * @param \Tx_Seminars_FrontEnd_DefaultController $controller the calling controller
         * @param \Tx_Seminars_BagBuilder_Event $builder the bag builder
         * @param string $whatToDisplay the flavor of list view: 'seminar_list', 'topic_list',
         *        'my_vip_events', 'my_entered_events', 'events_next_day' or 'other_dates'
         *
         * @return void
         */
        public function modifyEventBagBuilder(
            \Tx_Seminars_FrontEnd_DefaultController $controller,
            \Tx_Seminars_BagBuilder_Event $builder,
            string $whatToDisplay
        ) {
            // Your code here
        }

        /**
         * Modifies the list view registration bag builder (the item collection for a "my events" list).
         *
         * Add or alter limitations for the selection of seminars to be shown in the
         * list.
         *
         * @see \OliverKlee\Seminars\BagBuilder\AbstractBagBuilder::getWhereClausePart()
         * @see \OliverKlee\Seminars\BagBuilder\AbstractBagBuilder::setWhereClausePart()
         *
         * This function will be called for "my events" lists only.
         *
         * @param \Tx_Seminars_FrontEnd_DefaultController $controller the calling controller
         * @param \Tx_Seminars_BagBuilder_Registration $builder the bag builder
         * @param string $whatToDisplay the flavor of list view ('my_events' only?)
         *
         * @return void
         */
        public function modifyRegistrationBagBuilder(
            \Tx_Seminars_FrontEnd_DefaultController $controller,
            \Tx_Seminars_BagBuilder_Registration $builder,
            string $whatToDisplay
        ) {
            // Your code here
        }

        /**
         * Modifies the list view header row in a seminar list.
         *
         * This function will be called for all types of seminar lists ("topics",
         * "seminars", "my seminars", "my vip seminars", "my entered events",
         * "events next day", "other dates").
         *
         * @param \Tx_Seminars_FrontEnd_DefaultController $controller the calling controller
         *
         * @return void
         */
        public function modifyListHeader(\Tx_Seminars_FrontEnd_DefaultController $controller)
        {
            // Your code here
        }

        /**
         * Modifies a list row in a seminar list.
         *
         * This function will be called for all types of seminar lists ("topics",
         * "seminars", "my seminars", "my vip seminars", "my entered events",
         * "events next day", "other dates").
         *
         * @param \Tx_Seminars_FrontEnd_DefaultController $controller the calling controller
         *
         * @return void
         */
        public function modifyListRow(\Tx_Seminars_FrontEnd_DefaultController $controller)
        {
            // Your code here
        }

        /**
         * Modifies a list view row in a "my seminars" list.
         *
         * This function will be called for "my seminars" , "my vip seminars",
         * "my entered events" lists only.
         *
         * @param \Tx_Seminars_FrontEnd_DefaultController $controller the calling controller
         *
         * @return void
         */
        public function modifyMyEventsListRow(\Tx_Seminars_FrontEnd_DefaultController $controller)
        {
            // Your code here
        }

        /**
         * Modifies the list view footer in a seminars list.
         *
         * This function will be called for all types of seminar lists ("topics",
         * "seminars", "my seminars", "my vip seminars", "my entered events",
         * "events next day", "other dates").
         *
         * @param \Tx_Seminars_FrontEnd_DefaultController $controller the calling controller
         *
         * @return void
         */
        public function modifyListFooter(\Tx_Seminars_FrontEnd_DefaultController $controller)
        {
            // Your code here
        }
    }

.. _selectorwidget_en:

Hooks for the selector widget
"""""""""""""""""""""""""""""

There is a hook into the selector widget of the list view. If the selector widget
is activated, the hook is executed just before the template gets rendered to HTML.
You may set custom markers or change existing values for markers. See also
:file:`Classes/Frontend/SelectorWidget.php` for available properties and methods.

Register your class that implements :php:`\OliverKlee\Seminars\Hooks\Interfaces\SeminarSelectorWidget`
like this in :file:`ext_localconf.php` of your extension:

.. code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][\OliverKlee\Seminars\Hooks\Interfaces\SeminarSelectorWidget::class][]
        = \Tx_Seminarspaypal_Hooks_EventSelectorWidget::class;

Implement the methods required by the interface:

.. code-block:: php

    use \OliverKlee\Seminars\Hooks\Interfaces\SeminarSelectorWidget;

    class Tx_Seminarspaypal_Hooks_EventSelectorWidget implements SeminarSelectorWidget
    {
        /**
         * Modifies the seminar widget, just before the subpart is fetched.
         *
         * This function will be called for all types of seminar lists, if `displaySearchFormFields` is configured for it.
         *
         * @param \Tx_Seminars_FrontEnd_SelectorWidget $selectorWidget
         * @param \Tx_Seminars_Bag_Event $seminarBag the seminars used to create the selector widget
         *
         * @return void
         */
        public function modifySelectorWidget(
            \Tx_Seminars_FrontEnd_SelectorWidget $selectorWidget,
            \Tx_Seminars_Bag_Event $seminarBag
        ) {
            // Your code here
        }
    }

.. _registrationform_en:

Hooks for the registration form
"""""""""""""""""""""""""""""""

There are 3 hooks into the registration form rendering:

* just before the registration form header is rendered to HTML
* just before the registration form is rendered to HTML
* just before the registration form footer is rendered to HTML

You may set custom markers or change existing values for markers in the header and footer hooks.
See also :file:`Classes/Frontend/DefaultController.php` for available properties and methods.

The registration form is rendered by the builder class in :file:`Classes/Frontend/RegistrationForm.php`.
It handles the registration or unregistration in 1 or 2 pages according to configuraton. Depending on
the page shown, the previously entered values and if it is an unregistration or not the values in the
form may be set or not. If you add custom fields to the form you also need to handle storage and
retrieval in DB for them according to the page/state of the (un)registration process as well as
validation via `mkforms`.

Register your class that implements :php:`\OliverKlee\Seminars\Hooks\Interfaces\SeminarRegistrationForm`
like this in :file:`ext_localconf.php` of your extension:

.. code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][\OliverKlee\Seminars\Hooks\Interfaces\SeminarRegistrationForm::class][]
        = \Tx_Seminarspaypal_Hooks_SeminarRegistrationForm::class;

Implement the methods required by the interface:

.. code-block:: php

    use \OliverKlee\Seminars\Hooks\Interfaces\SeminarRegistrationForm;

    class Tx_Seminarspaypal_Hooks_SeminarRegistrationForm implements SeminarRegistrationForm
    {
        /**
         * Modifies the header of the seminar registration form.
         *
         * @param \Tx_Seminars_FrontEnd_DefaultController $controller the calling controller
         *
         * @return void
         */
        public function modifyRegistrationHeader(\Tx_Seminars_FrontEnd_DefaultController $controller)
        {
            // Your code here
        }

        /**
         * Modifies the seminar registration form.
         *
         * @param \Tx_Seminars_FrontEnd_DefaultController $controller the calling controller
         * @param \Tx_Seminars_FrontEnd_RegistrationForm $registrationEditor the registration form
         *
         * @return void
         */
        public function modifyRegistrationForm(
            \Tx_Seminars_FrontEnd_DefaultController $controller,
            \Tx_Seminars_FrontEnd_RegistrationForm $registrationEditor
        ) {
            // Your code here
        }

        /**
         * Modifies the footer of the seminar registration form.
         *
         * @param \Tx_Seminars_FrontEnd_DefaultController $controller the calling controller
         *
         * @return void
         */
        public function modifyRegistrationFooter(\Tx_Seminars_FrontEnd_DefaultController $controller)
        {
            // Your code here
        }
    }

.. _notificationemail_en:

Hooks for the registration notification emails
""""""""""""""""""""""""""""""""""""""""""""""

There are the following hooks into the registration notification emails:

* just before the attendee notification template is rendered to plain text
* just before the attendee notification template is rendered to HTML
* just before the attendee notification is sent
* just before the organizer notification is sent
* just before the additional organizer notifications are sent

You may set custom markers or change existing values for markers in the template hooks.
See also :file:`Classes/Model/Registration.php` for available properties and methods.
The plain text hook is always called, because a HTML email always contains a plain text version, too.
The HTML hook is called only if emails are sent as HTML.

With the other hooks you may modify the complete `Mail` object (e.g. sender or receiver addresses,
subject line or the complete body). See also :file:`Classes/Mail.php` of extension `oelib` for
available properties and methods.

Register your class that implements :php:`\OliverKlee\Seminars\Hooks\Interfaces\RegistrationEmail`
like this in :file:`ext_localconf.php` of your extension:

.. code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][\OliverKlee\Seminars\Hooks\Interfaces\RegistrationEmail::class][]
        = \Tx_Seminarspaypal_Hooks_RegistrationEmail::class;

Implement the methods required by the interface:

.. code-block:: php

    use \OliverKlee\Seminars\Hooks\Interfaces\RegistrationEmail;

    class Tx_Seminarspaypal_Hooks_RegistrationEmail implements RegistrationEmail
    {
        /**
         * Modifies the attendee "Thank you" email just before it is sent.
         *
         * You may modify the recipient or the sender as well as the subject and the body of the email.
         *
         * @param \Tx_Oelib_Mail $email
         * @param \Tx_Seminars_Model_Registration $registration
         * @param string $emailReason Possible values:
         *          - confirmation
         *          - confirmationOnUnregistration
         *          - confirmationOnRegistrationForQueue
         *          - confirmationOnQueueUpdate
         *
         * @return void
         */
        public function modifyAttendeeEmail(
            \Tx_Oelib_Mail $email,
            \Tx_Seminars_Model_Registration $registration,
            string $emailReason
        ) {
            // Your code here
        }

        /**
         * Modifies the attendee "Thank you" email body just before the subpart is rendered to plain text.
         *
         * This method is called for every confirmation email, even if HTML emails are configured.
         * The body of a HTML email always contains a plain text version, too.
         *
         * You may modify or set marker values in the template.
         *
         * @param \Tx_Oelib_Template $emailTemplate
         * @param \Tx_Seminars_Model_Registration $registration
         * @param string $emailReason Possible values:
         *          - confirmation
         *          - confirmationOnUnregistration
         *          - confirmationOnRegistrationForQueue
         *          - confirmationOnQueueUpdate
         *
         * @return void
         */
        public function modifyAttendeeEmailBodyPlainText(
            \Tx_Oelib_Template $emailTemplate,
            \Tx_Seminars_Model_Registration $registration,
            string $emailReason
        ) {
            // Your code here
        }

        /**
         * Modifies the attendee "Thank you" email body just before the subpart is rendered to HTML.
         *
         * This method is called only, if HTML emails are configured for confirmation emails.
         *
         * You may modify or set marker values in the template.
         *
         * @param \Tx_Oelib_Template $emailTemplate
         * @param \Tx_Seminars_Model_Registration $registration
         * @param string $emailReason Possible values:
         *          - confirmation
         *          - confirmationOnUnregistration
         *          - confirmationOnRegistrationForQueue
         *          - confirmationOnQueueUpdate
         *
         * @return void
         */
        public function modifyAttendeeEmailBodyHtml(
            \Tx_Oelib_Template $emailTemplate,
            \Tx_Seminars_Model_Registration $registration,
            string $emailReason
        ) {
            // Your code here
        }

        /**
         * Modifies the organizer notification email just before it is sent.
         *
         * You may modify the recipient or the sender as well as the subject and the body of the email.
         *
         * @param \Tx_Oelib_Mail $email
         * @param \Tx_Seminars_Model_Registration $registration
         * @param string $emailReason Possible values:
         *        - notification
         *        - notificationOnUnregistration
         *        - notificationOnRegistrationForQueue
         *        - notificationOnQueueUpdate
         *
         * @return void
         */
        public function modifyOrganizerEmail(
            \Tx_Oelib_Mail $email,
            \Tx_Seminars_Model_Registration $registration,
            string $emailReason
        ) {
            // Your code here
        }

        /**
         * Modifies the organizer additional notification email just before it is sent.
         *
         * You may modify the recipient or the sender as well as the subject and the body of the email.
         *
         * @param \Tx_Oelib_Mail $email
         * @param \Tx_Seminars_Model_Registration $registration
         * @param string $emailReason Possible values:
         *          - 'EnoughRegistrations' if the event has enough attendances
         *          - 'IsFull' if the event is fully booked
         *          see Tx_Seminars_Service_RegistrationManager::getReasonForNotification()
         *
         * @return void
         */
        public function modifyAdditionalEmail(
            \Tx_Oelib_Mail $email,
            \Tx_Seminars_Model_Registration $registration,
            string $emailReason
        ) {
            // Your code here
        }
    }

.. _emailsalutation_en:

Hooks for the salutation in all e-mails to the attendees
""""""""""""""""""""""""""""""""""""""""""""""""""""""""

It is also possible to extend the salutation used in the e-mails with
the following hook:

- modifySalutation for tx\_seminars\_EmailSaluation which is called just
  before the salutation is returned by getSalutation

To use this hook, you need to create a class with a method named
modifySalutation. The method in your class should expect two
parameters. The first one is a reference to an array with the following
structure:

array('dear' => String, 'title' => String, 'name' => String)

The second parameter is an user object \Tx_Seminars_Model_FrontEndUser.

Your class then needs to be included and registered like in this
example:

.. code-block:: php

   // register my hook objects
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['modifyEmailSalutation'][] = \MyVendor\MyExt\Hooks\ModifySalutationHook::class;


.. _datatimespan_en:

Hooks for the date and time span creation
"""""""""""""""""""""""""""""""""""""""""

There are hooks into the date and time span creation of the seminars. If at any place a date or time span
is required, these hooks are called to allow modification of the date or time span assembling. See also
:file:`Classes/OldModel/AbstractTimeSpan.php` for details about the default methods.

Register your class that implements :php:`\OliverKlee\Seminars\Hooks\Interfaces\DateTimeSpan`
like this in :file:`ext_localconf.php` of your extension:

.. code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][\OliverKlee\Seminars\Hooks\Interfaces\DateTimeSpan::class][]
        = \Tx_Seminarspaypal_Hooks_DateTimeSpan::class;

Implement the methods required by the interface:

.. code-block:: php

    use \OliverKlee\Seminars\Hooks\Interfaces\DateTimeSpan;

    class Tx_Seminarspaypal_Hooks_DateTimeSpan implements DateTimeSpan
    {
        /**
         * Modifies the date span string.
         *
         * This allows modifying the assembly of start and end date to the date span.
         * E.g., for Hungarian: '01.-03.01.2019' -> '2019.01.01.-03.'.
         *
         * The date format for the date parts are configured in TypoScript (`dateFormatYMD` etc.).
         * Get them from `$dateTimeSpan->getConfValueString('dateFormatYMD')` etc. The event
         * dates are also retrievable:
         * `$beginDateTime = $dateTimeSpan->getBeginDateAsTimestamp();`
         * `$endDateTime = $dateTimeSpan->getEndDateAsTimestamp();`
         *
         * @param string $dateSpan the date span produced by `AbstractTimeSpan::getDate()`
         * @param \Tx_Seminars_OldModel_AbstractTimeSpan $dateTimeSpan the date provider
         * @param string $dash the glue used by `AbstractTimeSpan::getDate()` (may be HTML encoded)
         *
         * @return string the modified date span to use
         */
        public function modifyDateSpan(
            string $dateSpan,
            \Tx_Seminars_OldModel_AbstractTimeSpan $dateTimeSpan,
            string $dash
        ): string
        {
            // Your code here
        }

        /**
         * Modifies the time span string.
         *
         * This allows modifying the assembly of start and end time to the time span.
         * E.g., for Hungarian: '9:00-10:30' -> '9:00tol 10:30ban'.
         *
         * The time format for the time parts is configured in TypoScript (`timeFormat`).
         * Get it from `$dateTimeSpan->getConfValueString('timeFormat')`. The event
         * times are also retrievable:
         * `$beginDateTime = $dateTimeSpan->getBeginDateAsTimestamp();`
         * `$endDateTime = $dateTimeSpan->getEndDateAsTimestamp();`
         *
         * @param string $timeSpan the time span produced by `AbstractTimeSpan::getTime()`
         * @param \Tx_Seminars_OldModel_AbstractTimeSpan $dateTimeSpan the date provider
         * @param string $dash the glue used by `AbstractTimeSpan::getTime()` (may be HTML encoded)
         *
         * @return string the modified time span to use
         */
        public function modifyTimeSpan(
            string $timeSpan,
            \Tx_Seminars_OldModel_AbstractTimeSpan $dateTimeSpan,
            string $dash
        ): string
        {
            // Your code here
        }
    }

.. _backendemail_en:

Hooks for the e-mails sent from the back-end module
"""""""""""""""""""""""""""""""""""""""""""""""""""

The hook classes need to be registered and written like this:

.. code-block:: php

   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['backEndModule'][]
         = \tx_seminarspaypal_Hooks_BackEndModule::class;

It's used like this:

.. code-block:: php

   class tx_seminarspaypal_Hooks_BackEndModule implements Tx_Seminars_Interface_Hook_BackEndModule {
         /**
        * Modifies the general e-mail sent via the back-end module.
        *
        * Note: This hook does not get called yet. It is just here so the interface
        * is finalized.
        *
        * @param Tx_Seminars_Model_Registration $registration
        *        the registration to which the e-mail refers
        * @param Tx_Oelib_Mail $eMail
        *        the e-mail that will be sent
        *
        * @return void
        */
         public function modifyGeneralEmail(Tx_Seminars_Model_Registration $registration, Tx_Oelib_Mail $eMail) {…}

         /**
        * Modifies the confirmation e-mail sent via the back-end module.
        *
        * @param Tx_Seminars_Model_Registration $registration
        *        the registration to which the e-mail refers
        * @param Tx_Oelib_Mail $eMail
        *        the e-mail that will be sent
        *
        * @return void
        */
         public function modifyConfirmEmail(Tx_Seminars_Model_Registration $registration, Tx_Oelib_Mail $eMail) {…}

         /**
        * Modifies the cancelation e-mail sent via the back-end module.
        *
        * Note: This hook does not get called yet. It is just here so the interface
        * is finalized.
        *
        * @param Tx_Seminars_Model_Registration $registration
        *        the registration to which the e-mail refers
        * @param Tx_Oelib_Mail $eMail
        *        the e-mail that will be sent
        *
        * @return void
        */
          public function modifyCancelEmail(Tx_Seminars_Model_Registration $registration, Tx_Oelib_Mail $eMail) {…}

.. _backendregistrationlistview_en:

Hooks for the backend registration list
"""""""""""""""""""""""""""""""""""""""

There are 3 hooks into the backend registration list. The hooks are called during
backend registration list creation:

* just before the table header is rendered to HTML
* just before a table row for a certain registration is rendered to HTML
* just before the table footer is rendered to HTML

In these hooks, you may set custom markers or change existing values for markers. For
available properties and methods, see :file:`Classes/Model/Registration.php` of `seminars`
and :file:`Classes/Template.php` of extension `oelib`.

There are 2 types of lists your implementation must handle:

* List of regular registrations (`REGULAR_REGISTRATIONS`)
* List of enqueued registrations (`REGISTRATIONS_ON_QUEUE`)

Register your class that implements :php:`\OliverKlee\Seminars\Hooks\Interfaces\BackendRegistrationListView`
like this in :file:`ext_localconf.php` of your extension:

.. code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][\OliverKlee\Seminars\Hooks\Interfaces\BackendRegistrationListView::class][]
        = \Tx_Seminarspaypal_Hooks_BackendRegistrationListView::class;

Implement the methods required by the interface:

.. code-block:: php

    use \OliverKlee\Seminars\Hooks\Interfaces\BackendRegistrationListView;

    class Tx_Seminarspaypal_Hooks_BackendRegistrationListView implements BackendRegistrationListView
    {
        /**
         * Modifies the list row template content just before it is rendered to HTML.
         *
         * This method is called once per list row, but the row may appear in the list of regular registrations or the
         * list of registrations on queue. Check $registrationsToShow (can be one of
         * `\OliverKlee\Seminars\BackEnd\RegistrationsList::REGISTRATIONS_ON_QUEUE`
         * and `\OliverKlee\Seminars\BackEnd\RegistrationsList::REGULAR_REGISTRATIONS`) to distinguish.
         *
         * @param \Tx_Seminars_Model_Registration $registration
         *        the registration the row is made from
         * @param \Tx_Oelib_Template $template
         *        the template that will be used to create the registration list
         * @param int $registrationsToShow
         *        the type of registration shown in the list
         *
         * @return void
         */
        public function modifyListRow(
            \Tx_Seminars_Model_Registration $registration,
            \Tx_Oelib_Template $template,
            int $registrationsToShow
        ) {
            // Your code here
        }

        /**
         * Modifies the list heading template content just before it is rendered to HTML.
         *
         * This method is called twice per list: First for the list of regular registrations, then for the list of
         * registrations on queue. Check $registrationsToShow (can be one of
         * `\OliverKlee\Seminars\BackEnd\RegistrationsList::REGISTRATIONS_ON_QUEUE`
         * and `\OliverKlee\Seminars\BackEnd\RegistrationsList::REGULAR_REGISTRATIONS`) to distinguish.
         *
         * @param \Tx_Seminars_Bag_Registration $registrationBag
         *        the registrationBag the heading is made for
         * @param \Tx_Oelib_Template $template
         *        the template that will be used to create the registration list
         * @param int $registrationsToShow
         *        the type of registration shown in the list
         *
         * @return void
         */
        public function modifyListHeader(
            \Tx_Seminars_Bag_Registration $registrationBag,
            \Tx_Oelib_Template $template,
            int $registrationsToShow
        ) {
            // Your code here
        }

        /**
         * Modifies the complete list template content just before it is rendered to HTML.
         *
         * This method is called twice per list: First for the list of regular registrations, then for the list of
         * registrations on queue. Check $registrationsToShow (can be one of
         * `\OliverKlee\Seminars\BackEnd\RegistrationsList::REGISTRATIONS_ON_QUEUE`
         * and `\OliverKlee\Seminars\BackEnd\RegistrationsList::REGULAR_REGISTRATIONS`) to distinguish.
         *
         * @param \Tx_Seminars_Bag_Registration $registrationBag
         *        the registrationBag the table is made for
         * @param \Tx_Oelib_Template $template
         *        the template that will be used to create the registration list
         * @param int $registrationsToShow
         *        the type of registration shown in the list
         *
         * @return void
         */
        public function modifyList(
            \Tx_Seminars_Bag_Registration $registrationBag,
            \Tx_Oelib_Template $template,
            int $registrationsToShow
        ) {
            // Your code here
        }
    }

.. _registrationlistcsv_en:

Hooks for the CSV generation of registration lists
""""""""""""""""""""""""""""""""""""""""""""""""""

There is a hook into the CSV generation of registration lists to modify the generated CSV text.

Register your class that implements :php:`\OliverKlee\Seminars\Hooks\Interfaces\RegistrationListCsv`
like this in :file:`ext_localconf.php` of your extension:

.. code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][\OliverKlee\Seminars\Hooks\Interfaces\RegistrationListCsv::class][]
        = \Tx_Seminarspaypal_Hooks_RegistrationListCsv::class;

Implement the methods required by the interface:

.. code-block:: php

    use \OliverKlee\Seminars\Hooks\Interfaces\RegistrationListCsv;

    class Tx_Seminarspaypal_Hooks_RegistrationListCsv implements RegistrationListCsv
    {
        /**
         * Modifies the rendered CSV string.
         *
         * This allows modifying the complete CSV text right before it is delivered.
         *
         * @param string $csv the CSV text produced by `\Tx_Seminars_Csv_AbstractRegistrationListView::render()`
         * @param \Tx_Seminars_Csv_AbstractRegistrationListView $registrationList the CSV data provider
         *
         * @return string the modified CSV text to use
         */
        public function modifyCsv(string $csv, \Tx_Seminars_Csv_AbstractRegistrationListView $registrationList): string
        {
            // Your code here
        }
    }

.. _datasanitization_en:

Hooks for the data sanitization on TCE validation
"""""""""""""""""""""""""""""""""""""""""""""""""

There is a hook into the data handler to additionaly manipulate `seminars` FlexForm data during
TCE validation (just before storing the data). You may apply additional constraints and dynamically
adjust values (e.g. registration deadline = begin date - 14 days).

TCE validation is a TYPO3-defined process. `seminars` gets the form values from the content element's
FlexForm and stores required changes of the values into the database.

Register your class that implements :php:`\OliverKlee\Seminars\Hooks\Interfaces\DataSanitization`
like this in :file:`ext_localconf.php` of your extension:

.. code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][\OliverKlee\Seminars\Hooks\Interfaces\DataSanitization::class][]
        = \Tx_Seminarspaypal_Hooks_DataSanitization::class;

Implement the methods required by the interface:

.. code-block:: php

    use \OliverKlee\Seminars\Hooks\Interfaces\DataSanitization;

    class Tx_Seminarspaypal_Hooks_DataSanitization implements DataSanitization
    {
        /**
         * Sanitizes event data values.
         *
         * The TCE form event values need to be sanitized when storing them into the
         * database. Check the values with additional constraints and provide the modified
         * values to use back in a returned array.
         *
         * @param int $uid
         * @param mixed[] $data the events data as stored in database
         *
         * @return mixed[] the data to change, [] for no changes
         */
        public function sanitizeEventData(int $uid, array $data): array
        {
            // Your code here
        }
    }
