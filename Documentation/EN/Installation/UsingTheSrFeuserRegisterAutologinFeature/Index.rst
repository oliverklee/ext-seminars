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


Using the sr\_feuser\_register autologin feature
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

If you use sr\_feuser\_register with autologin, you need to set the
following constants so this works:

::

   plugin.tx_srfeuserregister_pi1 {
     enablePreviewRegister = 0
     enableAdminReview = 0
     enableEmailConfirmation = 0
   }

In addition, you'll need to edit the TEMPLATE\_CREATE\_SAVED subpart
in the sr\_feuser\_register HTML template and remove this line:

::

   <input type="hidden" name="redirect_url" value="" />
