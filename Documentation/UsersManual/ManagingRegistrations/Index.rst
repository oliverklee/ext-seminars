Managing registrations
^^^^^^^^^^^^^^^^^^^^^^

Registration for an event is possible until the registration deadline.
If the event has no such deadline, registration is possible until the
start of the event.

When a logged-in front-end user registers for a seminar, the following
happens:

#. It is checked whether it still is possible to register for that
   seminar and the user still hasn't registered for that seminar yet.
   **Note:** If you need to allow the same front-end user to register for
   the same event multiple times, you can allow this in the event record.

#. In addition, the user can select a price, food options and other
   options. The total price then is calculated from the selected price
   and the number of seats.

#. An  *attendance* record is entered into the database, making the connection
   between this front-end user and the corresponding event. The statistics for
   that event are immediately updated in the back end and front end,
   preventing overbooked seminars.

#. A thank-you email is sent to the front-end user using the first
   organizer of that seminar record as From: address and that organizer's
   email footer. The thank-you email also has a disclaimer if the event
   is planned and not confirmed yet. The disclaimer, says that the user
   will be informed if this event will be confirmed.

#. A notification email is sent to that seminar's organizers (all of
   them, not just the first), using the attendee's email address as
   From: address.

#. Additional notification emails are sent if the event reaches the
   minimum limit of registrations to be held, or if the event gets fully
   booked. These notifications go to all organizers of this event, the
   first organizer's email address is used as sender.These mails will
   only be sent, if they are activated in the TypoScript setup. By
   default, the mails will be sent.

#. The user will be redirected to the thank-you page.

#. The booked event will be visible on the “my events” page.

From the “my events” page the user has the possibility to unregister
from an event. When a user unregisters the corresponding attendances
record will be marked as hidden.


Displaying the seminar and registration statistics and details
""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""

In the **back-end module “Events”, you can

*   list and edit events
*   export events as CSV
*   display the registrations for an event
*   export the registrations for an event as CSV
*   send an email to the attendees of an event


CSV export of events
""""""""""""""""""""

At the top in the event list in the back-end module *Events* , you’ll
find a button labelled "Download as CSV file" that will save the data of all
events on the current page as CSV.

CSV export of registrations
"""""""""""""""""""""""""""

At the top in the registration list for an event in the back-end module
*Events*, you’ll find a button labelled "Download as CSV file" that will save
the data of all registrations of the selected event as CSV, also
including data from the registered FE user. Please note that the CSV file
contains the columns for the data from the registration records first and then
data from the corresponding FE user record.

The columns used for the export of
registrations is determined by the two configuration
variables `fieldsFromFeUserForCsv` and `fieldsFromAttendanceForCsv`.

The CSV export can be configured via TS Setup in plugin.tx\_seminars
for the page where the event records are located. Please see the
reference for details.

CSV export of registrations is only available if:

- the event has at least one registration, and

- the logged-in BE user has read access to the events table and the
  registrations table, and

- the logged-in BE user has read access to all pages where the
  registrations for that particular event are stored


Changing registrations
"""""""""""""""""""""""""""""""""""""""""""""

You can edit registration records using Web >
List as well as the back-end module *Events*.


Unregistering from an event
"""""""""""""""""""""""""""

Front-end users can unregister themselves from an event using the “my
events” view if they are logged in and all of the following conditions
are met:

#. The event has an unregistration deadline set (or a global
   unregistration deadline has been set), and the deadline has not passed
   yet.

#. There are registrations on the waiting list, or the extension is
   configured to allow unregistration even if the waiting list is empty.


Entering payments
"""""""""""""""""

You can also use this extension to record payments from participants
for their seminar. If you have received a payment (be in in cash, bank
transfer, credit card or whatever), edit the corresponding
registration record and fill in the following fields:

- Has paid:  **Note that this field will go away soon. Instead, if
  someone has paid will be deducted by whether a payment date has been
  entered. So make sure to set a payment date for all attendances that
  have been paid.**

- Date of payment  **(if this field is set, an attendance is considered
  as paid, so always enter the date when you enter a payment)**

- Method of payment (optional, use it if you like to track this)


Tracking who has attended a seminar and who hasn't
""""""""""""""""""""""""""""""""""""""""""""""""""

If you want to record who has attended a seminar and who hasn't (e.g.,
for certificates), you can edit the corresponding registration record
and fill in this field:

- Has attended
