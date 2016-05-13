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


Erstellen der Systemordner
^^^^^^^^^^^^^^^^^^^^^^^^^^

Zusätzlich zum  *website\_users\_folder* müssen Sie einige
Systemordner erstellen, um die Records zu speichern, die von dieser
Extension benötigt werden.

Wenn Sie nicht viele Veranstaltungen haben und Sie den Überblick auch
behalten, wenn die Veranstaltungsdaten und die
Teilnehmerregistrierungen jeweils auf einer Seite stehen, dann können
Sie eine Minimalstruktur wie diese hier benutzen:

|img-6|  *Illustration 6: minimale Ordnerstruktur*

Wenn Sie nur eine Seite mit einer Listenansicht der Veranstaltungen haben, können Sie alle aktuellen Veranstaltungen in einem Systemordner speichern:|img-7|  *Illustration 7: alle aktuellen Veranstaltungen im selben
Ordner*

Wenn es in Ordnung ist, dass die Teilnehmerregistrierungen für alle
Veranstalter in einem Systemordner gespeichert werden oder wenn Sie
nur einen Veranstalter haben, so benötigen Sie nur einen Systemordner
für die Anmeldungen:

|img-8|  *Illustration 8: Anmeldungen für alle Veranstalter im selben
Ordner*

Die folgende Systemordnerstruktur wird für eine vollständige
Installation empfohlen, in der eine größere Anzahl von Veranstaltungen
und verschiedenen Veranstaltern existiert, wobei die Veranstalter ihre
Teilnehmer unabhängig verwalten können sollen:

|img-9|  *Illustration 9: vollständige, sehr große Installation*

Wenn Sie diese Ordner außerhalb der Root Seite (Die Rootseite müsste
ein Template haben) erstellen, müssen trotzdem ein Template für diese
Ordner erstellen und ein statisches Extension Template (“include
static (from extensions)”) in dieses Template einbinden, da sonst das
BE-Modul nicht in der Lage ist, die Standardkonfiguration der
Extension zu nutzen. (bsp.: Das Datum- und Zeitformat würde im BE-
Modul nicht angezeigt werden)
