=============================================================================
The extension zusammen mit TYPO3 und PHP über mehrere Versionen aktualisieren
=============================================================================

Dies ist die empfohlene Aktualisierungsreihenfolge, um den zahlreichen
Abhängigkeiten zu PHP- und TYPO3-Versionen sowie der Extensions untereinander
gerecht zu werden. Die Komponente, die im jeweiligen Schritt zu aktualisieren
ist, ist fett markiert.

To accommodate the multiple dependencies to PHP and TYPO3 versions as well
as between the extensions, this is the recommended upgrade path (with the
component to upgrade in each step marked bold):

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
