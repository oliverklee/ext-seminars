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


Adding links to the  *terms & conditions* page
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

On the second registration page, you can have up to two “terms &
conditions” checkboxes. If you would like the checkbox labels to link
to the page containing the terms & conditions (or if you would like to
open that page as a pop-up), you can use some HTML for the checkbox
label. For example, this will add a link to the first terms &
conditions checkbox for a German site:

plugin.tx\_seminars\_pi1.\_LOCAL\_LANG.de.label\_terms = <a
href="/index.php?id=1106&amp;type=11" onclick="popup(this.href);
return false">Die Allgemeinen Gesch&auml;ftsbedingungen</a> habe ich
gelesen und erkenne sie hiermit an.

You can do the same for the  *label\_terms\_2* label.

