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


Benutzung des CLI (command line interface)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Die Seminars Erweiterung stellt ein CLI zur Verfügung mit dem
Funktionen via Kommandozeile genutzt werden können, zum Beispiel für
Cronjob. Das CLI kann zum Versenden von Erinnerungs-E-Mails an die
Veranstalter bei folgenden Ereignissen benutzt werden:

- wenn ein fest zugesagtes Seminar in Kürze anfängt

- wenn die Frist zum Absagen eines Seminars, das bisher weder fest
  zugesagt noch abgesagt ist, gerade abläuft

Die versendeten E-Mails enthalten einen lokalisierten Text und im
Anhang eine CSV-Datei mit der aktuellen Teilnehmerliste.

Die folgenden Dinge sind zu konfigurieren:

#. Im User Admin Tool im TYPO3 BE muss zunächst ein neuer BE Benutzer mit
   dem Benutzernamen “ **\_cli\_seminars** ” angelegt werden. Es sind
   keine weiteren Angaben für diesen Benutzer erforderlich, auch das
   einzugebende Passwort wird nicht wieder gebraucht.

#. Für die TS Setup-Konfiguration des CLI ist eine beliebige FE Seite
   auszuwählen/zu erstellen. Auf dieser Seite muss folgendes konfiguriert
   werden:

- Die Option “ *sendCancelationDeadlineReminder* ” muss auf 1 gesetzt
  werden um die Erinnerung an die Absagefrist zu aktivieren.

- Die Option “ *sendEventTakesPlaceReminderDaysBeforeBeginDate* ” muss
  auf die Anzahl Tage vor Seminarbeginn gesetzt werden, wann eine
  Erinnerung darüber, dass das Seminar stattfindet verschickt werden
  soll. Ist die Anzahl Tage 0, so ist diese Erinnerungs-E-Mail
  deaktiviert.

- Um die angehängte CSV-Datei anzupassen sind sie Optionen “
  *filenameForRegistrationsCsv* ”, “ *fieldsFromFeUserForEmailCsv* ”, “
  *fieldsFromAttendanceForEmailCsv* ” und “
  *showAttendancesOnRegistrationQueueInEmailCsv* ” relevant. Weitere
  Informationen über diese Optionen befinden sich im Abschnitt über CSV-
  Dateianhang.

#. In der Cronjob-Datei startet das folgende Kommando das CLI-Skript:
   **/[** absoluter Pfad derTYPO3-Installation
   **]/typo3/cli\_dispatch.phpsh seminars [** UID der Seite mit der
   Konfiguration **]**


**CSV-Dateianhang**
"""""""""""""""""""

**Die E-Mails die mit dem CLI gesendet werden, können eine CSV-Datei
als Anhang enthalten, welche die Anmeldung, der zur Mail zugehörigen
Veranstaltung enthält. Um diese Datei zu modifizieren benutzen Sie
bitte die folgenden Optionen:**

- “ *fieldsFromAttendanceForEmailCsv* ” und “
  *fieldsFromFeUserForEmailCsv* ” bestimmen die Felder, die in der CSV-
  Datei exportiert werden. Bitte beachten Sie, dass immer zuerst die
  Daten der Registrierung und danach erst die Daten des Benutzers in der
  CSV-Datei stehen.

- “ *filenameForRegistrationsCsv* ” bestimmt den Dateinamen der
  angehängten CSV-Datei.

- “ *showAttendancesOnRegistrationQueueInEmailCsv* ” bestimmt, ob
  Registrierungen auf der Warteliste ebenfalls mit exportiert werden
  sollen oder nicht.
