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

==================
Configuring prices
==================

Configuring the currency
========================

If you are using a different currency than Euro (or you would like to tweak
the currency format), edit :typoscript:`plugin.tx_seminars.settings.currency`
in the TypoScript constants (or conveniently in the constants editor).

More price configuration options
================================

You can set up to four different prices for each event: a “standard
price” and a “special” price, e.g., for students and people in full
employment (each of them can also be saved as an early bird price). In
addition. In the registration form, the user can select the price to
pay.

**Events for free:** In the single view, the standard price always
gets displayed (even if it is 0.00), while the special price only gets
displayed if it is not 0.00. This means that if you need to enter a
price that is 0.00 (e.g., as a special discount), you need to enter
this as the standard price and enter the non-zero price as the special
price even if the non-free price technically is the standard price.

The early bird prices will only have an effect if you also define an
early bird deadline (until when these prices are valid). If no early
bird price is set or the deadline has already passed by, these prices
won't be visible in the front end.

If you have only one price per seminar, you can configure the list
view to not display the  *special price* column (look in the reference
for details). In addition, you might want to set some of the following
options to just display “price” instead of “standard price”:

- For the front-end view:

  - plugin.tx\_seminars\_pi1.generalPriceInList

  - plugin.tx\_seminars\_pi1.generalPriceInSingle

- For the e-mails to the attendees:

  - plugin.tx\_seminars.generalPriceInMail

If you have two prices for some or all seminars, you can change the
default labels “regular price” and “special price,” e.g., to “Adults”
and “Children.” You can change them using these variables:

- For the front-end list view and detail view:

  - plugin.tx\_seminars\_pi1.\_LOCAL\_LANG. *language*
    .label\_price\_regular / price\_regular\_early /
    / price\_special / price\_special\_early

- For the e-mails to the attendees and the drop-down box in the
  registration form:

  - plugin.tx\_seminars.\_LOCAL\_LANG. *language* .label\_price\_regular /
    price\_regular\_early / price\_special /
    price\_special\_early

Replace “ *language* ” with your two-letter language code if you use a
language other than English, e.g., “de” for German. Use “default” as
language code for English.
