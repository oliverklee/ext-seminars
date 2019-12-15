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
* :ref:`registrationform_en`
* :ref:`notificationemail_en`
* :ref:`emailsalutation_en`
* :ref:`backendemail_en`

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
gets rendered to HTML. You may set custom markers or change exisitng values for
markers. See also :file:`Classes/Frontend/DefaultController.php` for available
properties and methods.

Register your class that implements :php:`\OliverKlee\Seminars\Hooks\Interfaces\SeminarSingleView`
like this in :file:`ext_localconf.php` of your extension:

.. code-block:: php

    use \OliverKlee\Seminars\Hooks\Interfaces\SeminarSingleView;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][SeminarSingleView:class][]
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

In these hooks you may set custom markers or change exisitng values for markers. See also
:file:`Classes/Frontend/DefaultController.php` for available properties and methods.

The hook to the seminar or registration bag building process allows for changing
the seminars / registrations shown in the list. You may add more filters or remove
existing ones. See also :file:`Classes/BagBuilder/AbstractBagBuilder.php`,
:file:`Classes/BagBuilder/Event.php` and :file:`Classes/BagBuilder/Registration.php`
for available properties and methods.

There are 7 types of lists your implementation must handle:

* topic list (`topic_list`)
* seminar list (`seminar_list`)
* my seminars (`my_events`)
* my vip seminars (`my_vip_events`)
* my entered events (`my_entered_events`)
* events next day (`events_next_day`)
* other dates (`other_dates`)

The last two list types (events next day and other dates) are part of the single
view, but handled as fully rendered seminar lists (including bag building).

Register your class that implements :php:`\OliverKlee\Seminars\Hooks\Interfaces\SeminarListView`
like this in :file:`ext_localconf.php` of your extension:

.. code-block:: php

    use \OliverKlee\Seminars\Hooks\Interfaces\SeminarListView;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][SeminarListView:class][]
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

.. _registrationform_en:

Hooks for the registration form
"""""""""""""""""""""""""""""""

There are 3 hooks into the registration form rendering:

* just before the registration form header is rendered to HTML
* just before the registration form is rendered to HTML
* just before the registration form footer is rendered to HTML

You may set custom markers or change exisitng values for markers in the header and footer hooks.
See also :file:`Classes/Frontend/DefaultController.php` for available properties and methods.

The registration form is rendered by the builder class in :file:`Classes/Frontend/RegistrationForm.php`.
It handles the registration or unregistration in 1 or 2 pages according to configuraton. Depending on
the page shown, the previously entered values and if it is an unregistration or not the values in the
form may be set or not. If you add custom fields to the form you also need to handle storage and
retrieval in DB for them according to the page / state of the (un)registration process as well as
validation via `mkforms`.

Register your class that implements :php:`\OliverKlee\Seminars\Hooks\Interfaces\SeminarRegistrationForm`
like this in :file:`ext_localconf.php` of your extension:

.. code-block:: php

    use \OliverKlee\Seminars\Hooks\Interfaces\SeminarRegistrationForm;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][SeminarRegistrationForm:class][]
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

Hooks to post process notification emails
""""""""""""""""""""""""""""""""""""""""""

To use this hook, please create a class that implements the interface
\\OliverKlee\\Seminars\\Hooks\\RegistrationEmailHookInterface. Then you need to add the following methods:

**Hook to post process the attendee email**

.. code-block:: php

    /**
     * @param \Tx_Oelib_Mail $mail
     * @param \Tx_Seminars_Model_Registration $registration
     *
     * @return void
     */
    public function postProcessAttendeeEmail(\Tx_Oelib_Mail $mail, \Tx_Seminars_Model_Registration $registration)
    {
    }

**Hook to post process the attendee email text**

.. code-block:: php

    /**
     * @param \Tx_Seminars_OldModel_Registration $registration
     * @param \Tx_Oelib_Template $emailTemplate
     *
     * @return void
     */
    public function postProcessAttendeeEmailText(\Tx_Seminars_OldModel_Registration $registration, \Tx_Oelib_Template $emailTemplate)
    {
    }

**Hook to post process the organizer email**

.. code-block:: php

    /**
     * @param \Tx_Oelib_Mail $mail
     * @param \Tx_Seminars_OldModel_Registration $registration
     *
     * @return void
     */
    public function postProcessOrganizerEmail(\Tx_Oelib_Mail $mail, \Tx_Seminars_OldModel_Registration $registration)
    {
    }

**Hook to post process additional emails**

.. code-block:: php

    /**
     * @param \Tx_Oelib_Mail $mail
     * @param \Tx_Seminars_OldModel_Registration $registration
     * @param string $emailReason see Tx_Seminars_Service_RegistrationManager::getReasonForNotification()
     *                            for information about possible values
     *
     * @return void
     */
    public function postProcessAdditionalEmail(
        \Tx_Oelib_Mail $mail,
        \Tx_Seminars_OldModel_Registration $registration,
        $emailReason = ''
    )
    {
    }


Your class then needs to be included and registered like in this
example:

.. code-block:: php

   // register my hook objects
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][] = \\MyVendor\\MyExt\\Hooks\\RegistrationEmailHook::class;


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
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['modifyEmailSalutation'][] = \\MyVendor\\MyExt\\Hooks\\ModifySalutationHook::class;


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
