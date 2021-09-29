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
* :ref:`datatimespan_de`
* :ref:`backendemail_de`
* :ref:`backendregistrationlistview_de`
* :ref:`registrationlistcsv_de`
* :ref:`datasanitization_de`

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

Es gibt einen Hook in die Einzelansicht. Er wird aufgerufen, bevor das Template zu HTML
umgewandelt wird. Sie können damit eigene Marker ausfüllen oder bestehende Marker-Werte
verändern. Für Details zu Eigenschaften und Methoden siehe :file:`Classes/Frontend/DefaultController.php`.

Ihre Klasse, die :php:`\OliverKlee\Seminars\Hooks\Interfaces\SeminarSingleView` implementiert,
machen Sie seminars in :file:`ext_localconf.php` Ihrer Extension bekannt:

.. code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][\OliverKlee\Seminars\Hooks\Interfaces\SeminarSingleView::class][]
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
         */
        public function modifySingleView(\Tx_Seminars_FrontEnd_DefaultController $controller): void
        {
            // Hier Ihr Code
        }
    }

.. _listview_de:

Hooks zur Listenansicht
"""""""""""""""""""""""

Es gibt 4 Hooks in die Listenansicht(en). Der erste Hook wird vor der Erstellung der
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

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][\OliverKlee\Seminars\Hooks\Interfaces\SeminarListView::class][]
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
         */
        public function modifyEventBagBuilder(
            \Tx_Seminars_FrontEnd_DefaultController $controller,
            \Tx_Seminars_BagBuilder_Event $builder,
            string $whatToDisplay
        ): void {
            // Hier Ihr Code
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
         */
        public function modifyRegistrationBagBuilder(
            \Tx_Seminars_FrontEnd_DefaultController $controller,
            \Tx_Seminars_BagBuilder_Registration $builder,
            string $whatToDisplay
        ): void {
            // Hier Ihr Code
        }

        /**
         * Modifies the list view header row in a seminar list.
         *
         * This function will be called for all types of seminar lists ("topics",
         * "seminars", "my seminars", "my vip seminars", "my entered events",
         * "events next day", "other dates").
         *
         * @param \Tx_Seminars_FrontEnd_DefaultController $controller the calling controller
         */
        public function modifyListHeader(\Tx_Seminars_FrontEnd_DefaultController $controller): void
        {
            // Hier Ihr Code
        }

        /**
         * Modifies a list row in a seminar list.
         *
         * This function will be called for all types of seminar lists ("topics",
         * "seminars", "my seminars", "my vip seminars", "my entered events",
         * "events next day", "other dates").
         *
         * @param \Tx_Seminars_FrontEnd_DefaultController $controller the calling controller
         */
        public function modifyListRow(\Tx_Seminars_FrontEnd_DefaultController $controller): void
        {
            // Hier Ihr Code
        }

        /**
         * Modifies a list view row in a "my seminars" list.
         *
         * This function will be called for "my seminars" , "my vip seminars",
         * "my entered events" lists only.
         *
         * @param \Tx_Seminars_FrontEnd_DefaultController $controller the calling controller
         */
        public function modifyMyEventsListRow(\Tx_Seminars_FrontEnd_DefaultController $controller): void
        {
            // Hier Ihr Code
        }

        /**
         * Modifies the list view footer in a seminars list.
         *
         * This function will be called for all types of seminar lists ("topics",
         * "seminars", "my seminars", "my vip seminars", "my entered events",
         * "events next day", "other dates").
         *
         * @param \Tx_Seminars_FrontEnd_DefaultController $controller the calling controller
         */
        public function modifyListFooter(\Tx_Seminars_FrontEnd_DefaultController $controller): void
        {
            // Hier Ihr Code
        }
    }

.. _selectorwidget_de:

Hooks zum Selector-Widget
"""""""""""""""""""""""""

Es gibt einen Hook in das Selector-Widget der Listenansicht. Er wird aufgerufen,
bevor das Template zu HTML umgewandelt wird, wenn in der Listenansicht das
Selector-Widget aktiviert ist. Sie können damit eigene Marker befüllen oder
bestehende Marker-Werte verändern. Für Details zu Eigenschaften und Methoden
siehe :file:`Classes/Frontend/SelectorWidget.php`.

Ihre Klasse, die :php:`\OliverKlee\Seminars\Hooks\Interfaces\SeminarSelectorWidget` implementiert,
machen Sie seminars in :file:`ext_localconf.php` Ihrer Extension bekannt:

.. code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][\OliverKlee\Seminars\Hooks\Interfaces\SeminarSelectorWidget::class][]
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
         */
        public function modifySelectorWidget(
            \Tx_Seminars_FrontEnd_SelectorWidget $selectorWidget,
            \Tx_Seminars_Bag_Event $seminarBag
        ): void {
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

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][\OliverKlee\Seminars\Hooks\Interfaces\SeminarRegistrationForm::class][]
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
         */
        public function modifyRegistrationHeader(\Tx_Seminars_FrontEnd_DefaultController $controller): void
        {
            // Hier Ihr Code
        }

        /**
         * Modifies the seminar registration form.
         *
         * @param \Tx_Seminars_FrontEnd_DefaultController $controller the calling controller
         * @param \Tx_Seminars_FrontEnd_RegistrationForm $registrationEditor the registration form
         */
        public function modifyRegistrationForm(
            \Tx_Seminars_FrontEnd_DefaultController $controller,
            \Tx_Seminars_FrontEnd_RegistrationForm $registrationEditor
        ): void {
            // Hier Ihr Code
        }

        /**
         * Modifies the footer of the seminar registration form.
         *
         * @param \Tx_Seminars_FrontEnd_DefaultController $controller the calling controller
         */
        public function modifyRegistrationFooter(\Tx_Seminars_FrontEnd_DefaultController $controller): void
        {
            // Hier Ihr Code
        }
    }

.. _notificationemail_de:

Hooks zu den Emails der Registrierungsbenachrichtigungen
""""""""""""""""""""""""""""""""""""""""""""""""""""""""

Es gibt folgende Hooks in die Emails der Registrierungsbenachrichtigungen:

* bevor das Template für die Teilnehmer-Benachrichtigung in Plain-Text umgewandelt wird
* bevor das Template für die Teilnehmer-Benachrichtigung in HTML umgewandelt wird
* bevor die Teilnehmer-Benachrichtigung abgeschickt wird
* bevor die Benachrichtigung an die Organisatoren abgeschickt wird
* bevor zusätzliche Statusinformationen an die Organisatoren abgeschickt werden

In den Template-Hooks können Sie eigene Marker ausfüllen oder vorhandene Marker-Werte ändern. Zu
verfügbaren Eigenschaften und Methoden dafür siehe :file:`Classes/Model/Registration.php`.
Der Plain-Text-Hook wird immer aufgerufen, denn eine HTML-Email enthält auch eine Plain-Text-Version.
Der HTML-Hook wird nur aufgerufen, wenn auch HTML-Emails versandt werden.

Die übrigen Hooks erlauben das Verändern des gesamten `Mail`-Objektes (z.B. Absender- oder
Empfänger-Adressen, Betreffzeile oder den gesamten Body).  Zu verfügbaren Eigenschaften und Methoden
siehe :file:`Classes/Mail.php` aus der Extension `oelib`.

Ihre Klasse, die :php:`\OliverKlee\Seminars\Hooks\Interfaces\RegistrationEmail` implementiert,
machen Sie seminars in :file:`ext_localconf.php` Ihrer Extension bekannt:

.. code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][\OliverKlee\Seminars\Hooks\Interfaces\RegistrationEmail::class][]
        = \Tx_Seminarspaypal_Hooks_RegistrationEmail::class;

Implementieren Sie die benötigten Methoden gemäß dem Interface:

.. code-block:: php

    use \OliverKlee\Seminars\Hooks\Interfaces\RegistrationEmail;

    class Tx_Seminarspaypal_Hooks_RegistrationEmail implements RegistrationEmail
    {
        /**
         * Modifies the attendee "Thank you" email just before it is sent.
         *
         * You may modify the recipient or the sender as well as the subject and the body of the email.
         *
         * @param string $emailReason Possible values:
         *          - confirmation
         *          - confirmationOnUnregistration
         *          - confirmationOnRegistrationForQueue
         *          - confirmationOnQueueUpdate
         */
        public function modifyAttendeeEmail(
            MailMessage $email,
            \Tx_Seminars_Model_Registration $registration,
            string $emailReason
        ): void {
            // Hier Ihr Code
        }

        /**
         * Modifies the attendee "Thank you" email body just before the subpart is rendered to plain text.
         *
         * This method is called for every confirmation email, even if HTML emails are configured.
         * The body of a HTML email always contains a plain text version, too.
         *
         * You may modify or set marker values in the template.
         *
         * @param \Tx_Seminars_Model_Registration $registration
         * @param string $emailReason Possible values:
         *          - confirmation
         *          - confirmationOnUnregistration
         *          - confirmationOnRegistrationForQueue
         *          - confirmationOnQueueUpdate
         */
        public function modifyAttendeeEmailBodyPlainText(
            Template $emailTemplate,
            \Tx_Seminars_Model_Registration $registration,
            string $emailReason
        ): void {
            // Hier Ihr Code
        }

        /**
         * Modifies the attendee "Thank you" email body just before the subpart is rendered to HTML.
         *
         * This method is called only, if HTML emails are configured for confirmation emails.
         *
         * You may modify or set marker values in the template.
         *
         * @param \Tx_Seminars_Model_Registration $registration
         * @param string $emailReason Possible values:
         *          - confirmation
         *          - confirmationOnUnregistration
         *          - confirmationOnRegistrationForQueue
         *          - confirmationOnQueueUpdate
         */
        public function modifyAttendeeEmailBodyHtml(
            Template $emailTemplate,
            \Tx_Seminars_Model_Registration $registration,
            string $emailReason
        ): void {
            // Hier Ihr Code
        }

        /**
         * Modifies the organizer notification email just before it is sent.
         *
         * You may modify the recipient or the sender as well as the subject and the body of the email.
         *
         * @param string $emailReason Possible values:
         *        - notification
         *        - notificationOnUnregistration
         *        - notificationOnRegistrationForQueue
         *        - notificationOnQueueUpdate
         */
        public function modifyOrganizerEmail(
            MailMessage $email,
            \Tx_Seminars_Model_Registration $registration,
            string $emailReason
        ): void {
            // Hier Ihr Code
        }

        /**
         * Modifies the organizer additional notification email just before it is sent.
         *
         * You may modify the recipient or the sender as well as the subject and the body of the email.
         *
         * @param string $emailReason Possible values:
         *          - 'EnoughRegistrations' if the event has enough attendances
         *          - 'IsFull' if the event is fully booked
         *          see Tx_Seminars_Service_RegistrationManager::getReasonForNotification()
         */
        public function modifyAdditionalEmail(
            MailMessage $email,
            \Tx_Seminars_Model_Registration $registration,
            string $emailReason
        ): void {
            // Hier Ihr Code
        }
    }

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
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['modifyEmailSalutation'][] = \MyVendor\MyExt\Hooks\ModifySalutationHook::class;


.. _datatimespan_de:

Hooks zur Erstellung Datums- und Zeitspannen
""""""""""""""""""""""""""""""""""""""""""""

Es gibt Hooks in die Erstellung der Datums- und Zeitspannen der Seminare. Wenn an irgendeiner Stelle
eine Datums- oder Zeitspanne ausgegeben werden soll, werden diese Hooks aufgerufen und erlauben das
Anpassen der Zusammensetzung. Für die Standard-Zusammensetzung siehe
:file:`Classes/OldModel/AbstractTimeSpan.php`.

Ihre Klasse, die :php:`\OliverKlee\Seminars\Hooks\Interfaces\DateTimeSpan` implementiert,
machen Sie seminars in :file:`ext_localconf.php` Ihrer Extension bekannt:

.. code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][\OliverKlee\Seminars\Hooks\Interfaces\DateTimeSpan::class][]
        = \Tx_Seminarspaypal_Hooks_DateTimeSpan::class;

Implementieren Sie die benötigten Methoden gemäß dem Interface:

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
            // Hier Ihr Code
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
            // Hier Ihr Code
        }
    }

.. _backendemail_de:

Hooks for the e-mails sent from the back-end module
"""""""""""""""""""""""""""""""""""""""""""""""""""

The hook classes need to be registered and written like this:

.. code-block:: php

   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['backEndModule'][]
         = \tx_seminarspaypal_Hooks_BackEndModule::class;

It's used like this:

.. code-block:: php

   class tx_seminarspaypal_Hooks_BackEndModule implements Tx_Seminars_Interfaces_Hook_BackEndModule {
        /**
         * Modifies the general e-mail sent via the back-end module.
         *
         * Note: This hook does not get called yet. It is just here so the interface
         * is finalized.
         *
         * @param \Tx_Seminars_Model_Registration $registration
         *        the registration to which the e-mail refers
         * @param Mail $eMail the e-mail that will be sent
        */
        public function modifyGeneralEmail(\Tx_Seminars_Model_Registration $registration, Mail $eMail): void {…}

         /**
         * Modifies the confirmation e-mail sent via the back-end module.
         *
         * @param \Tx_Seminars_Model_Registration $registration
         *        the registration to which the e-mail refers
         * @param Mail $eMail the e-mail that will be sent
         */
        public function modifyConfirmEmail(\Tx_Seminars_Model_Registration $registration, Mail $eMail): void {…}

         /**
        * Modifies the cancelation e-mail sent via the back-end module.
        *
        * Note: This hook does not get called yet. It is just here so the interface
        * is finalized.
        *
        * @param \Tx_Seminars_Model_Registration $registration
        *        the registration to which the e-mail refers
        * @param Mail $eMail the e-mail that will be sent
        */
        public function modifyCancelEmail(\Tx_Seminars_Model_Registration $registration, Mail $eMail): void {…}

.. _backendregistrationlistview_de:

Hooks zur Backend-Registrierungsliste
"""""""""""""""""""""""""""""""""""""

Es gibt 3 Hooks in die Backend-Registrierungsliste. Die Hooks werden während der Erstellung der
Backend-Registrierungsliste aufgerufen:

* bevor der Tabellenkopf in HTML umgewandelt wird
* bevor eine Tabellenzeile zu einer Registrierung in HTML umgewandelt wird
* bevor der Tabellenfuß in HTML umgewandelt wird

In diesen Hooks können Sie eigene Marker befüllen oder vorhandene Marker-Werte ändern. Zu
verfügbaren Eigenschaften und Methoden siehe :file:`Classes/Model/Registration.php` aus
`seminars` und :file:`Classes/Template.php` aus der Extension `oelib`.

Sie müssen 2 Listenarten bei Ihrer Implementation beachten:

* Liste regulärer Registrierungen (`REGULAR_REGISTRATIONS`)
* Liste der Registrierungen in der Warteschlange (`REGISTRATIONS_ON_QUEUE`)

Ihre Klasse, die :php:`\OliverKlee\Seminars\Hooks\Interfaces\BackendRegistrationListView` implementiert,
machen Sie seminars in :file:`ext_localconf.php` Ihrer Extension bekannt:

.. code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][\OliverKlee\Seminars\Hooks\Interfaces\BackendRegistrationListView::class][]
        = \Tx_Seminarspaypal_Hooks_BackendRegistrationListView::class;

Implementieren Sie die benötigten Methoden gemäß dem Interface:

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
         * @param Template $template the template that will be used to create the registration list
         * @param int $registrationsToShow
         *        the type of registration shown in the list
         */
        public function modifyListRow(
            \Tx_Seminars_Model_Registration $registration,
            Template $template,
            int $registrationsToShow
        ): void {
            // Hier Ihr Code
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
         * @param Template $template the template that will be used to create the registration list
         * @param int $registrationsToShow
         *        the type of registration shown in the list
         */
        public function modifyListHeader(
            \Tx_Seminars_Bag_Registration $registrationBag,
            Template $template,
            int $registrationsToShow
        ): void {
            // Hier Ihr Code
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
         * @param Template $template the template that will be used to create the registration list
         * @param int $registrationsToShow
         *        the type of registration shown in the list
         */
        public function modifyList(
            \Tx_Seminars_Bag_Registration $registrationBag,
            Template $template,
            int $registrationsToShow
        ): void {
            // Hier Ihr Code
        }
    }

.. _registrationlistcsv_de:

Hooks in die CSV-Generierung der Registrierungsliste
""""""""""""""""""""""""""""""""""""""""""""""""""""

Es gibt einen Hook in die CSV-Generierung der Registrierungsliste, um das erzeugte CSV
zu verändern.

Machen Sie seminars Ihre Klasse, die :php:`\OliverKlee\Seminars\Hooks\Interfaces\RegistrationListCsv`
implementiert, in :file:`ext_localconf.php` Ihrer Extension bekannt:

.. code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][\OliverKlee\Seminars\Hooks\Interfaces\RegistrationListCsv::class][]
        = \Tx_Seminarspaypal_Hooks_RegistrationListCsv::class;

Implementieren Sie die benötigten Methoden gemäß dem Interface:

.. code-block:: php

    use \OliverKlee\Seminars\Hooks\Interfaces\RegistrationListCsv;

    class Tx_Seminarspaypal_Hooks_RegistrationListCsv implements RegistrationListCsv
    {
        /**
         * Modifies the rendered CSV string.
         *
         * This allows modifying the complete CSV text right before it is delivered.
         *
         * @param string $csv the CSV text produced by `AbstractRegistrationListView::render()`
         * @param AbstractRegistrationListView $registrationList the CSV data provider
         *
         * @return string the modified CSV text to use
         */
        public function modifyCsv(string $csv, AbstractRegistrationListView $registrationList): string
        {
            // Hier Ihr Code
        }
    }

.. _datasanitization_de:

Hooks zur Datenbereinigung bei der TCE-Validierung
""""""""""""""""""""""""""""""""""""""""""""""""""

Es gibt einen Hook in den Data-Handler, um bei der TCE-Validierung (vor dem Speichern einer
Veranstaltung) zusätzliche Bedingungen zu prüfen und eigene dynamische Anpassungen der Daten
vorzunehmen (z.B. Registrierung-Deadline = Beginn-Datum minus 14 Tage).

Das Verfahren der TCE-Validierung ist von TYPO3 vorgegeben. `seminars` erhält dabei die Formular-Daten
aus dem FlexForm des Content-Elements und speichert nötige Änderungen der eingetragenen Werte in die
Datenbank.

Machen Sie seminars Ihre Klasse, die :php:`\OliverKlee\Seminars\Hooks\Interfaces\DataSanitization`
implementiert, in :file:`ext_localconf.php` Ihrer Extension bekannt:

.. code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][\OliverKlee\Seminars\Hooks\Interfaces\DataSanitization::class][]
        = \Tx_Seminarspaypal_Hooks_DataSanitization::class;

Implementieren Sie die benötigten Methoden gemäß dem Interface:

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
            // Hier Ihr Code
        }
    }
