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


Roles related to event records
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**Organizers** can be listed on the event list view and the single
view. Each event must have at least one organizer. The organizers
receive an email when someone signs up for an event (or when someone
unregisters). The first organizer is used as the sender for the
emails to the attendees. So each organizer must have a valid email
address.

**Attendees** are the front-end users who are signed up for an event.
They van view the events for which they are registered in the “my
events” view. The attendees’ contact data is stored in the front-end
user record, not in the registration records. The extension may be
configured so that attendees can my the list of registrations for
their events in the front end.

**Speakers** are the persons speaking at an event. There can be
several speakers for an event (and even none). The speakers are listed
on the event list view and the event single view. The speakers
currently are not used for any email related activities; so they do
not need to have an email address. If speakers should be able to view
the registration lists for their events, their corresponding front-end
user needs to be set as event manager for their events.

**Tutors, partners, tutors and leaders** are just speakers that are
listed under a different heading.

**Event managers/VIPs** are special front-end users who are allowed to
view the list of registrations for an event from their “my managed
events” list. The extension can be configured so that all users from e
a certain front-end user group are considered to be event managers for
all events. In addition, it can be configured so that event managers
may edit event records in the front end.
