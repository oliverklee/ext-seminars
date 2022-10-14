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


Die Erweiterung installieren
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**Inkompatible Erweiterungen:** Diese Erweiterung kann nicht
installiert werden, wenn eine der folgenden Erweiterungen installiert
ist:

- sourceopt

**Benötigte Erweiterungen:** Diese Erweiterung benötigt die folgenden
TYPO3- Erweiterungen auf Ihrem Server:

- **static\_info\_tables**

- **oelib**

- **mkforms**

- felogin: Wenn Sie möchten, dass sich Frontend-Benutzer einloggen und
  für Veranstaltungen anmelden können, sollten Sie eine Login-
  Erweiterung benutzen (oder Sie verwenden die Loginfunktionen des
  Systems).

- onetimeaccount (optional): Wenn Sie möchten, dass sich die
  TeilnehmerInnen zu Veranstaltungen anmelden können, ohne dass diese
  sich vorher einen Frontend-Benutzer-Account einrichten müssen, können
  Sie die *onetimeaccount* -Erweiterung benutzen.

Anschließend können Sie die Erweiterung installieren.

**Ende des überarbeiteten/übersetzen Teils**

Im Extension-Manager gibt einige Konfigurationseinstellungen.  **Bitte
speichern Sie diese Einstellungen einmal (auch wenn Sie sie nicht
ändern).** Die Standardwerte sind für den Einstieg okay.

- Sie können die automatische Konfigurationsprüfung deaktivieren, wenn
  die Extension installiert und die vollständige Konfiguration für das
  Backend und Frontend abgeschlossen wurde. Ein deaktivieren der
  automatischen Konfiguratiopnsprüfung wird die Geschwindigkeit ein
  bisschen verbessern. Wenn Sie die Extension auf eine neuere Version
  upgraden sollten Sie die automatische Konfigurationsprüfung wieder
  aktivieren und überprüfen, ob Warnungen auf Seiten erscheinen, auf
  denen das Plugin verwendet wird.

- Deaktivieren Sie“Select topic records from all pages” ausschließlich
  in einem der folgenden Fälle:

  - wenn Sie eine größere Anzahl (bspw. Mehr als 20) Veranstaltungen haben

  - wenn Sie bereits eine große Anzahl an vollständigen Veranstaltungen
    haben und jetzt nur damit beginnen, die Thema/Datum Trennung für
    Veranstaltungen zu verwenden.

- Sie können “Manual sorting of events” aktivieren, wenn Sie eine
  manuelle Sortierung (mit den kleinen aufwärts/abwärts Pfeilen) der
  Veranstaltungen im Backend wünschen. Als Standard werden
  Veranstaltungen nach dem Startdatum sortiert. Hinweis: Diese
  Konfiguration gilt nur für das Backend und hat keine Auswirkung auf
  das Frontend oder andere Bereiche.

- Sie können bei „Format of e-mails for attendees“ auswählen ob Sie
  E-Mails im Text- oder HTML-Format verschicken wollen, oder ob das
  Benutzerabhängig gemacht werden soll.

Bitte setzen Sie in Ihrem TS-Setup außerdem config.language und
config.locale\_all, damit die Extension im Frontend die korrekte
Sprache benutzt.
