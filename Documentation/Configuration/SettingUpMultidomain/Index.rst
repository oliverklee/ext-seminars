.. ==================================================
￼.. FOR YOUR INFORMATION
￼.. --------------------------------------------------
￼.. -*- coding: utf-8 -*- with BOM.
￼
￼.. ==================================================
￼.. DEFINE SOME TEXTROLES
￼.. --------------------------------------------------
￼.. role::   underline
￼.. role::   typoscript(code)
￼.. role::   ts(typoscript)
￼   :class:  typoscript
￼.. role::   php(code)
￼
￼
￼Multidomain einrichten
￼^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
￼
TODO - Auf Englisch übersetzen
￼Bei der Verwendung von Seminars für mehrere Domain in einem TYPO3 Projekt erscheinen standardmäßig alle Kategorien, Zahlungsarten, Orte, Veranstalter, etc., die irgendwo im Projekt zu finden sind. Werden nun mehrere Homepages in einem Projekt betreut, können sowohl die anzuzeigenden Einträge, sowohl in der Bearbeitung eines Seminars, als auch in der Flexform beim Anlegen eines Seiteninhaltselements eingeschränkt werden.
￼
￼Dazu können im Seiten-TS folgende Werte auf die jeweilige UID des Systemordners gesetzt werden, in dem die Daten zu finden sind. In folgendem Beispiel befinden sich alle Datensätze im Ordner mit der UID 384. Bei den Kategorien wird mit dem Platzhalter % nach wie vor alle im Projekt vorkommenden Kategorien aufgelistet:
￼
￼::
￼
￼   TCEFORM.tx_seminars_seminars.categories.PAGE_TSCONFIG_STR=384
￼   TCEFORM.tx_seminars_seminars.event_types.PAGE_TSCONFIG_STR=384
￼   TCEFORM.tx_seminars_seminars.sites.PAGE_TSCONFIG_STR =384
￼   TCEFORM.tx_seminars_seminars.lodgings.PAGE_TSCONFIG_STR=384
￼   TCEFORM.tx_seminars_seminars.foods.PAGE_TSCONFIG_STR=384
￼   TCEFORM.tx_seminars_seminars.speakers.PAGE_TSCONFIG_STR=384
￼   TCEFORM.tx_seminars_seminars.checkboxes.PAGE_TSCONFIG_STR=384
￼   TCEFORM.tx_seminars_seminars.payment_methods.PAGE_TSCONFIG_STR =384
￼   TCEFORM.tx_seminars_seminars.organizers.PAGE_TSCONFIG_STR=384
￼   TCEFORM.tx_seminars_seminars.target_groups.PAGE_TSCONFIG_STR=384

￼   
￼   TCEFORM.tt_content.pi_flexform.FLEXFORM-EVENTTYPES-PID=384
￼   TCEFORM.tt_content.pi_flexform.FLEXFORM-CATEGORIES-PID=384
￼   TCEFORM.tt_content.pi_flexform.FLEXFORM-SITES-PID=384
￼   TCEFORM.tt_content.pi_flexform.FLEXFORM-ORGANIZERS-PID=384
}