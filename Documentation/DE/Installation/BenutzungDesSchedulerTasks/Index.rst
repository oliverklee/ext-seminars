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


Benutzung des Scheduler-Tasks
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Die Seminars-Erweiterung stellt eine Scheduler-Task zur Verfügung, mit dem
Funktionen man von Erinnerungs-E-Mails versenden kann:

- wenn ein fest zugesagtes Seminar in Kürze anfängt

- wenn die Frist zum Absagen eines Seminars, das bisher weder fest
  zugesagt noch abgesagt ist, gerade abläuft

Die versendeten E-Mails enthalten einen lokalisierten Text und im
Anhang eine CSV-Datei mit der aktuellen Teilnehmerliste.

Die folgenden Dinge sind zu konfigurieren:

#. Richten Sie den Scheduler ein, wie es im Manual der Scheduler-Extension
   beschrieben ist.

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

#. Legen Sie eine Seminars-Scheduler-Task an und geben Sie die UID der Seite
   mit der Konfiguration an.


**CSV-Dateianhang**
"""""""""""""""""""

**Die E-Mails, die mit dem Scheduler-Task gesendet werden, können eine CSV-Datei
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


** Tägliche Zusammenfassung neuer Anmeldungen **
""""""""""""""""""""""""""""""""""""""""""""""""

Der Scheduler-Task kann auch eine (normalerweise tägliche) Zusammenfassung der
neuen Anmeldungen verschicken. Diese Funktionalität wird per TypoScript-Setup
im Namespace plugin.tx\_seminars.registrationDigestEmail konfiguriert und
aktiviert.

Die Mails werden in der Sprache erstellt, die für den Scheduler-BE-User als
Default-Sprache eingestellt ist.
