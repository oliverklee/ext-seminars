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


Linking to single seminar records
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

If you would like to link to the detailed description for a seminar
(from other seminar descriptions or from any other page), you can use
this format:

<URL of seminar listing page>?tx\_seminars\_pi1[showUid]=<UID of the
seminar>

For example, if the URL of the seminar listing page is
http://www.casebo.de/casebo-workshops.html and you would like to the
seminar with the UID 27, the complete URL to that seminar would be
this:

http://www.casebo.de/casebo-
workshops.html?tx\_seminars\_pi1[showUid]=27

(In the links from the list view, the URLs contain an addition
ampersand after the question mark, but that can be ignored.)
