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


Upgrading from a 0.9.x release to 0.10.x
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**Upgrading to .10.x will bring you a lot of new features, but it will
also mean a bit of work if you are using custom HTML templates.**

You should do the upgrade at a time when there are usually no new
registrations as there might be some configuration check warnings on
the front end until you have finished the upgrade.

#. Make sure you are using PHP 5.3 or greater.

#. Make sure you are using TYPO3 4.5.0 or greater.

#. Update to the latest version of oelib and static\_info\_tables.

#. If you are using custom HTML templates, make a diff between the
   provided templates and your templates so you know what you have
   changed. (You’ll need to create new templates to make use of all new
   features. In addition, your old templates probably will display some
   garbage on the front end.) Switch off your custom templates.

#. If you are using the old single view hook, adapt your code to use the
   new single view hook instead.

#. Upgrade to the new Seminar Manager from TER and upgrade the database.

#. In the extension manager, enable the automatic configuration check for
   the Seminar Manager.

#. Run the extension's update script in the extension manager.(Choose the
   option “UPDATE!” in the extension's drop-down menu at the top. If the
   option is not displayed than you either have already run the update
   script or it is not necessary to run the update script on your
   installation.)

#. Remove the contents of the typo3temp/llxml/ and
   typo3conf/l10n/\*/seminars/ directories (if they exist).

#. Clear all caches.

#. Remove the temp cache files in typo3conf (either manualy or via
   extdeveval).

#. Remove the FORMidable cache files in typo3temp/ameos\_formidable.

#. In the TYPO3 back end, edit all plug-in content elements which you are
   using for displaying an event single view. In the “what to display”
   drop-down, change the selection form “event list” to “event single
   view”.

#. View all pages in the FE that contain anything Seminar Manager-
   related. Sign up for an event and check that everything still is
   working. If you encounter any errors from the automatic configuration
   check, fix the corresponding part of you configuration, clear the FE
   cache and reload the corresponding page.

#. Check that the e-mails to the participants and the organizers still
   are working and still look like you want them to look.

#. Many classes have been moved or renamed. If you are using XCLASSes,
   you'll need to adapt them.

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
