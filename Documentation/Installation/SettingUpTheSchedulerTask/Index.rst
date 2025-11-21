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


Setting up the Scheduler task
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

..  warning::
    The CSV export currently will be empty if  the `b13/bolt` package is
    installed.

This extension offers a Scheduler Task to trigger actions. It can be configured to
send reminders to the events' organizers

- if a confirmed event is about to begin, or

- if the speakers' cancelation deadline of a neither confirmed nor
  canceled event has just passed.

The reminders are emails with a localized text and the list of
registrations appended as CSV.

To setup the CLI, do the following:

#. Set up the Scheduler as described in the manual of the Scheduler extension.

#. Choose/create a FE page where to do some TS setup configuration for
   the Scheduler task and configure the following:

- Set the option “ *sendCancelationDeadlineReminder* ” to 1 to enable
  the cancellation deadline reminder.

- For the option “ *sendEventTakesPlaceReminderDaysBeforeBeginDate* ”,
  set the number of days before an upcoming event, when to send a
  reminder to the organizers. Setting zero will disable this reminder
  about an event taking place.

- In order to customize the appended CSV, the options “
  *filenameForRegistrationsCsv* ”, “ *fieldsFromFeUserForEmailCsv* ”, “
  *fieldsFromAttendanceForEmailCsv* ” and “
  *showAttendancesOnRegistrationQueueInEmailCsv* ” are relevant. Please
  consider the corresponding section about CSV-File Attachmentfor more
  details.

#. Add a seminars Scheduler task and provide UID of the page with the configuration.


**CSV-File Attachment**
"""""""""""""""""""""""

The mails send via Scheduler can contain a CSV file with the registrations
to the event the mail is sent for. To customize the contents of the
CSV file use the following options:

- “ *fieldsFromAttendanceForEmailCsv* ” and “
  *fieldsFromFeUserForEmailCsv* ” customize the fields which are
  exported in the CSV file. Please note that the CSV files always
  contains the columns for the data from the registration records first
  and then data from the corresponding FE user record.

- “ *filenameForRegistrationsCsv* ” determines the name of the attached
  CSV file.

- “ *showAttendancesOnRegistrationQueueInEmailCsv* ” determines whether
  registrations on the waiting list, should also be exported via CSV.


** Daily digest of new registrations **
"""""""""""""""""""""""""""""""""""""""

The Scheduler task also can send a (usually daily) digest of new registration.
This functionality can be enabled and configured via TypoScript setup in the
namespace plugin.tx\_seminars.registrationDigestEmail.

The emails will use the language that has been set as default language for the
Scheduler back-end user.
