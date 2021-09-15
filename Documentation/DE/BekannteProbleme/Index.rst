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


Bekannte Probleme
-----------------

- Viele Ideen für diese Extension sind noch nicht umgesetzt. Sie können
  gerne helfen oder die weitere Entwicklung sponsern.

- Die Veranstaltungszeiten werden ohne Einheit dargestellt, zum Beispiel
  „17:00“ statt „17:00 h“.

- Alle Anmeldungen (sowohl bezahlt als auch nicht bezahlt) werden bei
  den Statistiken gezählt. Dies wird in einer späteren Version dieser
  Extension konfigurierbar sein.

- In manchen Fällen kann die Listenansicht im Front-end leer sein. Tun
  Sie dann Folgendes:

- Überprüfen Sie, dass alle Veranstaltungen im eingestellten
  Zeitabschnitt liegen (in der Standardeinstellung zukünftige und gerade
  laufende Veranstaltungen). Veranstaltungen ohne Anfangstermin werden
  dabei als zukünftige Veranstaltung angesehen.

- Es funktioniert nicht, die Veranstaltungsliste (oder die
  Einzelansicht) auf derselben Seite mit der Veranstaltungsanmeldung zu
  haben (Sie werden dann eine Fehlermeldung sehen). Tun Sie Folgendes:

  - Legen Sie beide Plug-ins auf getrennten Seiten ab und setzen Sie
    plugin.tx\_seminars\_pi1.listPID und
    plugin.tx\_seminars\_pi1.registerPID.

- **Alle nichtleeren Werte in den Flexforms überschreiben die
  entsprechenden Werte im TS-Setup. Leere Werte in den Flexforms werden
  dabei ignoriert, und das Plug-in benutzt dann weiter den Wert aus dem
  TS-Setup.**

- Nachdem Sie auf eine neue Version dieser Extension aktualisiert haben,
  sollten Sie auf jeden Fall alle Caches löschen, damit sich diese
  Extension nicht seltsam verhält.

- Die Suche in der Listenansicht deckt so ziemlich alles ab, was in der
  Detailansicht sichtbar ist (mit Ausnahme der Zahlungsarten). Dies ist
  so beabsichtigt.

- Sortieren in den Front-end-Listen funktioniert mit MySQL < 4.1 nicht
  richtig.

- Manche Benutzer haben von Problemen mit RealURL berichtet (vor allem
  mit dem Internet Explorer) bezüglich nicht funktionierenden
  Weiterleitungen zur Danke-Seite oder doppelten Anmeldungen
  (Fehlerbericht). Wenn diese Probleme bei Ihnen auftreten, schalten Sie
  bitte realURL für die Anmeldeseite und die Danke-Seite aus (und
  ermutigen Sie Ihren Kunden, auf Mozilla Firefox umzusteigen).

- Wenn die maximale Upload-Dateigröße in PHP auf einen niedrigeren Wert
  gesetzt ist, als in TYPO3 konfiguriert ist, wird bei der Bearbeitung
  von Veranstaltungen in FE keine Fehlermeldung angezeigt.

- Der Front-end-Editor funktioniert mit MySQL/MariaDB nicht im Strict-Mode.
  Deswegen ist es notwendig, `STRICT_TRANS_TABLES` aus `sql_mode` zu entfernen:

.. code-block:: ini

   # This is required for the seminars FE editor to graciously convert "" to 0
   # for integer columns (which is a shortcoming of the "mkforms" extension).
   sql_mode=ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION
