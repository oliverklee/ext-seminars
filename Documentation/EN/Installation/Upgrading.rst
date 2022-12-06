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

==========================================
Upgrading from seminars 4.x to 4.3 and 5.0
==========================================

New configuration values
========================

If you would like to use the informal salutation mode in the frontend, set
:typoscript:`plugin.tx_seminars.settings.salutation = informal` in the
TypoScript constants (or conveniently in the constants editor).

If you are using a different currency than Euro (or you would like to tweak
the currency format), edit :typoscript:`plugin.tx_seminars.settings.currency`
in the TypoScript constants (or conveniently in the constants editor).

Switching to the rewritten backend module
=========================================

Starting with seminars 4.4, using the new backend module form is recommended.
(The legacy backend module form will be removed in seminars 5.0.)

Edit your backend user or user group permissions, grant the users/groups
permissions for the new backend module, and drop their permissions for the
old backend module.

Switching to the rewritten registration form
============================================

Starting with seminars 4.3, using the new registration form is recommended.
(The legacy registration form will be removed in seminars 5.0.)

#.  If you are using the "onetimeaccount" extension on the login page, switch
    the plugin type to "One-time FE account creator without autologin".
    (This step is optional, but recommended.)

#.  On the registration page, switch the seminars plugin to a general plugin
    and set the type to "Registration form for events". Configure it to your
    needs using the settings in the FlexForms.

#.  Delete the thank-you page that was be display after someone has registered
    for an event. (This is now part of the registration form plugin.)

Switching to the rewritten FE editor
====================================

Starting with seminars 4.2, using the new FE editor is recommended.
(The legacy FE editor will be removed in seminars 5.0.)

#.  On the FE editor page, switch the seminars plugin to a general plugin
    and set the type to "Front-end editor for events". Configure it to your
    needs using the settings in the FlexForms.
