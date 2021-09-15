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

- Many feature ideas still are unimplemented. Feel free to provide code
  or pay for the author's time.

- The seminar hours are displayed without a unit, e.g. “17:00” instead
  of “17:00 h”.

- All registrations (paid and unpaid) are counted for the seminar
  statistics.

- In some cases, the list view in the front-end plug-in may be empty. Do
  this:

- Check that all seminars lie within the configured time window for the
  list view (the default is current and upcoming events). Events without
  a begin date/time always appear as an upcoming event.

- It doesn't work to have the seminar manager and the online
  registration on the same page (you will get an error message in the
  registration plug-in). Do this:

  - Put them on separate pages and set plugin.tx\_seminars\_pi1.listPID
    and plugin.tx\_seminars\_pi1.registerPID.

- **All non-empty changes at the flexforms of the plug-in overwrite the
  settings of the corresponding TS setup. Empty data in the flexforms
  don't overwrite non-empty data from the TS setup.**

- The search in the list view covers pretty most of what is visible in
  the single view except for the payment methods (this is intended).

- If the maximum upload file size in PHP is set to a lower value than
  the one in TYPO3, the FE editor does not show an error message if one
  tries to upload a too large file.

- The front-end editor does not work with MySQL/MariaDB in strict mode. You will
  need to remove `STRICT_TRANS_TABLES` from `sql_mode`:

.. code-block:: ini

   # This is required for the seminars FE editor to graciously convert "" to 0
   # for integer columns (which is a shortcoming of the "mkforms" extension).
   sql_mode=ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION
