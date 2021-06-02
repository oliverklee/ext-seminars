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


Set up the plug-in
^^^^^^^^^^^^^^^^^^

**First, include the *MKFORMS - Basics (mkforms)*
template in your site template under
“Include static (from extensions).”**

**Below that, include the *Seminars* static template**.
It is important that you include this
template *below* the MKFORMS template.

If your site does not use jQuery by default, also include the following
static template::

   MKFORMS JQuery-JS (mkforms)

Then configure the plug-in in your TS template setup or the plug-in
flexforms. The properties are listed in the reference.

Please note than when using flexforms, you need to set the
corresponding values at all relevant instances of the plug-in: It
doesn't do to specify the fields for the online registration in the
seminar list front-end plug-in—you need to set these fields in the
online registration front-end plug-in.

You can use this TypoScript setup template for setting all required
values for a basic setup:

::

   plugin.tx_seminars {
     # PID of the sysfolder where event registrations (attendances) will be stored
     attendancesPID =
   }

   # localizations for strings in e-mails and some FE parts go here (the example is for German)
   plugin.tx_seminars._LOCAL_LANG.de {
   }

   plugin.tx_seminars_pi1 {
     # PID of the sysfolder that contains all the event records (e.g., the starting point)
     pages =

     # PID of the FE page that contains the event list
     listPID =

     # PID of the FE page that contains the single view
     detailPID =

     # PID of the FE page that contains the "my events" list
     myEventsPID =

     # PID of the FE page that contains the seminar registration plug-in
     registerPID =

     # PID of the FE page that contains the login form or onetimeaccount
     loginPID =

     # PID of the thank-you page that will be displayed after a FE user has registered for an event
     thankYouAfterRegistrationPID =

     # PID of the page that will be displayed after a FE user has unregistered from an event
     pageToShowAfterUnregistrationPID =
   }

   # localizations for FE-only parts go here (the example is for German)
   plugin.tx_seminars_pi1._LOCAL_LANG.de {
   }

   # here you can change stuff like the number of items per page etc.
   plugin.tx_seminars_pi1.listView {
   }

Note that the notification e-mail to the organizer and the list view
show the headings even for empty fields, while the single view and the
notification e-mail to the participant remove the headings for some
seminar properties (not all, just where it makes sense).
