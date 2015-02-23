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


Anmeldungen verwalten
---------------------

Die Anmeldung für eine Veranstaltung ist bis zur Anmelde-Deadline der
entsprechenden Veranstaltung möglich. Falls eine Veranstaltung keine
solche Deadline hat, ist die Anmeldung bis zum Veranstaltungsbeginn
möglich.

Wenn ein eingeloggter Benutzer sich für ein Seminar anmeldet, passiert
Folgendes:

#. Es wird kontrolliert ob das gewählte Seminar noch Plätze frei hat und
   ob sich der Benutzer schon für dieses Seminar angemeldet hat (falls
   die Checkbox für Mehrfach-Anmeldung nicht aktiviert ist).

#. Der Benutzer kann seine zusätzlichen Infos angeben indem er die
   restlichen Felder ausfüllt.

#. Ein Anmeldungsdatensatz wird angelegt und die Verknüpfung zu den FE-
   Benutzerdaten erstellt. Im Backend wird die Statistik automatisch
   aktualisiert

#. Eine Bestätigungs-E-Mail wird an die E-Mail-Adresse des Benutzers
   gesendet (mit der E-Mail-Adresse des ersten Veranstalters als
   Absender). Wenn der Benutzer sich für ein Seminar angemeldet hat, dass
   noch nicht bestätigt wurde, wird ein Hinweis an die Mail angehängt,
   der ebend dieses besagt und darauf hinweist, dass der Benutzer noch
   eine E-Mail erhalten wird, wenn die Veranstaltung bestätigt wurde.

#. Eine Benachrichtigungs-E-Mail wird an  *alle* Veranstalter dieser
   Veranstaltung gesendet.

#. Ein zusätzliches E-Mail wird an den Organisator gesendet falls das
   Seminar die Mindestanforderungen zur Durchführung erfüllt hat oder
   falls das Seminar voll ist.

#. Eine Dankeschön-Meldung für den User erscheint auf der Website


.. toctree::
   :maxdepth: 5
   :titlesonly:
   :glob:

   AnmeldestatistikAnzeigen/Index
   AnmeldungenNdern/Index
   AbmeldungVonEinemSeminar/Index
   BezahlungErfolgt/Index
   LinkingToSingleSeminarRecords/Index
   RollenFrVeranstaltungen/Index
   VeranstaltungenVerffentlichenDieImFronend-editorErstelltWurden/Index
