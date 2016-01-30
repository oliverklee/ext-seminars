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
         = 'EXT:seminarspaypal/Classes/Hooks/EventSingleView.php:' .
                 '&Tx_Seminarspaypal_Hooks_EventSingleView';

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
         = 'EXT:seminarspaypal/Hooks/Classes/Hooks/ListView.php:' .
                 '&Tx_Seminarspaypal_Hooks_ListView';

It's used like this:

::

   class Tx_Seminarspaypal_Hooks_ListView implements Tx_Seminars_Interface_Hook_EventListView {
        /**
         * Adds a countdown column.
       *
       * @param tx_seminars_Model_Event $event
       *        the affected registration
        * @param Tx_Oelib_Template $template
        *        the template from which the list row is built
        */
         public function modifyListRow(
                 tx_seminars_Model_Event $event, Tx_Oelib_Template $template
         ) {…}

        /**
         * Adds an "add to cart" PayPal button for non-free registrations that have
       * not been paid for.
       *
       * @param tx_seminars_Model_Registration $registration
       *        the affected registration
        * @param Tx_Oelib_Template $template
        *        the template from which the list row is built
        */
         public function modifyMyEventsListRow(
                 tx_seminars_Model_Registration $registration, Tx_Oelib_Template $template
       ) {…}


Hooks for the organizer notification e-mails
""""""""""""""""""""""""""""""""""""""""""""

To use this hook, please create a class that implements the interface
tx\_seminars\_Interface\_Hook\_Registration. The method in your class
then should expect two parameters:

::

   public function modifyOrganizerNotificationEmail(
         tx_seminars_registration $registration, Tx_Oelib_Template $emailTemplate
   ) {

Your class then needs to be included and registered like in this
example:

::

   // register my hook objects
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][] = 'EXT:invoices/class.tx_invoices_email.php:tx_invoices_email';


Hooks for the thank-you e-mails sent after a registration
"""""""""""""""""""""""""""""""""""""""""""""""""""""""""

There are two hooks: one for modifying the e-mail (e.g., adding
recipients or attachments), and one for modifying the e-mail texts
before the corresponding subparts are rendered

**Hook for the e-mail**

To use this hook, you need to create a class with a method named
modifyThankYouEmail. The method in your class should expect two
parameters:

::

           public function modifyThankYouEmail(
                 Tx_Oelib_Mail $email, tx_seminars_Model_Registration $registration
         ) {

Your class then needs to be included and registered like in this
example:

::

   // includes my hook class
   require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('invoices') . 'class.tx_invoices_email.php');

   // register my hook objects
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][] = 'EXT:invoices/class.tx_invoices_email.php:tx_invoices_email';

**Hook for the e-mail text**

To use this hook, please create a class that implements the interface
tx\_seminars\_Interface\_Hook\_Registration. The method in your class
then should expect two parameters:

::

    public function modifyAttendeeEmailText(
                 tx_seminars_registration $registration, Tx_Oelib_Template $emailTemplate
       ) {

Your class then needs to be included and registered like in this
example:

::

   // register my hook objects
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][] = 'EXT:invoices/class.tx_invoices_email.php:tx_invoices_email';


Hooks for the e-mails sent from the back-end module
"""""""""""""""""""""""""""""""""""""""""""""""""""

The hook classes need to be registered and written like this:

::

   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['backEndModule'][]
         = 'EXT:seminarspaypal/Hooks/class.tx_seminarspaypal_Hooks_BackEndModule.php:' .
                 '&tx_seminarspaypal_Hooks_BackEndModule';

It's used like this:

::

   class tx_seminarspaypal_Hooks_BackEndModule implements Tx_Seminars_Interface_Hook_BackEndModule {
         /**
        * Modifies the general e-mail sent via the back-end module.
        *
        * Note: This hook does not get called yet. It is just here so the interface
        * is finalized.
        *
        * @param tx_seminars_Model_Registration $registration
        *        the registration to which the e-mail refers
        * @param Tx_Oelib_Mail $eMail
        *        the e-mail that will be sent
        *
        * @return void
        */
         public function modifyGeneralEmail(tx_seminars_Model_Registration $registration, Tx_Oelib_Mail $eMail) {…}

         /**
        * Modifies the confirmation e-mail sent via the back-end module.
        *
        * @param tx_seminars_Model_Registration $registration
        *        the registration to which the e-mail refers
        * @param Tx_Oelib_Mail $eMail
        *        the e-mail that will be sent
        *
        * @return void
        */
         public function modifyConfirmEmail(tx_seminars_Model_Registration $registration, Tx_Oelib_Mail $eMail) {…}

         /**
        * Modifies the cancelation e-mail sent via the back-end module.
        *
        * Note: This hook does not get called yet. It is just here so the interface
        * is finalized.
        *
        * @param tx_seminars_Model_Registration $registration
        *        the registration to which the e-mail refers
        * @param Tx_Oelib_Mail $eMail
        *        the e-mail that will be sent
        *
        * @return void
        */
          public function modifyCancelEmail(tx_seminars_Model_Registration $registration, Tx_Oelib_Mail $eMail) {…}

Please contact us if you need additional hooks.
