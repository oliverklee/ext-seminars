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


Die übersetzten Texte ändern
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Sie können die meisten Texte ändern, die im Front-end und in den
E-Mails benutzt werden. (Die lokalisierten Texte im Back-end können
nicht geändert werden.)

Wenn Sie einige lokalisierte Texte ändern möchten, bearbeiten Sie
bitte nicht direkt die Dateienlocallang.xml oder pi1/locallang.xml, da
Änderungen in diesen Dateien beim nächsten Update überschrieben werden
würden. Stattdessen können Sie Folgendes tun:

#. Find out the language code of the language for which you'd like to
   change a string. The language code for English is “default” and the
   language code for German is “de”. All other languages have two-letter
   codes as well.

#. Find out whether the string which you'd like to change is in
   locallang.xml or pi1/locallang.xml.

#. Find out the array key for that corresponding string.

#. In your TS setup, set the following (replacing  *language* with your
   language code and *key* with the corresponding array key):

- plugin.tx\_seminars.\_LOCAL\_LANG. *language.key* (für Texte aus
  locallang.xml) oder

- plugin.tx\_seminars\_pi1.\_LOCAL\_LANG. *language.key* (für Texte aus
  pi1/locallang.xml)
