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


Setting up the countdown
^^^^^^^^^^^^^^^^^^^^^^^^

The countdown will show the time left (in words) until the next event
starts. Only upcoming events with a begin date/time will be selected
for this. If no matching event is found, a message will be shown
instead.

To set this up, follow these easy steps:

#. At the page/column where you want the countdown to be shown, add a new
   “Seminar Manager”-plug-in content element.

#. In the settings of this new content element, select “Countdown to the
   next event” from the “what to display” dropdown list.

#. You can change the visual appearance by changing the CSS or the HTML
   template. But please don't change them in the extension's directory
   (or all changes will be lost upon the next update of the extension).
