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


Das Firma-Feld in der Rechnungsadresse im Anmeldeformular von einem Input-Feld auf ein Textarea ändern
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Dies lässt sich per TS leicht ändern:

::

   plugin.tx_seminars_pi1.form.registration.step1.elements {
     company = renderlet:TEXTAREA
     company.custom = rows="3" cols="20"
   }
