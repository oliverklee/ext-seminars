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


Liste der verfügbaren Datentypen
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   a
         |img-13|

   b
         Einzelveranstaltung

   c
         Dies ist der Standard-Seminar-Datensatztyp, wenn Sie einen
         Veranstaltungsdatensatz anlegen. Sie können den Datensatz auf „Thema“
         oder „Termin“ umändern.


.. container:: table-row

   a
         |img-14|

   b
         Thema für Veranstaltungsreihen

   c
         Dieser Seminar-Datensatztyp stellt ein Thema für eine
         Veranstaltungsreihe dar und enthält die Grunddaten (z.B. Titel und
         Beschreibung). Sie können zu diesem Thema dann Termindatensätze
         anlegen.


.. container:: table-row

   a
         |img-15|

   b
         Termin für Veranstaltungsreihen

   c
         Diese Seminar-Datensatztype stellt einen Termin für eine
         Veranstaltungsreihe (eine Thema) dar.


.. container:: table-row

   a
         |img-16|

   b
         Anmeldung

   c
         Diese Datensätze werden erzeugt, wenn sich jemand für eine
         Veranstaltungs anmeldet (oder für die Warteliste). Sie sind die
         Verbindung zwischen einer Veranstaltung und einer Person (einem Front-
         end-Benutzer).


.. container:: table-row

   a
         |img-17|

   b
         Veranstalter

   c
         Diese Datensätze enthalten den Absender der Anmelde-Mails an die
         Teilnehmer. Außerdem gehen die Anmeldebenachrichtigungen an die darin
         hinterlegte Mailadresse.


.. container:: table-row

   a
         |img-18|

   b
         Zahlungsart

   c
         Zahlungsarten stehen bei der Anmeldung zu einer Veranstaltung zur
         Auswahl.


.. container:: table-row

   a
         |img-19|

   b
         ReferentIn

   c
         ReferentInnen werden auf der Listen- und Einzelansicht lediglich
         dargestellt, erhalten aber keine Mail.


.. container:: table-row

   a
         |img-20|

   b
         Zielgruppe

   c
         Zielgruppen von Veranstaltungen werden nur dargestellt, schränken die
         Anmeldung aber nicht ein. Beispiele: Erwachsene, Jugendliche,
         Alleinerziehende, SoziologInnen.


.. container:: table-row

   a
         |img-21|

   b
         Zeitblock

   c
         Zeitblöcke werden  *innerhalb* eines Veranstaltungsdatensatzes
         angelegt, um beispielsweise abzubilden, dass ein Seminar Freitag 14-18
         Uhr und Samstag von 10-14 Uhr stattfindet.


.. container:: table-row

   a
         |img-22|

   b
         Veranstaltungsort

   c
         Veranstaltungsorte werden in der Listen- und Einzelansicht
         dargestellt.


.. container:: table-row

   a
         |img-23|

   b
         Fähigkeit

   c
         Dies sind Fähigkeiten, die den ReferentInnen zugeordnet werden. Diese
         werden im Front-end bisher noch nicht dargestellt.


.. container:: table-row

   a
         |img-24|

   b
         Unterbringungsmöglichkeit

   c
         Die Unterbringungsmöglichkeiten einer Veranstaltung stehen bei der
         Anmeldung zur Auswahl.


.. container:: table-row

   a
         |img-25|

   b
         Verpflegungsmöglichkeit

   c
         Die Verpflegungsmöglichkeiten einer Veranstaltung stehen bei der
         Anmeldung zur Auswahl.


.. container:: table-row

   a
         |img-26|

   b
         Veranstaltungsart

   c
         Jede Veranstaltung kann genau einer Veranstaltungsart zugeordnet
         werden, zum Beispiel Workshop, Abendkurs, Vortrag oder Repititorium.
         Die Veranstaltungsart wird in der Listen- und Einzelansicht
         dargestellt.


.. container:: table-row

   a
         |img-27|

   b
         Checkbox bei der Anmeldung

   c
         Dies sind Optionen, die bei der Anmeldung zur Auswahl stehen.


.. container:: table-row

   a
         |img-28|

   b
         Kategorie

   c
         Jede Veranstaltung kann mehreren Kategorien zugeordnet werden, zum
         Beispiel Methodenseminare, Fortbildungen und Prüfungskurse.


.. ###### END~OF~TABLE ######
