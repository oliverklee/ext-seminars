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


Abmeldung von einem Seminar
^^^^^^^^^^^^^^^^^^^^^^^^^^^

User können sich selbst online von einem Seminar wieder abmelden, wenn
alle folgende Bedingungen erfüllt sind:

#. Es wurde für die Veranstaltung eine Abmeldefrist gesetzt (oder eine
   globale Abmeldefrist), und die Frist ist noch nicht abgelaufen.

#. Es befinden sich Anmeldungen auf der Warteliste, oder es ist
   konfiguriert, dass eine Abmeldung auch dann möglich ist, wenn die
   Warteliste leer ist.

Falls eine Abmeldung jedoch per Telefon oder per Mail eintrifft, so
sollte folgendes Vorgehen angewendet werden

- Im Backend manuell den entsprechenden Datensatz löschen (Siehe
  Anmeldungen ändern).