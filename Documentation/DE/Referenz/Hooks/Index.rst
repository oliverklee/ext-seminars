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


New hooks for the single view
"""""""""""""""""""""""""""""

There now are two new hooks for the single view. They are registered
like this in ext\_localconf.php:

::

   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['singleView'][]
         = \Tx_Seminarspaypal_Hooks_EventSingleView::class;

They are used like this:

::

   class Tx_Seminarspaypal_Hooks_SingleView implements Tx_Seminars_Interface_Hook_EventSingleView {

/\*\*

\* Modifies the event single view.

\*

\*  **@param** tx\_seminars\_Model\_Event $event

\* the event to display in the single view

\*  **@param** Tx\_Oelib\_Template $template

\* the template that will be used to create the single view output

\*

\*  **@return** void

\*/

public functionmodifyEventSingleView(tx\_seminars\_Model\_Event$event,
Tx\_Oelib\_Template$template) {…}

/\*\*

\* Modifies a list row in the time slots list (which is part of the
event

\* single view).

\*

\*  **@param** tx\_seminars\_Model\_TimeSlot $timeSlot

\* the time slot to display in the current row

\*  **@param** Tx\_Oelib\_Template $template

\* the template that will be used to create the list row output

\*

\*  **@return** void

\*/

public function modifyTimeSlotListRow(tx\_seminars\_Model\_TimeSlot
$timeSlot, Tx\_Oelib\_Template $template) {…}


New hooks for the list view
"""""""""""""""""""""""""""

There now is a new hook for the list view. It's registered like this
in ext\_localconf.php:

::

   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['listView'][]
         = \Tx_Seminarspaypal_Hooks_ListView::class;

It's used like this:

::

   class Tx_Seminarspaypal_Hooks_ListView implements Tx_Seminars_Interface_Hook_EventListView {
        /**
         * Adds a countdown column.
       *
       * @param Tx_Seminars_Model_Event $event
       *        the affected registration
        * @param Tx_Oelib_Template $template
        *        the template from which the list row is built
        */
         public function modifyListRow(
                 Tx_Seminars_Model_Event $event, Tx_Oelib_Template $template
         ) {…}

        /**
         * Adds an "add to cart" PayPal button for non-free registrations that have
       * not been paid for.
       *
       * @param Tx_Seminars_Model_Registration $registration
       *        the affected registration
        * @param Tx_Oelib_Template $template
        *        the template from which the list row is built
        */
         public function modifyMyEventsListRow(
                 Tx_Seminars_Model_Registration $registration, Tx_Oelib_Template $template
       ) {…}


Hooks to post process notification emails
""""""""""""""""""""""""""""""""""""""""""

To use this hook, please create a class that implements the interface
\\OliverKlee\\Seminars\\Hooks\\RegistrationEmailHookInterface. Then you need to add the following methods:

**Hook to post process the attendee email**

::

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

::

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

::

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

::

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

::

   // register my hook objects
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][] = \\MyVendor\\MyExt\\Hooks\\RegistrationEmailHook::class;


Hooks for the e-mails sent from the back-end module
"""""""""""""""""""""""""""""""""""""""""""""""""""

The hook classes need to be registered and written like this:

::

   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['backEndModule'][]
         = \tx_seminarspaypal_Hooks_BackEndModule::class;

It's used like this:

::

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

Please contact us if you need additional hooks.
