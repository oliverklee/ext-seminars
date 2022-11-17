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


Die Front-End Bearbeitung für Verwalter einrichten
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

@deprecated #1633 will be removed in seminars 5.0

Sie können so genannte Verwalter (ehemals VIPs) zu jeder ihrer
Veranstaltungen hinzufügen, welche dann – sofern von Ihnen
eingerichtet – eine spezielle Listenansicht mit deren Veranstaltungen
erhalten.

Wenn Sie möchten, können Sie Ihren Verwaltern das Bearbeiten deren
Veranstaltungen erlauben in dem Sie im TypoScript Setup die Variable
*plugin.tx\_seminars\_pi1.mayManagersEditTheirEvents* auf 1 setzen.
