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
    Um Hooks nutzen zu können, benötigen Sie tiefgehende Kenntnisse über PHP-Klassen,
    die Implementation von Interfaces und der seminars-Objekt-Interna.

Mit Hilfe von Hooks erweitern Sie die Funktionalität von seminars, ohne XCLASSen zu nutzen.
Es gibt Hooks in diese Teile von seminars:

* :ref:`singleview_de`
* :ref:`listview_de`
* :ref:`selectorwidget_de`
* :ref:`registrationform_de`
* :ref:`notificationemail_de`
* :ref:`emailsalutation_de`
* :ref:`backendemail_de`

Bitte nehmen Sie Kontakt zu uns auf, wenn Sie weitere Hooks benötigen.

.. important::
    seminars wird derzeit grundlegend überarbeitet, um es an die Weiterentwicklung von
    TYPO3-Programmiertechniken anzupassen. Wir bemühen uns, die Änderungen so gering wie möglich zu
    halten. Informieren Sie sich über Änderungen in CHANGELOG.md, den DocBlocks der Interfaces,
    die Sie implementieren, und in diesem Kapitel der Dokumentation, bevor Sie auf eine neue
    Haupt-Version updaten.

.. _singleview_de:

Hooks zur Einzelansicht
"""""""""""""""""""""""

.. important::
    Die Nutzung von :php:`\Tx_Seminars_Interface_Hook_EventSingleView` ist veraltet seit
    seminars 3. Es wird in seminars 4 entfernt werden. Bitte aktualisieren Sie auf
    :php:`\OliverKlee\Seminars\Hooks\Interfaces\SeminarSingleView`.

Es gibt einen Hook in die Einzelansicht. Er wird aufgerufen, bevor das Template zu HTML
umgewandelt wird. Sie können damit eigene Marker ausfüllen oder bestehende Marker-Werte 
verändern. Für Details zu Eigenschaften und Methoden siehe :file:`Classes/Frontend/DefaultController.php`.

Ihre Klasse, die :php:`\OliverKlee\Seminars\Hooks\Interfaces\SeminarSingleView` implementiert,
machen Sie seminars in :file:`ext_localconf.php` Ihrer Extension bekannt:

.. code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][\OliverKlee\Seminars\Hooks\Interfaces\SeminarSingleView:class][]
        = \Tx_Seminarspaypal_Hooks_EventSingleView::class;

Implementieren Sie die benötigten Methoden gemäß dem Interface:

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
            // Hier Ihr Code
        }
    }

.. _listview_de:

Hooks zur Listenansicht
"""""""""""""""""""""""

.. important::
    Die Nutzung von :php:`\Tx_Seminars_Interface_Hook_EventListView` ist veraltet seit
    seminars 3. Es wird in seminars 4 entfernt werden. Bitte aktualisieren Sie auf
    :php:`\OliverKlee\Seminars\Hooks\Interfaces\SeminarListView`.

Es gibt 5 Hooks in die Listenansicht(en). Der erste Hook wird vor der Erstellung der
Seminar-Bag (die in der Liste auszugebenden Seminare) oder der Registrierungen-Bag (die
Seminare, für die sich ein Benutzer angemeldet hat) aufgerufen. Der Hook wird immer aufgerufen,
auch wenn die Liste leer sein wird.

Die übrigen Hooks werden während der Erstellung der Seminar-Listen-Tabelle aufgerufen:

* Bevor der Tabellenkopf in HTML umgewandelt wird
* Bevor eine Tabellenzeile zu einem bestimmten Seminar oder einer Registrierung in HTML umgewandelt wird
* Im Fall der `my_event` Liste: direkt nach dem oben genannten Zeilen-Hook
* Bevor der Tabellenfuß in HTML umgewandelt wird

In diesen Hooks können Sie eigene Marker ausfüllen oder vorhandene Marker-Werte ändern. Zu
verfügbaren Eigenschaften und Methoden siehe :file:`Classes/Frontend/DefaultController.php`.

Der Hook in die Erstellung der Seminar- oder Registrierungen-Bag erlaubt es, die für die Liste
ausgewählten Seminare bzw. Reqistrierungen zu beeinflussen. Sie können neue Filter hinzufügen oder
bestehende Filter entfernen. Details dazu finden Sie in :file:`Classes/BagBuilder/AbstractBagBuilder.php`,
:file:`Classes/BagBuilder/Event.php` und :file:`Classes/BagBuilder/Registration.php`.

Sie müssen 7 Listenarten bei Ihrer Implementation beachten:

* Themen Liste (`topic_list`)
* Termin Liste (`seminar_list`)
* Meine Seminare (`my_events`)
* Meine VIP-Seminare (`my_vip_events`)
* Von mir angelegte Seminare (`my_entered_events`)
* Termine am nächsten Tag (`events_next_day`)
* Andere Termine (`other_dates`)

Die letzten beiden Listenarten (Termine am nächsten Tag und Andere Termine) gehören zur Einzelansicht,
werden aber als komplette Listenansicht behandelt (inklusive Erstellung der Seminar-Bag).

Ihre Klasse, die :php:`\OliverKlee\Seminars\Hooks\Interfaces\SeminarListView` implementiert,
machen Sie seminars in :file:`ext_localconf.php` Ihrer Extension bekannt:

.. code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][\OliverKlee\Seminars\Hooks\Interfaces\SeminarListView:class][]
        = \Tx_Seminarspaypal_Hooks_ListView::class;

Implementieren Sie die benötigten Methoden gemäß dem Interface:

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

.. _selectorwidget_de:

Hooks zum Selector Widget
"""""""""""""""""""""""""

Es gibt einen Hook in das Selector Widget der Listenansicht. Er wird aufgerufen,
bevor das Template zu HTML umgewandelt wird, wenn in der Listenansicht das
Selector Widget aktiviert ist. Sie können damit eigene Marker ausfüllen oder
bestehende Marker-Werte verändern. Für Details zu Eigenschaften und Methoden
siehe :file:`Classes/Frontend/SelectorWidget.php`.

Ihre Klasse, die :php:`\OliverKlee\Seminars\Hooks\Interfaces\SeminarSelectorWidget` implementiert,
machen Sie seminars in :file:`ext_localconf.php` Ihrer Extension bekannt:

.. code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][\OliverKlee\Seminars\Hooks\Interfaces\SeminarSelectorWidget:class][]
        = \Tx_Seminarspaypal_Hooks_EventSelectorWidget::class;

Implementieren Sie die benötigten Methoden gemäß dem Interface:

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
            // Hier Ihr Code
        }
    }

.. _registrationform_de:

Hooks zum Registrierungsformular
""""""""""""""""""""""""""""""""

Es gibt 3 Hooks in das Registrierungsformular:

* Bevor der Formularkopf in HTML umgewandelt wird
* Bevor das Formular selbst in HTML umgewandelt wird
* Bevor der Formularfuß in HTML umgewandelt wird

Im Formularkopf und -fuß können Sie eigene Marker ausfüllen oder vorhandene Marker-Werte ändern. Zu
verfügbaren Eigenschaften und Methoden dafür siehe :file:`Classes/Frontend/DefaultController.php`.

Das Registrierungsformular wird von einer eigenen PHP-Klasse erstellt: :file:`Classes/Frontend/RegistrationForm.php`.
Es bearbeitet sowohl Registrierung alsauch Abmeldung auf einer oder 2 Formularseiten gemäß der Konfiguration.
Je nachdem, auf welcher Seite man sich befindet bzw. ob es sich um eine Abmeldung handelt, sind bereits Formularwerte
eingetragen oder nicht. Wenn Sie eigene Formularfelder hinzufügen wollen, müssen Sie die Speicherung in die DB und das
Ausfüllen passend zu Seite und Status innerhalb des Registrierungs- bzw. Abmeldeprozesses behandeln. Eine Validierung
erfolgt bei Bedarf durch `mkforms` (nicht über diese Hooks).

Ihre Klasse, die :php:`\OliverKlee\Seminars\Hooks\Interfaces\SeminarRegistrationForm` implementiert,
machen Sie seminars in :file:`ext_localconf.php` Ihrer Extension bekannt:

.. code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][\OliverKlee\Seminars\Hooks\Interfaces\SeminarRegistrationForm:class][]
        = \Tx_Seminarspaypal_Hooks_SeminarRegistrationForm::class;

Implementieren Sie die benötigten Methoden gemäß dem Interface:

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

.. _notificationemail_de:

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


.. _emailsalutation_de:

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


.. _backendemail_de:

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
