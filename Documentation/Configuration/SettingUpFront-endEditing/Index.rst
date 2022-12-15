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

============================
Setting up front-end editing
============================

Only do this if you really trust your users to only enter serious
events and no fun or test records.

Setting up the front-end editing with the new plugin
====================================================

..  note::

    This is the new front-end editor that was introduced in seminars 4.2.0
    in order to replace the old mkforms-based front-end editor. This is what
    you should use for new installations for TYPO3 10 LTS and higher. (This
    feature is not available for TYPO3 9LTS.)

..  important::

    I plan to remove this feature in seminars 6 as nobody seems to be using
    front-end editing of events nowadays. If you are using it, please let me
    know in order for me to keep this feature.

#.  If you have not done so already, create a front-end user group for your
    editors.

#.  If you have not done so already, create a folder for the front-end-created
    events. (You can also use the general events folder if you do not want to
    store the front-end-created events separately.)

#.  Create a front-end page and **limit access to the front-end user group
    you created in step 1**. This is very important.

#.  On the page you've just created, create a plugin and set its type to
    *Front-end editor for events*.

#.  In the plugin flexform, set the the folder for in which the
    front-end-created events should be stored.

#.  That's it - you're done!

Setting up the legacy front-end editing
=======================================

..  important::

    The legacy front end editing is deprecated and will be removed in
    seminars 5.0. Please use the new front end editing instead.

Front-end users in the configured FE user group can edit an event
either if they are the owner of that event, or if they are a manager
for that event (or in the general “managers” front-end user group) and
front-end editing is enabled for managers.

#. Create a system folder where front-end created event records will be
   stored. If you like, you can also use your existing event records
   folder for that. Either way, note the PID of this system folder.

#. Create a front-end user group for the front-end users that are allowed
   to enter and edit event records in the front end. Write down the UID
   of that group.

#. Add all front-end users that should beallowed to enter and edit events
   to that group.

#. Create a page “Enter/edit events” and allow access exclusively to
   users of that front-end user group.

#. Add a Seminar Manager plug-in to that page and set its type to “Event
   Editor.”

#. In the tab “Front-end editing”, select the front-end group that is
   allowed to edit events. Alternatively, you can set this using the TS
   setup variable plugin.tx\_seminars\_pi1.eventEditorFeGroupID.

#. Select the system folder where the created events will be stored.
   Alternatively, you can set this using the TS setup variable
   plugin.tx\_seminars\_pi1.createEventsPID.

#. Select the the page that will be shown when an event has been saved.
   This can be the page with the user-entered events (which we will
   create in the next page) or a separate thank-you page. Alternatively,
   you can set this using the TS setup variable
   plugin.tx\_seminars\_pi1.eventSuccessfullySavedPID.

#. Create a page “Events which I have entered” (or “My events”) and allow
   access exclusively to users of that front-end user group.

#. Add a Seminar Manager plug-in to that page and set its type to “Events
   which I have entered”.

#. In the first tab, select the system folder where front-end-created
   events are stored as data source from where to fetch the event
   records.

#. In the second tab, you probably want to select “all events” as time-
   frame.

#. In the tab “Front-end editing”, select the front-end group that is
   allowed to edit events. Alternatively, you can set this using the TS
   setup variable plugin.tx\_seminars\_pi1.eventEditorFeGroupID.

#. Select the page with the event editor plug-in (that is the page which
   you have just created). Alternatively, you can set this using the TS
   setup variable plugin.tx\_seminars\_pi1.eventEditorPID.
