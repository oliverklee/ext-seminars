.. ==================================================
￼.. FOR YOUR INFORMATION
￼.. --------------------------------------------------
￼.. -*- coding: utf-8 -*- with BOM.
￼
￼.. ==================================================
￼.. DEFINE SOME TEXTROLES
￼.. --------------------------------------------------
￼.. role::   underline
￼.. role::   typoscript(code)
￼.. role::   ts(typoscript)
￼   :class:  typoscript
￼.. role::   php(code)
￼
￼
￼Setup Multidomain 
￼^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
￼When using seminars for multiple domains in a TYPO3 project, all categories, payment methods, locations, organizers, etc. that can be found anywhere in the project appear by default. If several homepages are managed in one project, the entries to be displayed can be restricted.
To do this, the following values can be set in the page TS to the respective UID of the system folder in which the data can be found. In the following example, all data records are in the folder with UID 384. If no entry is made, all entries in the project are listed:
￼::
￼
￼   TCEFORM.tx_seminars_seminars.categories.PAGE_TSCONFIG_STR=384
￼   TCEFORM.tx_seminars_seminars.event_types.PAGE_TSCONFIG_STR=384
￼   TCEFORM.tx_seminars_seminars.sites.PAGE_TSCONFIG_STR =384
￼   TCEFORM.tx_seminars_seminars.lodgings.PAGE_TSCONFIG_STR=384
￼   TCEFORM.tx_seminars_seminars.foods.PAGE_TSCONFIG_STR=384
￼   TCEFORM.tx_seminars_seminars.speakers.PAGE_TSCONFIG_STR=384
￼   TCEFORM.tx_seminars_seminars.checkboxes.PAGE_TSCONFIG_STR=384
￼   TCEFORM.tx_seminars_seminars.payment_methods.PAGE_TSCONFIG_STR =384
￼   TCEFORM.tx_seminars_seminars.organizers.PAGE_TSCONFIG_STR=384
￼   TCEFORM.tx_seminars_seminars.target_groups.PAGE_TSCONFIG_STR=384

￼   
￼   TCEFORM.tt_content.pi_flexform.FLEXFORM-EVENTTYPES-PID=384
￼   TCEFORM.tt_content.pi_flexform.FLEXFORM-CATEGORIES-PID=384
￼   TCEFORM.tt_content.pi_flexform.FLEXFORM-SITES-PID=384
￼   TCEFORM.tt_content.pi_flexform.FLEXFORM-ORGANIZERS-PID=384
}