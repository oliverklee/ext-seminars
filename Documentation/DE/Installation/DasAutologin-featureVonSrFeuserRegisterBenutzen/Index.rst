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


Das Autologin-Feature von sr\_feuser\_register benutzen
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Wenn Sie sr\_feuser\_register mit dem Autologin-Feature benutzen,
benötigen Sie folgende Einstellungen in den Konstanten, damit alles
funktioniert:

::

   plugin.tx_srfeuserregister_pi1 {
     enablePreviewRegister = 0
     enableAdminReview = 0
     enableEmailConfirmation = 0
   }

Weiterhin ist es wichtig, dass Sie das HTML-Template von
sr\_feuser\_register bearbeiten und im Subpart TEMPLATE\_CREATE\_SAVED
die folgende Zeile löschen:

::

   <input type="hidden" name="redirect_url" value="" />
