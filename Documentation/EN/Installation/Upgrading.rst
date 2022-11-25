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

==================================
Upgrading from seminars 4.x to 5.0
==================================

New configuration values
========================

If you would like to use the informal salutation mode in the frontend, set
:typoscript:`plugin.tx_seminars.settings.salutation = informal` in the
TypoScript constants (or conveniently in the constants editor).

If you are using a different currency than Euro (or you would like to tweak
the currency format), edit :typoscript:`plugin.tx_seminars.settings.currency`
in the TypoScript constants (or conveniently in the constants editor).
