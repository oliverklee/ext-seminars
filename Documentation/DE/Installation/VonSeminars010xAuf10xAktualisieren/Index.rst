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


Von seminars 0.10.x auf 1.0.x aktualisieren
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**Eine Aktualisierung auf 1.0.x bringt viele neue Features, aber es
bedeutet auch einiges an Arbeit, wenn Sie mit angepassten HTML-
Templates arbeiten.**

Außerdem haben sich die meisten Klassennamen und Hook-Deklarationen geändert.

Am besten führen Sie die Aktualisierung zu einem Zeitpunkt durch, zu
dem keine neuen Anmeldungen zu erwarten sind, da während der
Aktualisierung Warnungen auf den Seiten zu sehen sein können.

#. Stellen Sie sicher, dass Sie PHP 5.5 oder 5.6 benutzen.

#. Stellen Sie sicher, dass Sie mindestens TYPO3 6.2.0 benutzen.

#. Deinstallieren sie vorübergehend seminars, onetimeaccount (falls es
   installiert ist) und alle Extensions, die Hooks oder XCLASSes von
   seminars benutzen.

#. Entfernen Sie die Extension ameos_formidable von Ihrem System.

#. Aktualisieren Sie oelib und static\_info\_tables auf die aktuelle
   Version.

#. Wenn Sie angepasste HTML-Template benutzen, machen Sie ein Diff
   zwischen den Originaltemplates und Ihren angepassten Templates, so
   dass Ihre Änderungen dokumentiert sind. (Um alle neuen Funktionen zu
   benutzen, benötigen Sie neue Templates. Außerdem werden Ihre alten
   Templates im Frontend wahrscheinlich fehlerhaft dargestellt werden.)
   Schalten Sie die angepassten Templates aus.

#. Aktualisieren Sie den Seminarmanager (und onetimeaccount, falls nötig)
   aus dem TER

#. Installieren Sie die seminars wieder.

#. Schalten Sie im Extensionmanager den automatischen Konfigurationscheck
   für den Seminarmanager ein.

#. Inkludieren Sie das statische Extension-Template
   *MKFORMS - Basics (mkforms)* in
   in Ihrem Seitentemplate unter “Include static (from extensions)”
   *oberhalb* des statischen seminars-Templates.

#. Wenn Ihre Seite nicht ohnehin schon jQuery einbindet, binden Sie noch
   folgendes statisches Template ein::
     MKFORMS JQuery-JS (mkforms)

#. Der CLI-Runner für den Cronjob wurde durch einen Scheduler-Task ersetzt.
   Falls Sie den Cronjob nutzen, löschen Sie bitte den Cronjob und legen Sie
   stattdessen einen Scheduler-Task an (mit derselben Page-UID wie beim Cronjob).

#. Führen Sie das Update-Skript der Extension im EM durch (falls verfügbar).

#. Leeren Sie die Verzeichnisse typo3temp/llxml/ und
   typo3conf/l10n/\*/seminars/ (falls diese bei Ihnen existieren).

#. Leeren Sie alle Caches.

#. Entfernen Sie die FORMidable-Cache-Dateien in
   typo3temp/ameos\_formidable.

#. Schauen Sie sich alle Frontend-Seiten an, die den Seminarmanager
   enthalten. Melden Sie sich für eine Veranstaltung an und prüfen Sie,
   dass alles noch korrekt funktioniert. Wenn Sie eine Warnung des
   automatischen Konfigurationschecks zu sehen bekommen, beseitigen Sie
   den Fehler in der Konfiguration, leeren den Frontend-Cache und laden
   die Seite neu.

#. Überprüfen Sie, dass die E-Mails an die Teilnehmer und die
   Veranstalter noch funktionieren und so aussehen, wie sie aussehen
   sollen.

#. Wenn Sie seminars-spezifische Hooks oder XCLASSEs benutzen, aktualisieren
   Sie diese anhand der neuen Klassen.

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
