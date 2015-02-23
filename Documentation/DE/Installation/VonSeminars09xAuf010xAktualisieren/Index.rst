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


Von seminars 0.9.x auf 0.10.x aktualisieren
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**Eine Aktualisierung auf 0.10.x bringt viele neue Features, aber es
bedeutet auch einiges an Arbeit, wenn Sie mit angepassten HTML-
Templates arbeiten.**

Am besten führen Sie die Aktualisierung zu einem Zeitpunkt durch, zu
dem keine neuen Anmeldungen zu erwarten sind, da während der
Aktualisierung Warnungen auf den Seiten zu sehen sein können.

#. Stellen Sie sicher, dass Sie mindestens PHP 5.3 benutzen.

#. Stellen Sie sicher, dass Sie mindestens TYPO3 4.5.0 benutzen.

#. Aktualisieren Sie oelib und static\_info\_tables auf die aktuelle
   Version.

#. Wenn Sie angepasste HTML-Template benutzen, machen Sie ein Diff
   zwischen den Originaltemplates und Ihren angepassten Templates, so
   dass Ihre Änderungen dokumentiert sind. (Um alle neuen Funktionen zu
   benutzen, benötigen Sie neue Templates. Außerdem werden Ihre alten
   Templates im Frontend wahrscheinlich fehlerhaft dargestellt werden.)
   Schalten Sie die angepassten Templates aus.

#. Wenn Sie den alten Hook für die Einzelansicht benutzen, ändern Sie
   Ihren Code, so dass er stattdessen den neuen Hook benutzt.

#. Aktualisieren Sie den Seminarmanager aus dem TER und führen Sie die
   Datenbank-Aktualisierungen durch.

#. Schalten Sie im Extensionmanager den automatischen Konfigurationscheck
   für den Seminarmanager ein.

#. Leeren Sie die Verzeichnisse typo3temp/llxml/ und
   typo3conf/l10n/\*/seminars/ (falls diese bei Ihnen existieren).

#. Leeren Sie alle Caches.

#. Entfernen Sie die Temp-Cache-Dateien in typo3conf (entweder manuell
   oder via extdeveval).

#. Entfernen Sie die FORMidable-Cache-Dateien in
   typo3temp/ameos\_formidable.

#. Öffnen Sie im TYPO3-Backend alle Inhaltselemente, die Sie für die
   Veranstaltungs-Einzelansicht benutzen. Ändern Sie in den Flexforms das
   „Was soll angezeigt werden“-Drop-down von „Veranstaltungsliste“ auf
   „Veranstaltungs-Einzelansicht“.

#. Schauen Sie sich alle Frontend-Seiten an, die den Seminarmanager
   enthalten. Melden Sie sich für eine Veranstaltung an und prüfen Sie,
   dass alles noch korrekt funktioniert. Wenn Sie eine Warnung des
   automatischen Konfigurationschecks zu sehen bekommen, beseitigen Sie
   den Fehler in der Konfiguration, leeren den Frontend-Cache und laden
   die Seite neu.

#. Überprüfen Sie, dass die E-Mails an die Teilnehmer und die
   Veranstalter noch funktionieren und so aussehen, wie sie aussehen
   sollen.

#. Viele Klassen wurden verschoben oder umbenannt. Wenn Sie mit XCLASSes
   arbeitet, sollten Sie diese jetzt anpassen.

#. Schauen Sie sich die neuen Felder in den Veranstaltungsdatensätzen an
   und entscheiden Sie, welche davon Sie nutzen möchten.

#. Spielen Sie mit den Konfigurationen hideColumns, hideFields,
   showRegistrationFields herum.

#. Wenn alles funktioniert, schalten Sie den automatischen
   Konfigurationscheck im Extensionmanager wieder aus.

#. Das HTML-Template für die Registration kann nicht mehr per flexforms
   gesetzt werden.

#. Falls Sie angepasste HTML-Templates benutzen: Machen Sie Kopien der
   Originaltemplates, wenden das Diff an, schalten Sie die angepassten
   Templates wieder ein und testen Sie sie.

#. Und fertig! Oder Sie können jetzt mit den neuen Features herumspielen
   ...
