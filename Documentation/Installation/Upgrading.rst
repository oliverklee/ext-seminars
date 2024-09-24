==================================
Upgrading from seminars 4.x to 5.x
==================================

Upgrading in multiple steps
===========================

As this extension follow semantic versioning, seminars 5.0 has all the breaking
changes. So it is recommended you do the upgrade in the following order.

Also, clearing all caches after each step is recommended.

1. Drop removed content elements
================================

Some content elements have been removed in seminars 5.0.

#.  If you are using the event countdown, delete those content elements.
#.  If you are using the event headline plugin, delete those content elements.

2. Upgrade to seminars 4.4
==========================

Upgrade to seminars 4.4 and run the upgrade wizards (just to be sure).

3. Set new configuration values
===============================

If you would like to use the informal salutation mode in the frontend, set
:typoscript:`plugin.tx_seminars.settings.salutation = informal` in the
TypoScript constants (or conveniently in the constants editor).

If you are using a different currency than Euro (or you would like to tweak
the currency format), edit :typoscript:`plugin.tx_seminars.settings.currency`
in the TypoScript constants (or conveniently in the constants editor).

4. Switch to the rewritten FE editor
====================================

Starting with seminars 4.2, using the new FE editor is recommended.
(The legacy FE editor was removed in seminars 5.0.)

#.  On the FE editor page, switch the seminars plugin to a general plugin
    and set the type to "Front-end editor for events". Configure it to your
    needs using the settings in the FlexForms.

5. Switch to the rewritten registration form
============================================

Starting with seminars 4.3, using the new registration form is recommended.
(The legacy registration form was removed in seminars 5.0.)

#.  If you are using the "onetimeaccount" extension on the login page, switch
    the plugin type to "One-time FE account creator without autologin".
    (This step is optional, but recommended.)

#.  On the registration page, switch the seminars plugin to a general plugin
    and set the type to "Registration form for events". Configure it to your
    needs using the settings in the FlexForms.

#.  Delete the thank-you page that was be display after someone has registered
    for an event. (This is now part of the registration form plugin.)

6. Switch to the rewritten backend module
=========================================

Starting with seminars 4.4, using the new backend module form is recommended.
(The legacy backend module form was removed in seminars 5.0.)

Edit your backend user or user group permissions, grant the users/groups
permissions for the new backend module, and drop their permissions for the
old backend module.

7. Update the configuration
===========================

#.  Enable the automatic configuration check in the extension settings.
#.  Click through all your seminars-related content elements, watch for
    configuration check warnings, and fix them.
#.  Disable the automatic configuration check in the extension settings again.

8. Upgrade to seminars 5.x
==========================

#.  Upgrade to seminars 5.x.
#.  Uninstall the mkforms and rn\_base extensions (if you are not using
    Composer mode).
#.  Apply all DB updates.
#.  Run the upgrade wizards.

9. Enable the slug fields for your editors
==========================================

Enable the seminars "URL segment" (slug) field for your editors.
(Otherwise, you might get exceptions in the frontend if an event does
not have a slug and your are using
:ref:`nice URLs for the single view <single-view-urls>`.)
