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


Setup Multidomain
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
If you use seminars for multiple domains, all categories, payment methods, places, etc. existing in the typo3 project are shown by default. With the following configurations you can restrict the displayed entries in editing a seminar as well in using the flexforms.

All you have to do is set the following values in the page-ts on the UID of the system folder in which the entries are stored. In the following example all records of the systemfolder with UID 384 are displayed.

::

   TCEFORM.tx_seminars_seminars.categories.PAGE_TSCONFIG_STR =384
   TCEFORM.tx_seminars_seminars.event_type.PAGE_TSCONFIG_STR =384
   TCEFORM.tx_seminars_seminars.place.PAGE_TSCONFIG_STR =384
   TCEFORM.tx_seminars_seminars.lodgings.PAGE_TSCONFIG_STR =384
   TCEFORM.tx_seminars_seminars.foods.PAGE_TSCONFIG_STR =384
   TCEFORM.tx_seminars_seminars.speakers.PAGE_TSCONFIG_STR =384
   TCEFORM.tx_seminars_seminars.partners.PAGE_TSCONFIG_STR =384
   TCEFORM.tx_seminars_seminars.tutors.PAGE_TSCONFIG_STR =384
   TCEFORM.tx_seminars_seminars.leaders.PAGE_TSCONFIG_STR =384
   TCEFORM.tx_seminars_seminars.organizers.PAGE_TSCONFIG_STR =384
   TCEFORM.tx_seminars_seminars.organizing_partners.PAGE_TSCONFIG_STR =384
   TCEFORM.tx_seminars_seminars.target_groups.PAGE_TSCONFIG_STR =384
   TCEFORM.tx_seminars_seminars.checkboxes.PAGE_TSCONFIG_STR =384
   TCEFORM.tx_seminars_seminars.payment_methods.PAGE_TSCONFIG_STR =384
   
   TCEFORM.tt_content.pi_flexform.PAGE_TSCONFIG_STR=384




