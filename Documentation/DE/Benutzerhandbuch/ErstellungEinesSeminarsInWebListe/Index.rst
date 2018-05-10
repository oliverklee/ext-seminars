.. include:: Images.txt

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


Erstellung eines Seminars in Web > Liste
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

F

|img-40|  *Illustration 18: Auswahl des Datentyps für den neuen
Datensatz*

ür das Management der Seminare, wechseln wir in das Modul  *Liste* .Die Seminare und deren Anmeldungen werden in dem Seitenbaum unter Zusatzseiten im Ordner Seminare abgelegt.Um ein neues Seminar zu erstellen klicken wir auf das Symbol des
Ordners Seminare und wählen im Kontextmenu die Option „Neu“

Wir erhalten die Ansicht Neuer Datensatz im Content Frame. Dort wählen
wir den Datensatz „Seminare“ aus.

I

|img-41|  *Illustration 19: Eingabe der Grunddaten einer
Veranstaltung*

n der nun eingeblendeten Maske müssen wir folgende Punkte zuerst definieren:- Den Typ der Veranstaltung(Bei einmaligen Seminaren, kann der Typ
  Einzelveranstaltung gewählt werden. Bei wiederkehrenden Seminaren muss
  zuerst eine Veranstaltungsreihen Thema erstellt werden welche dann
  durch die einzelnen Veranstaltungsreihe Termine angesprochen werden
  kann.

- Der Titel des Seminars

- Und der Veranstalter

Danach speichern wir den Datensatz und ergänzen folgende Tabs mit
allen nötigen Informationen:

- Ort/Zeit

- Referenten

- Teilnehmer

- Wohnen/Essen

- Bezahlung

Die Tabs verteilen sich bei den Veranstaltungsreihen auf die beiden
Datensätze.

Folgende Felder werden in diesen Tabs zur Verfügung gestellt und im
Frontend angezeigt:

- Untertitel

- Bild (nur für die Seminartypen Einzelveranstaltung und Thema)

- Kategorien

- Anreißertext (für die Listenansicht)

- Beschreibung (hier kann HTML verwendet werden, falls erwünscht)

- Veranstaltungsart

- Sprache

- Seminarstart

- Seminarende

- Anmelde-Deadline

- Frühbucher-Deadline

- Lizenzablauf: wie lange eine Anmeldung als Voraussetzung für eine
  andere Anmeldung gültig ist

- Veranstaltungsort

- Raumnummer

- Referenten

- Standardpreis

- Frühbucher-Standardpreis

- Spezialpreis

- Frühbucher-Spezialpreis

- Erlaubte Zahlungsarten

- Registration notwendig (sollte immer aktiv sein)

- Mindestbesucherzahl zur Durchführung des Seminars

- Maximalbesucherzahl zur Durchführung des Seminars

- ob der automatische Kollisionscheck (d.h. das Teilnehmer sich nicht
  für eine Veranstaltung anmelden können, wenn sie bereits für eine
  Veranstaltung zu dieser Zeit angemeldet sind) für diese Veranstaltung
  ausgeschaltet werden soll

- Themen, für die ein Benutzer angemeldet sein muss, bevor er sich für
  dieses Thema anmelden kann (nur bei Themendatensätzen)

- Themen, für deren Anmeldung dieses Thema Voraussetzung ist (nur bei
  Themendatensätzen)

Alle interne Notizfelder sind (wie es der Name schon sagt) nur für
interne Informationen und werden nicht im Frontend angezeigt.


Zeitblöcke
""""""""""

Wenn ein Seminar nicht an allen Tagen zur gleichen Zeit stattfindet
(zum Beispiel Freitag von 14-18 Uhr, Samstag von 10-18 Uhr und Sonntag
von 10-14 Uhr), dann können Sie im Seminar-Datensatz so genannte
**Zeitblöcke** anlegen, um dies einzugeben und im Front-end korrekt
darzustellen:

|img-42|  *Abbildung 20: Zeitblöcke eingeben*

Sie können bei jedem Zeitblock Zeit, Ort, Einlass und ReferentInnen
angeben (falls diese nicht bei der gesamten Veranstaltung gleich
sind).

Wichtig: Bitte geben Sie die Zeitblöcke bei der Bearbeitung der
Veranstaltung ein. (Wenn Sie Zeitblöcke direkt in der Listenansicht
eingeben, werden die Zeitblöcke keiner Veranstaltung zugeordnet.)
