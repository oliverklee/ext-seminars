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


Upgrading from a 0.10.x release to 1.0.x
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**Upgrading to 1.0.x will bring you a lot of new features, but it will
also mean a bit of work if you are using custom HTML templates.**

Also, most of the class names and hook declarations have changed.

You should do the upgrade at a time when there are usually no new
registrations as there might be some configuration check warnings on
the front end until you have finished the upgrade.

#. Make sure you are using PHP 5.5 or greater.

#. Make sure you are using TYPO3 6.2.0 or greater.

#. Temporarily uninstall seminars, onetimeaccount (if it is installed)
   and all extensions that use hooks or XCLASSes of seminars.

#. Remove the ameos_formidable extension from your system.

#. Update to the latest version of oelib and static\_info\_tables.

#. If you are using custom HTML templates, make a diff between the
   provided templates and your templates so you know what you have
   changed. (You’ll need to create new templates to make use of all new
   features. In addition, your old templates probably will display some
   garbage on the front end.) Switch off your custom templates.

#. Upgrade to the new seminars (and onetimeaccount, if needed) from the TER.

#. Enable the seminars extension again.

#. In the extension manager, enable the automatic configuration check for
   the Seminar Manager.

#. In your site TS template, include the static extension template
   *MKFORMS - Basics (mkforms)*
   *above* the static seminars template.

#. If your site does not use jQuery by default, also include the following
   static template::
     MKFORMS JQuery-JS (mkforms)

#. The CLI runner has been replaced by a Scheduler task. If you are using
   the cronjob, delete it and add a Scheduler task (with the same page
   UID for the configuration).

#. Run the extension's update script in the extension manager. (Choose the
   option “UPDATE!” in the extension's drop-down menu at the top. If the
   option is not displayed than you either have already run the update
   script or it is not necessary to run the update script on your
   installation.)

#. Remove the contents of the typo3temp/llxml/ and
   typo3conf/l10n/\*/seminars/ directories (if they exist).

#. Clear all caches.

#. Remove the FORMidable cache files in typo3temp/ameos\_formidable.

#. View all pages in the FE that contain anything Seminar Manager-
   related. Sign up for an event and check that everything still is
   working. If you encounter any errors from the automatic configuration
   check, fix the corresponding part of you configuration, clear the FE
   cache and reload the corresponding page.

#. Check that the e-mails to the participants and the organizers still
   are working and still look like you want them to look.

#. If you are using any seminars-related hooks or XCLASSES, update them
   to the new classes.

#. Have a look at the new fields for event records and decide which you
   want to use.

#. Play around with the configuration values hideColumns, hideFields,
   showRegistrationFields.

#. When everything is working, disabled the automatic configuration check
   in the extension manager.

#. The registration editor template can no longer be set via flexforms.

#. If you are using custom HTML templates: Make a copy of the provided
   templates, apply your changes from the diff, enable the custom
   templates and test them.

#. Now you’re finished. Or you could start playing with the new features
   ...
