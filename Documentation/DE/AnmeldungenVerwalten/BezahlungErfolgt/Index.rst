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


Bezahlung erfolgt
^^^^^^^^^^^^^^^^^

Sie können ebenfalls im Datensatz der Anmeldung die
Zahlungsinformationen im Backend verwalten falls erwünscht.Folgende
Felder stehen dabei im Modul Veranstaltungen/Anmeldungen zur
Verfügung:

- Hat bezahlt

- Datum der Bezahlung

- Zahlungsart (Diverse Bankinformationen können hier ebenfalls
  eingegeben werden)


Entering and managing event types
"""""""""""""""""""""""""""""""""

On the same system folder that contains the speakers and organizers,
you can create event types. At the moment an event type record consist
of only a title field. You can assign none or exactly one event type
to an event record. If you assign no event type to an event, the
default event type from TS setup will be used.

The field is hidden in the list view by default.


Using lodging and food options
""""""""""""""""""""""""""""""

You can create “lodging options” and “food options” records that will
be available in the registration form. After you have created these
records, you can select them in the event records; the corresponding
options then will be displayed in the registration for for this event
and get saved in the registration record.


Displaying the seminar and registration statistics and details
""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""

**Back-end module “Events”:** The back-end module "Events" has four
different tabs:

- Events

- Registrations

- Speakers

- Organizers

By clicking on a tab, you can access the according submodule. Each
submodule shows a list of records of the according type on the
currently selected page or sysfolder (e.g. the submodule "Events"
shows a list of events).

It is possible to delete, to modify or to create new records within
the back-end module if the back-end user has the rights to do this.

**CSV export:** In the event list, you can also export the events on
the current page or the list of registrations of an event as CSV.


CSV export of events
""""""""""""""""""""

At the top in the event list in the back-end module  *Events* , you’ll
find a button named Export as *CSV* that will save the data of all
events on the current page as CSV.


CSV export of registrations
"""""""""""""""""""""""""""

In the event list in the back-end module  *Events* , you’ll find a
button named *CSV* that will save the data of the registrations for
that particular event as CSV, also including data from the registered
FE user. Please note that the CSV files contains the columns for the
data from the registration records first and then data from the
corresponding FE user record.

The CSV export can be configured via TS Setup in plugin.tx\_seminars
for the page where the event records are located. Please see the
reference for details.

CSV export of registrations is only available if:

- the event has at least one registration, and

- the logged-in BE user has read access to the events table and the
  registrations table, and

- the logged-in BE user has read access to all pages where the
  registrations for that particular event are stored


Changing, deleting and entering registrations
"""""""""""""""""""""""""""""""""""""""""""""

You can edit, delete and enter registration records using W *eb* >
*Lists* as well as the back-end module *Events* .


Unregistering from an event
"""""""""""""""""""""""""""

Front-end users currently cannot unregister themselves from events.
This is a missing feature. As a workaround, do this:

- In the back end, manually delete the corresponding registration
  record.

- Then go to  *Web > Seminars* and use the “Update Statistics” function.


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

If you want to record who has attended a seminar and who hasn't (eg.
for certificates), you can edit the corresponding registration record
and fill in this field:

- Has attended