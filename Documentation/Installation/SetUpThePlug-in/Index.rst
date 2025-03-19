Set up the plug-in
^^^^^^^^^^^^^^^^^^

Include the Seminars static template in your site template under
“Include static (from extensions).”.

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
   }

   # localizations for strings in emails and some FE parts go here (the example is for German)
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
   }

   # localizations for FE-only parts go here (the example is for German)
   plugin.tx_seminars_pi1._LOCAL_LANG.de {
   }

   # here you can change stuff like the number of items per page etc.
   plugin.tx_seminars_pi1.listView {
   }

Note that the notification email to the organizer and the list view
show the headings even for empty fields, while the single view and the
notification email to the participant remove the headings for some
seminar properties (not all, just where it makes sense).
