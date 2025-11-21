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


Known problems
--------------

- The seminar hours are displayed without a unit, e.g. “17:00” instead
  of “17:00 h”.

- All registrations (paid and unpaid) are counted for the seminar
  statistics.

- In some cases, the list view in the front-end plug-in may be empty. Do
  this:

  - Check that all seminars lie within the configured time window for the
    list view (the default is current and upcoming events). Events without
    a begin date/time always appear as an upcoming event.

- All non-empty changes at the flexforms of the plug-in overwrite the
  settings of the corresponding TS setup. Empty data in the flexforms
  don't overwrite non-empty data from the TS setup.

- The search in the list view covers pretty most of what is visible in
  the single view except for the payment methods (this is intended).

- The inlining of CSS in HTML emails is available in Composer-mode only
  as this feature makes use of a third-party library.

- The CSV export currently will be empty if  the `b13/bolt` package is
  installed.

- The scheduler tasks currently cannot read their configuration if the
  `b13/bolt` package is installed.
