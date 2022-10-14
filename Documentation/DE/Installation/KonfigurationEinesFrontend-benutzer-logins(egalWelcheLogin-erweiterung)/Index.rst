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


Konfiguration eines Frontend-Benutzer-Logins (egal welche Login-Erweiterung)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Wählen sie eine Loginbox- und eine Frontendregistrierungsextension und
installieren und konfigurieren Sie diese. Auf meiner Internetseite
nutze ich die  *felogin*, aber Sie können
auch andere nutzen. Sie können die Frontendregistrierung auch
weglassen, wenn Sie nicht möchten, dass Frontendnutzer eigene Accounts
erstellen können.

Wenn ein Nutzer sich auf der Detailseite befindet und sich für eine
Veranstaltung registrieren möchte, so wird ihm ein Link zur Loginseite
angezeigt. Die angegebene URL beinhaltet eine Information, um den
Nutzer nach dem Login zur Veranstaltungsregistrierung
zurückzuschicken. Wichtig: Dieses Feature funktioniert nur mit
*felogin*!

Es besteht die Möglichkeit auf der Login-Seite den Titel und das Datum
der veranstaltung anzeigen zu lassen, für die der User sich
registrieren möchte.Dazu muss auf der Login-Seite eine content
element, mit dem Seminars Plugin eingefügt werden. Dort muss dann die
Ansicht auf Veranstaltungsüberschrift gestellt werden. Nun wird wenn
der User sich für eine Veranstaltung registrieren möchte, auf der
Login-Seite der Titel und das Datum der Veranstaltung angezeigt.
