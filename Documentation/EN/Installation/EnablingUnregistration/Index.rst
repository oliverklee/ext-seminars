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


Enabling unregistration
^^^^^^^^^^^^^^^^^^^^^^^

To enable users to unregister themselves from events (using the “my
events” page), you need two things:

#. **Unregistration deadline:** You either can set individual
   unregistration deadlines in the events for which unregistration should
   be possible, or you can set a global deadline for all events using
   plugin.tx\_seminars.unregistrationDeadlineDaysBeforeBeginDate.

#. **Waiting list:** By default, unregistration is only possible if an
   event is full and has some people on its waiting list. If you want to
   enable unregistration if the waiting list is empty (or if there is no
   waiting list), you can use
   plugin.tx\_seminars.allowUnregistrationWithEmptyWaitingList = 1.

