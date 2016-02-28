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


Eine Kategorienliste anlegen
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Die Kategorienliste zeigt alle Kategorien, für die es in den
ausgewählten Systemordnen und im gewählten Zeitraum Veranstaltungen
gibt. Wenn Sie Ihre Veranstaltungen keinen Kategorien zugeordnet
haben, wird die Kategorienliste daher leer sein.

Die Namen der Kategorien sind mit der Listenansicht verlinkt,
gefiltert nach der Kategorie (mit anderen Worten: Es werden nur
Veranstaltungen mit der ausgewählten Kategorie angezeigt).

Diese Anleitung geht davon aus, dass Sie schon eine Listenansicht für
Ihre Veranstaltungen angelegt haben.

#. Legen Sie eine Frontend-Seite an.

#. Fügen Sie Sie ein Seminarmanager-Plugin hinzu und setzen Sie den Type
   auf „Kategorienliste“.

#. Wählen Sie die Seite aus, auf der Ihre Listenansicht liegt (diese
   Seite wird das Ziel der verlinkten Kategorientitel sein).

#. Optional: Wählen Sie die Systemordner (plus Rekursionstiefe) aus, in
   den die Veranstaltungsdatensätze liegen, deren Kategorien sie anzeigen
   möchten. Wenn Sie hier nichts auswählen, werden die Kategorien alle
   Veranstaltungen von allen Systemordnern angezeigt.

#. Optional: Wählen Sie den Zeitraum aus, aus dem Veranstaltungen
   berücksichtigt werden sollen. Wenn Sie hier nichts auswählen, werden
   laufenden und zukünftige Veranstaltungen berücksichtigt.

#. Speichern und schließen Sie das Plugin.
