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


Vorauswahl in der URL der Listenansicht
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Wenn Sie von anderen Seiten auf die Listenansicht verlinken, können
Sie über die URL eine Vorauswahl angeben (indem Sie die Variablen aus
dem Filterformular der Listenansicht benutzen):

::

   &tx_seminars_pi1[place][]=1
   &tx_seminars_pi1[event_type][]=3
   &tx_seminars_pi1[category]=1
   &tx_seminars_pi1[country][]=DE etc. (der ISO 3166 alpha2-Code in Großbuchstaben)
   &tx_seminars_pi1[city][]=Berlin
   &tx_seminars_pi1[language][]=EN (der ISO 639 alpha2-Code in Großbuchstaben)

Dies funktioniert auch in Kombination:

::

   &tx_seminars_pi1[event_type][]=3&tx_seminars_pi1[place][]=3
   &tx_seminars_pi1[place][]=1&tx_seminars_pi1[category]=1
