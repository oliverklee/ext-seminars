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


Changing the localized strings
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You can change most of the localized strings that are used on the
front end and for the e-mails. (The localized strings for the back end
cannot be changed.)

When you want to change some strings, please don't change
locallang.xml directly as these changes would get
overwritten on the next update. Instead, do it like this:

#. Find out the language code of the language for which you'd like to
   change a string. The language code for English is “default” and the
   language code for German is “de.” All other languages have two-letter
   codes as well.

#. Find out whether the string which you'd like to change is in
   locallang.xml or FrontEnd/locallang.xml.

#. Find out the array key for that corresponding string.

#. In your TS setup, set the following (replacing  *language* with your
   language code and *key* with the corresponding array key):

- plugin.tx\_seminars.\_LOCAL\_LANG. *language.key* (for strings from
  locallang.xml) or

- plugin.tx\_seminars\_pi1.\_LOCAL\_LANG. *language.key* (for strings
  from FrontEnd/locallang.xml)
