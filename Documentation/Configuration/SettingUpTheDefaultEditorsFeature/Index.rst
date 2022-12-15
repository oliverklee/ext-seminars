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


Setting up the default editors feature
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You can assign editors (front-end users) to each single event. These
editors are allowed to see all registrations for the events where
he/she is manually added as an editor.

If you want to allow a group of editors to see the registrations of
all events, you can add all those editors to a group. Just add the UID
of that group to the TS configuration
*plugin.tx\_seminars\_pi1.defaultEventVipsFeGroupID* .

After clearing the cache, all members of that group will see all
events on their “my editor events” page, and will be able to see the
registrations list of all those events.

You can also set the group's uid in the flexform configuration or the
plugin. But you will need to set it for each plug-in(on every page).
It's easier to set it via TypoScript setup on a global page.
