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


Abmeldungen erlauben
^^^^^^^^^^^^^^^^^^^^

Damit sich Benutzer von Veranstaltungen wieder abmelden können (über
die „Meine Veranstaltungen“-Liste), ist Folgendes zu wichtig:

#. **Abmeldefrist:** Sie können entweder in den Veranstaltungen, für die
   eine Abmeldung möglich sein soll, individuelle Abmeldefristen
   eintragen, oder Sie können eine globale Abmeldefrist für alle
   Veranstaltungen setzen:
   plugin.tx\_seminars.unregistrationDeadlineDaysBeforeBeginDate

#. **Warteliste:** In der Standardeinstellung ist eine Abmeldung nur bei
   Veranstaltungen möglich, bei denen schonAnmeldungen auf der Warteliste
   stehen. Um Abmeldungen auch für Veranstaltungen einzuschalten, die
   keine oder eine leere Warteliste haben, können Sie diese Einstellung
   benutzen: plugin.tx\_seminars.allowUnregistrationWithEmptyWaitingList
   = 1.
