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


Setting up the “multiple seats” feature
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

In the default configuration, this extension allows each user to
register only one seat per event. This can be changed if you need
users to register more than one seat per registration (e.g., when you
use this extension for a theater, or you would like companies to
register all their attendees in one go).

Please note that this doesn't enable users to register multiple
times—it just allows them to enter the number of seats for their
registration.

This is what needs to be changed:

#. For the back-end user group managing the registrations, enable the
   excludefield *Attendances: number of seats* . If you would like the
   attendee to also enter the names of the other attendees, please also
   add *Names of the attendees.*

#. Enable the seats field for the notification e-mail to the organizers
   by adding *seats* to
   *plugin.tx\_seminars.showAttendanceFieldsInNotificationMail* . If you
   would like the attendee to also enter the names of the other
   attendess, please also add *attendees\_names* .

If the field *seats* is not filled in (i.e., the registration is for 0
seats), the registration is counted as 1 seat.

Please note the the number of seats currently is not included in the
automated e-mail to the user. This will be implemented in a later
version of this extension.
