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


Setting access rights
^^^^^^^^^^^^^^^^^^^^^

The user groups who should be able to manage seminars should have:

- the module  *Web > Events* in their module list

- write access to the following tables (may be split into several
  groups): Seminars, Speakers, Registrations, Seminar Sites, Organizers,
  Payment Methods

- allowed excludefields: Seminars: hide, Seminars: start, Seminars: stop
  ( **only set this for users who really need it and know the difference
  between start/stop for FE display and start/stop of the seminar
  hours** )

- other needed excludefields from the event records

- the corresponding system folders in their DB mounts

If you want to enter registrations manually for participants who don't
have a front-end user account yet, or if you want to be able to edit
the front-end user data, you need to set the following access rights
as well:

- write access to the following tables (may be split into several
  groups): front-end users, addresses

- allowed excludefields: front-end user: name, address, phone
  email, zip code, city, inactive; address: mobile

- the front-end users system folder in their DB mounts
