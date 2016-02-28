.. include:: Images.txt

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


List of available record types
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   a
         |img-13|

   b
         single event

   c
         This is the default event record type when you create an event record.
         You can change the record type to “topic” or “date.”


.. container:: table-row

   a
         |img-14|

   b
         topic for multiple events

   c
         This event record type represents a topic for a series of events. It
         contains the basic data, for example the title and the description.
         After you have created a topic record, you can start entering date
         records for it.


.. container:: table-row

   a
         |img-15|

   b
         date

   c
         This event record type represents a date for a series of events (a
         topic).


.. container:: table-row

   a
         |img-16|

   b
         registration

   c
         These records get created when someone signs up for an event (or for
         the waiting list). Technically, they are the connection between an
         event and a person (represented by a front-end user).


.. container:: table-row

   a
         |img-17|

   b
         organizer

   c
         These records contain the sender for the thank-you e-mail sent to the
         attendees upon registration. In addition, registration notifications
         are sent to this e-mail address.


.. container:: table-row

   a
         |img-18|

   b
         payment method

   c
         Payment methods are available for selection in the event registration.


.. container:: table-row

   a
         |img-19|

   b
         speaker

   c
         Speakers get listed in the list view and the single view, but they
         don’t receive any e-mails.


.. container:: table-row

   a
         |img-20|

   b
         target group

   c
         Target groups of events are merely displayed, but don’t limit the
         registration in any way.

         Examples: Adults, teenagers, single parents, sociologists.


.. container:: table-row

   a
         |img-21|

   b
         time slow

   c
         Time slots are created  *within* event records. They are used to
         represent a scenario like an event taking place on Friday from 14-18
         o’clock and on Saturday from 10-14 o’clock.


.. container:: table-row

   a
         |img-22|

   b
         place

   c
         Places (event sites) are displayed in the list view and the single
         view.


.. container:: table-row

   a
         |img-23|

   b
         skill

   c
         These records are skills which can be associated with the speakers.
         Currently, the skills are not displayed in the front-end yet (missing
         feature).


.. container:: table-row

   a
         |img-24|

   b
         lodging option

   c
         Lodging options are available for selection on the registration page.


.. container:: table-row

   a
         |img-25|

   b
         food option

   c
         Food options are available for selection on the registration page.


.. container:: table-row

   a
         |img-26|

   b
         event type

   c
         Each event can be associated with exactly one event type, for example
         workshop, evening class, talk or discussion group. The event type is
         displayed in the list view and the single view.


.. container:: table-row

   a
         |img-27|

   b
         checkbox for the registration page

   c
         These are options available on the registration page.


.. container:: table-row

   a
         |img-28|

   b
         category

   c
         Each event can be associated with several categories, for example
         theory seminars or examination preparation courses.


.. ###### END~OF~TABLE ######
