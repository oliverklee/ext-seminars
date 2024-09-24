Setup for the list view
^^^^^^^^^^^^^^^^^^^^^^^

For the list view, there are some additional configuration option that
can only be set using the TS setup in the form
plugin.tx\_seminars\_pi1.listView. *property = value.* Those values
can  *not* be set via Flexforms.

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:

   Data type
         Data type:

   Description
         Description:

   Default
         Default:


.. container:: table-row

   Property
         orderBy

   Data type
         string

   Description
         The default sort order in list view. Allowed values are:category,
         title, uid, event\_type, accreditation\_number, credit\_points,
         speakers, date, time, place, price\_regular, price\_special,
         organizers, vacancies

   Default
         date


.. container:: table-row

   Property
         descFlag

   Data type
         boolean

   Description
         whether to show the list view ordered in ascending (=0) or descending
         order (=1)

   Default
         0


.. container:: table-row

   Property
         results\_at\_a\_time

   Data type
         integer

   Description
         The number of events that shall be displayed per page

   Default
         20


.. container:: table-row

   Property
         maxPages

   Data type
         integer

   Description
         the number of neighboring pages to list in the page browser

   Default
         5


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_seminars\_pi1.listView]
