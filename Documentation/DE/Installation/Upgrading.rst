=============================================================================
Die Extension zusammen mit TYPO3 und PHP über mehrere Versionen aktualisieren
=============================================================================

Empfohlene Upgrade-Reihenfolge mit allen beteiligten Extensions
===============================================================

Dies ist die empfohlene Aktualisierungsreihenfolge, um den zahlreichen
Abhängigkeiten zu PHP- und TYPO3-Versionen sowie der Extensions untereinander
gerecht zu werden. Die Komponente, die im jeweiligen Schritt zu aktualisieren
ist, ist fett markiert.

#. PHP 7.0, TYPO3 8.7, seminars 3.4, oelib 3.6.1, mkforms 9.5.4,
   rn\_base 1.11.4, static\_info\_tables 6.8.0
#. **PHP 7.2**, TYPO3 8.7, seminars 3.4, oelib 3.6.1, mkforms 9.5.4,
   rn\_base 1.11.4, static\_info\_tables 6.8.0
#. PHP 7.2, TYPO3 8.7, seminars 3.4, oelib 3.6.1, mkforms 9.5.4,
   **rn\_base 1.13.2**, static\_info\_tables 6.8.0
#. PHP 7.2, **TYPO3 9.5**, seminars 3.4, oelib 3.6.1, mkforms 9.5.4,
   rn\_base 1.13.2, static\_info\_tables 6.8.0
#. PHP 7.2, TYPO3 9.5, seminars 3.4, oelib 3.6.1, mkforms 9.5.4,
   rn\_base 1.13.2, **static\_info\_tables 6.9.5**
#. PHP 7.2, TYPO3 9.5, **seminars 4.0**, oelib 3.6.1, mkforms 9.5.4,
   rn\_base 1.13.2, static\_info\_tables 6.9.5
#. PHP 7.2, TYPO3 9.5, seminars 4.0, **oelib 4.0*, **mkforms 10.0.0**,
   rn\_base 1.13.2, static\_info\_tables 6.9.5
#. PHP 7.2, TYPO3 9.5, **seminars 4.1**, oelib 4.0, mkforms 10.0.0,
   rn\_base 1.13.2, static\_info\_tables 6.9.5
#. PHP 7.2, **TYPO3 10.4**, seminars 4.1, oelib 4.0, mkforms 10.0.0,
   rn\_base 1.13.2, static\_info\_tables 6.9.5
#. **PHP 7.4**, TYPO3 10.4, seminars 4.1, oelib 4.0, mkforms 10.0.0,
   rn\_base 1.13.2, static\_info\_tables 6.9.5

Wenn Sie diese Reihenfolge einhalten, sollten Sie alle beteiligten Extensions
zu jeder Zeit installiert lassen können.

Zu beachtende Dinge beim Upgrade von seminars 3.x zu 4.x
========================================================

Die TypoScript-Dateien wurde von :file:`*.txt` zu :file:`*.typoscript`
umbenannt. Falls Sie diese Dateien direkt referenzieren, passen Sie bitte
Ihre Referenzen entsprechend an.

Alle PHP-Klassen benutzen jetzt Namespaces. Falls Sie XCLASSes benutzen,
passen Sie diese bitte entsprechend an.

Die Hook-Interfaces wurden auf die genamespaceten Klassen umgestellt, und die
deprecateten Hooks wurden entfernt. Bitte passen Sie Ihre Hooks entsprechend an.

Alle Bilder und Anhänge wurden auf FAL umgestellt. Bitte benutzen Sie die
Upgrade-Wizards, um Ihre Daten automatisch zu migrieren.

Das Feature zum Upload von Bildern und Anhängen im FE-Editor wurde entfernt.
Falls Sie ein eigenes HTML-Template für den FE-Editor benutzen, entfernen
Sie bitte die entsprechenden Subparts aus Ihrem HTML-Template.
