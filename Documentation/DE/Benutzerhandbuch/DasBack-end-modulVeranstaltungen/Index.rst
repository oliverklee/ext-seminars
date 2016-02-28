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


Das Back-end-Modul „Veranstaltungen“
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Dieses Modul bietet den komfortablen Zugriff auf
Veranstaltungsdatensätze, Veranstalter, ReferentInnen und Anmeldungen.

**Wichtig: Es werden immer nur die Datensätze des ausgewählten
SysOrdners dargestellt.** Das bedeutet, dass Sie zwischendurch einen
anderen SysOrdner auswählen, wenn Ihre Datensätze (zum Beispiel die
Veranstaltungen und die Anmeldungen) in unterschiedlichen SysOrdnern
liegen.

In diesem Back-end-Modul erstellte Datensätze werden ebenfalls im
gerade ausgewählten SysOrdner angelegt.


Tab: Veranstaltungen
""""""""""""""""""""

|img-29|  *Abbildung 13: Tab "Veranstaltungen" im Back-end-Modul
"Veranstaltungen"*

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Spalte/Bedienelement
         Spalte/Bedienelement

   Bedeutung
         Bedeutung


.. container:: table-row

   Spalte/Bedienelement
         |img-30| Neuen Datensatz erstellen

   Bedeutung
         erstellt einen neuen Veranstaltungsdatensatz im ausgewählten SysOrdner


.. container:: table-row

   Spalte/Bedienelement
         |img-31| CSV-Datei herunterladen

   Bedeutung
         lädt alle Veranstaltungen in dieser Liste als CSV-Datei herunter, die
         sich unter anderem in Excel öffnen lässt


.. container:: table-row

   Spalte/Bedienelement
         |img-13| |img-15| |img-14|

   Bedeutung
         Art des Datensatzes:

         .. ### BEGIN~OF~TABLE ###

         .. container:: table-row

            a
                  |img-13|

            b
                  Einzelveranstaltung


         .. container:: table-row

            a
                  |img-14|

            b
                  Thema einer Veranstaltungsreihe


         .. container:: table-row

            a
                  |img-15|

            b
                  Termin einer Veranstaltungsreihe


         .. ###### END~OF~TABLE ######


.. container:: table-row

   Spalte/Bedienelement
         Akkreditierungsnummer

   Bedeutung
         manuell vergebene Nummer der Veranstaltung (kann auch leer sein)


.. container:: table-row

   Spalte/Bedienelement
         Titel

   Bedeutung
         Titel der Veranstaltung


.. container:: table-row

   Spalte/Bedienelement
         Datum

   Bedeutung
         Datum der Veranstaltung


.. container:: table-row

   Spalte/Bedienelement
         |img-32|

   Bedeutung
         Veranstaltung bearbeiten


.. container:: table-row

   Spalte/Bedienelement
         |img-33|

   Bedeutung
         Veranstaltung löschen


.. container:: table-row

   Spalte/Bedienelement
         |img-34|

   Bedeutung
         Veranstaltung (vorübergehend) ausblenden


.. container:: table-row

   Spalte/Bedienelement
         Akt.

   Bedeutung
         |img-35| Anzahl der aktuellen Anmeldungen; der CSV-Button lädt die
         Anmeldungen zu dieser Veranstaltung als CSV-Datei herunter, die sich
         unter anderem in Excel öffnen lässt


.. container:: table-row

   Spalte/Bedienelement
         Auf Warteliste

   Bedeutung
         Anzahl der Anmeldungen auf der Warteliste (falls die Veranstaltung
         eine Warteliste hat)


.. container:: table-row

   Spalte/Bedienelement
         Min.

   Bedeutung
         wie viele Anmeldungen diese Veranstaltung benötigt, um stattfinden zu
         können


.. container:: table-row

   Spalte/Bedienelement
         Max.

   Bedeutung
         wie viele Plätze es insgesamt für diese Veranstaltung gibt


.. container:: table-row

   Spalte/Bedienelement
         Genug

   Bedeutung
         ob die Veranstaltung genug Anmeldungen hat, um stattfinden zu können


.. container:: table-row

   Spalte/Bedienelement
         Voll

   Bedeutung
         ob alle Plätze dieser Veranstaltung belegt sind


.. container:: table-row

   Spalte/Bedienelement
         Status

   Bedeutung
         abgesagt, fest zugesagt oder in Planung (neutral)


.. container:: table-row

   Spalte/Bedienelement
         Button „absagen“

   Bedeutung
         sagt die Veranstaltung ab und schickt (per Mailformular) eine E-Mail
         an die angemeldeten Teilnehmer


.. container:: table-row

   Spalte/Bedienelement
         Button „zusagen“

   Bedeutung
         sagt die Veranstaltung fest zu und schickt (per Mailformular) eine
         E-Mail an die angemeldeten Teilnehmer


.. ###### END~OF~TABLE ######


Eine Veranstaltung absagen
""""""""""""""""""""""""""

Wenn eine Veranstaltung ausfällt, können Sie diese mit einem Klick auf
den „Absagen“-Button absagen und eine Mail an die angemeldeten
TeilnehmerInnen verschicken:

|img-36|  *Abbildung 14: eine Veranstaltung absagen*

In dem Mailformular wird bereits ein Text vorgegeben, den Sie vor dem
Abschicken bearbeiten können. Der Platzhalter **%s** wird automatisch
durch den Namen der Teilnehmerin/des Teilnehmers ersetzt.

Eine abgesagte Veranstaltung ist im Front-end weiterhin sichtbar (so
dass Sie nicht nach dem Absagen nicht haufenweise Anfragen „Wo finde
ich Ihre Veranstaltung denn auf der Website?“ erhalten ;-), ist aber
deutlich als abgesagt erkennbar. Eine Anmeldung für abgesagte
Veranstaltungen ist nicht möglich.


Eine Veranstaltung fest zusagen
"""""""""""""""""""""""""""""""

Wenn Sie sicher sind, dass eine Veranstaltung stattfinden kann (weil
sich genügend Leute angemeldet haben und die ReferentInnen ihr Okay
gegeben haben), können Sie eine Veranstaltung mit einem Klick auf den
„Zusagen“-Button fest zusagen und eine Mail an die angemeldeten
TeilnehmerInnen verschicken:

|img-37|  *Abbildung 15: eine Veranstaltung fest zusagen*

Anmeldungen für eine fest zugesagte Veranstaltung sind weiterhin
möglich (solang noch Plätze frei sind); lediglich der Text in der
Bestätigungsmail ist etwas geändert.


Tab: Anmeldungen
""""""""""""""""

|img-38|  *Abbildung 16: Tab "Anmeldungen" im Back-end-Modul
"Veranstaltungen"*

In diesem Tab werden  **alle** Anmeldungsdatensätze des ausgewählten
SysOrdners dargestellt (also von allen Veranstaltungen).

Die erste Liste **reguläre Anmeldungen** enthält die Anmeldungen, die
nicht auf der Warteliste sind.


Tab: ReferentInnen
""""""""""""""""""

|img-39|  *Abbildung 17: Tab "ReferentInnen" im Back-end-Modul
"Veranstaltungen"*

In diesem Tab werden  **alle** ReferentInnen-Datensätze des
ausgewählten SysOrdners dargestellt (also von allen Veranstaltungen).


Tab: Veranstalter
"""""""""""""""""

In diesem Tab werden  **alle** Veranstalter-Datensätze des
ausgewählten SysOrdners dargestellt (also von allen Veranstaltungen).
