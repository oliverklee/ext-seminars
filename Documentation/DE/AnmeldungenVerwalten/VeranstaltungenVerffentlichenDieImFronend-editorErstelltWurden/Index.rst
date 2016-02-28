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


Veranstaltungen veröffentlichen, die im Fronend-Editor erstellt wurden
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Um den Veröffentlichungs-Workflow zu benutzen müssen einige
Einstellungen im Backend vorgenommen werden. In der Gruppe der
Frontend-Benutzer die eine Veranstaltung im Frontend-Editor
erstellen/bearbeiten können, müssen Veröffentlichungseinstellung
gesetzt werden. Diese kann folgende Werte enthalten

- sofort Veröffentlichen

- neue Veranstaltungen versteckten

- bearbeitete und neue Veranstaltungen verstecken

Nun muss in der Gruppe noch ein BE-Benutzer eingestellt werden, der
Freischaltmails bekommt. Hierbei ist es wichtig, dass der BE-Benutzer
eine gültige E-Mail Adresse hat.

Wenn diese Einstellungen vorgenommen wurden, werden je nach
Einstellung in der Gruppe neue oder auch berarbeitete Veranstaltungen
nach dem Anlegen/Bearbeiten automatisch versteckt und mit dem Status
„ausstehend“ versehen.

Wenn eine Veranstaltung versteckt wird, bekommt der BE-Benutzer, der
in der Gruppe eingestellt ist, eine E-Mail mit einem Freischaltlink
mit der die Veranstaltung veröffentlicht werden kann. Diese bekommt
dann den Status veröffentlicht und kann im Frontend angezeigt werden.
