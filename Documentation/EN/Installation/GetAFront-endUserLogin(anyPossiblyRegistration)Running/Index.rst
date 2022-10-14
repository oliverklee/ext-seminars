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


Get a front-end user login (any possibly registration) running
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Choose a login box extension and a front-end user registration
extension, install and configure them. On my site, I use  *felogin*,
but you may want to use others. You can
leave out the front-end user registration extension if you don't want
front-end users to be able to create their own accounts.

If a user is on the detail view page of an event and wants to
register, he's shown a link to the login page. The URL provided there
contains the information to redirect the user directly to the
registration page after login. Important: This feature works only with
*felogin* !

It is possible to show the events title and date on the login page, by
setting up the event headline view on the login page.To do so, insert
a page content element on the login page containing the seminars plug-
inand set the view to „event headline.“If a user clicks on the
register link of an event, the title and date of the event will be
shown on the login page.

