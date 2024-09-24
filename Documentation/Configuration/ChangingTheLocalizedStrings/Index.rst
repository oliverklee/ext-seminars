============
Localization
============

Changing the salutation mode (formal to informal)
=================================================
If you would like to use the informal salutation mode in the frontend, set
:typoscript:`plugin.tx_seminars.settings.salutation = informal` in the
TypoScript constants (or conveniently in the constants editor).

Changing the localized strings
==============================

You can change most of the localized strings that are used on the
front end and for the emails. (The localized strings for the back end
cannot be changed.)

When you want to change some strings, please don't change
locallang.xlf directly as these changes would get
overwritten on the next update. Instead, do it like this:

#. Find out the language code of the language for which you'd like to
   change a string. The language code for English is “default” and the
   language code for German is “de.” All other languages have two-letter
   codes as well.

#. Find out whether the string which you'd like to change is in
   locallang.xlf or FrontEnd/locallang.xlf.

#. Find out the array key for that corresponding string.

#. In your TS setup, set the following (replacing  *language* with your
   language code and *key* with the corresponding array key):

- plugin.tx\_seminars.\_LOCAL\_LANG. *language.key* (for strings from
  locallang.xlf) or

- plugin.tx\_seminars\_pi1.\_LOCAL\_LANG. *language.key* (for strings
  from FrontEnd/locallang.xlf)
