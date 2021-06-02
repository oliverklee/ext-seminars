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


Das Plugin konfigurieren
^^^^^^^^^^^^^^^^^^^^^^^^

**Inkludieren Sie zuerst das statische Extension-Template
*MKFORMS - Basics (mkforms)* in
in Ihrem Seitentemplate unter “Include static (from extensions)”.**

**Inkludieren Sie danach das statische Extension-Template *Seminars* in
in Ihrem Seitentemplate.**
Wichtig ist, dass Sie dieses Template *nach* dem MKFORMS-Template einbinden.

Wenn Ihre Seite nicht ohnehin schon jQuery einbindet, binden Sie noch
folgendes statisches Template ein::

   MKFORMS JQuery-JS (mkforms)

Dann konfigurieren Sie das Plugin in Ihrem TS template setup oder in
den Plugin Flexforms. Die Eigenschaften sind in der Referenz
aufgeführt.

Bitte beachten Sie, dass wenn Sie Flexforms nutzen, Sie die
betreffenden Werte in allen relevanten Instanzen des Plugins setzen
müssen. Es reicht nicht aus, die Felder für die Onlineanmeldung in der
Seminarliste des Frontend Plugins zu setzen – Sie müssen diese Werte
in der Onlineanmeldung des Frontend Plugins ebenfalls setzen.

Sie können die folgende TypoScript-Setup-Vorlage benutzen, um alle
erforderlichen Werte für eine kleine Installation zu setzen:

::

   plugin.tx_seminars {
     # PID des Ordners, in dem Anmeldungen gespeichert werden
     attendancesPID =
   }

   # Übersetzungen der Texte in Mails und einige FE-Teilen kommen hierhin (das Beispiel ist für Deutsch)
   plugin.tx_seminars._LOCAL_LANG.de {
   }

   plugin.tx_seminars_pi1 {
     # PID des Ordners, das die Veranstaltungsdatensätze enthält
     pages =

     # PID der FE-Seite, die die Listenansicht enthält
     listPID =

     # PID der FE-Seite, die die Einzelansicht enthält
     detailPID =

     # PID der „Meine Veranstaltungen“-Seite
     myEventsPID =

     # PID der FE-Seite mit der Veranstaltungsanmeldung
     registerPID =

     # PID der FE-Seite mit dem Login bzw. onetimeaccount
     loginPID =

     # PID der FE-Seite, die man nach erfolgreicher Anmeldung zu einer Veranstaltung sehen soll
     thankYouAfterRegistrationPID =

     # PID der Seite, die man nach erfolgreicher Abmeldung von einer Seite sehen soll
     pageToShowAfterUnregistrationPID =
   }

   # Übersetzungen FE-spezifischen Texte kommen hierhin (das Beispiel ist für Deutsch)
   plugin.tx_seminars_pi1._LOCAL_LANG.de {
   }

   # hier können Sie Dinge wie die Anzahl der Veranstaltungen pro Seite etc. ändern
   plugin.tx_seminars_pi1.listView {
   }

Beachten Sie, dass die Benachrichtigungsemail für den Veranstalter and
die Listenansicht die Überschriften ebenfalls für leere Felder
anzeigen, während die Einzelansicht und die Benachrichtigungsemail für
die registrierten Teilnehmer die Überschrift für einige Eigenschaften
entfernen. (nicht alle, nur wo es sinnvoll ist).
