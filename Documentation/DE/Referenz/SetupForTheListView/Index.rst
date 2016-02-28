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


Setup for the list view
^^^^^^^^^^^^^^^^^^^^^^^

For the list view, there are some additional configuration option that
can only be set using the TS setup in the form
plugin.tx\_seminars\_pi1.listView. *property = value.* Those values
can  *not* be set via Flexforms.

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Eigenschaft
         Eigenschaft:

   Datentyp
         Datentyp:

   Beschreibung
         Beschreibung:

   Standardwert
         Standardwert:


.. container:: table-row

   Eigenschaft
         orderBy

   Datentyp
         string

   Beschreibung
         The default sort order in list view. Allowed values are:category,
         title, uid, event\_type, accreditation\_number, credit\_points,
         speakers, date, time, place, price\_regular, price\_special,
         organizers, vacancies

   Standardwert
         date


.. container:: table-row

   Eigenschaft
         descFlag

   Datentyp
         boolean

   Beschreibung
         whether to show the list view ordered in ascending (=0) or descending
         order (=1)

   Standardwert
         0


.. container:: table-row

   Eigenschaft
         results\_at\_a\_time

   Datentyp
         integer

   Beschreibung
         The number of events that shall be displayed per page

   Standardwert
         20


.. container:: table-row

   Eigenschaft
         maxPages

   Datentyp
         integer

   Beschreibung
         the number of neighboring pages to list in the page browser

   Standardwert
         5


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_seminars\_pi1.listView]
