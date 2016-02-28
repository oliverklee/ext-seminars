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


Rollen für Veranstaltungen
^^^^^^^^^^^^^^^^^^^^^^^^^^

**Veranstalter** können in der Frontend-Listenansicht und
Einzelansicht angezeigt werden. Jede Veranstaltung muss mindestens
einen Veranstalter haben. Die Veranstalter erhalten eine E-Mail, wenn
sich jemand zu einer Veranstaltung anmeldet oder davon abmeldet.
Außerdem wird der erste Veranstalter einer Veranstaltung als Absender
für die E-Mails an die Teilnehmer benutzt. Daher muss jeder
Veranstalter zwingend eine gültige E-Mail-Adresse besitzen.

**TeilnehmerInnen** sind die Frontend-Benutzer, die für eine
Veranstaltung angemeldet sind. Sie können ihre Anmeldungen in der
„meine Veranstaltungen“-Liste einsehen. Die Kontaktdaten der
Teilnehmer werden in den Frontend-Benutzer-Datensätzen gespeichert,
nicht in den Anmeldungsdatensätzen. Die Extension kann so konfiguriert
werden, dass Teilnehmer die Daten der anderen Teilnehmer im Frontend
einsehen können.

**ReferentInnen** sind die Personen, die eine Veranstaltung leiten
(oder dort sprechen). Eine Veranstaltung kann mehrere Referenten haben
(oder auch gar keine). Die Referenten werden in der Listen- und
Einzelansicht dargestellt. Die Referenten werden nicht für E-Mails
benutzt, so dass sie keine E-Mail-Adresse haben müssen. Wenn
Referenten die Teilnehmerlisten einer Veranstaltung einsehen können
sollen, muss ihr jeweiliger Frontend-Benutzer dafür als
Veranstaltungsmanager eingetragen sein.

**Partner, Kursbegleitung und Kursleitung** sind einfach ReferentInnen
mit einer anderen Überschrift.

**Besitzer** sind die Frontend-Benutzer, die eine Veranstaltung im
Frontend angelegt haben. Sie können ihre Veranstaltungen in der Liste
„Veranstaltungen, die ich eingegeben habe“ anzeigen.

**Verwalter/VIPs** sind spezielle Frontend-Benutzer, die
Anmeldungslisten für Veranstaltungen einsehen dürfen (in der Liste
„meine verwalteten Veranstaltungen“). Die Extension kann so
konfiguriert werden, dass alle Benutzer einer bestimmten Frontend-
Benutzergruppe als Verwalter fungieren. Außerdem kann konfiguriert
werden, dass Verwalter im Frontend die Veranstaltungen auch bearbeiten
können.