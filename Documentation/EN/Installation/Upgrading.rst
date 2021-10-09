=============================================================
Upgrading the extension across TYPO3 and PHP version upgrades
=============================================================

Recommended upgrade path with all affected extensions
=====================================================

To accommodate the multiple dependencies to PHP and TYPO3 versions as well
as between the extensions, this is the recommended upgrade path (with the
component to upgrade in each step marked bold):

#. PHP 7.0, TYPO3 8.7, seminars 3.4, oelib 3.6.1, mkforms 9.5.4,
   rn__base 1.11.4, static\_info\_tables 6.8.0
#. **PHP 7.2**, TYPO3 8.7, seminars 3.4, oelib 3.6.1, mkforms 9.5.4,
   rn__base 1.11.4, static\_info\_tables 6.8.0
#. PHP 7.2, TYPO3 8.7, seminars 3.4, oelib 3.6.1, mkforms 9.5.4,
   **rn__base 1.13.2**, static\_info\_tables 6.8.0
#. PHP 7.2, **TYPO3 9.5**, seminars 3.4, oelib 3.6.1, mkforms 9.5.4,
   rn__base 1.13.2, static\_info\_tables 6.8.0
#. PHP 7.2, TYPO3 9.5, seminars 3.4, oelib 3.6.1, mkforms 9.5.4,
   rn__base 1.13.2, **static\_info\_tables 6.9.5**
#. PHP 7.2, TYPO3 9.5, **seminars 4.0**, oelib 3.6.1, mkforms 9.5.4,
   rn__base 1.13.2, static\_info\_tables 6.9.5
#. PHP 7.2, TYPO3 9.5, seminars 4.0, **oelib 4.0*, **mkforms 10.0.0**,
   rn__base 1.13.2, static\_info\_tables 6.9.5
#. PHP 7.2, TYPO3 9.5, **seminars 4.1**, oelib 4.0, mkforms 10.0.0,
   rn__base 1.13.2, static\_info\_tables 6.9.5
#. PHP 7.2, **TYPO3 10.4**, seminars 4.1, oelib 4.0, mkforms 10.0.0,
   rn__base 1.13.2, static\_info\_tables 6.9.5
#. **PHP 7.4**, TYPO3 10.4, seminars 4.1, oelib 4.0, mkforms 10.0.0,
   rn__base 1.13.2, static\_info\_tables 6.9.5

Following this order, you will be able keep all affected extensions installed
all the time.

Important things to do when upgrading from seminars 3.x to 4.x
==============================================================

The TypoScript template files have been renamed from :file:`*.txt` to
:file:`*.typoscript`. If you are directly referencing any of these files,
you will need to upgrade your references.

All PHP classes are now namespaced. If you are using any XCLASSes, you will
need to update those accordingly.

The hook interfaces have been updated to use namespaced classes, and some
deprecated hooks have been removed. If you are using any hooks, please
update you hook classes.

All images and attachments have been migrated to FAL. Please run the seminars
upgrade wizards to automatically update you data.

The feature for uploading images and attachments in the FE editor has been
removed. If you are using a custom HTML template for the FE editor, you will
need to remove the corresponding subparts.
