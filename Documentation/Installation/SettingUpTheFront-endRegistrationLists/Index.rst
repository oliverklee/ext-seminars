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


Setting up the front-end registration lists
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

This feature allows front-end users who have signed up for an event to
see who has signed up for that event as well, e.g., for forming car
pools or for coordination before the event takes place. In addition,
this allows so-called editors (e.g., speakers or organizers of that
event) to see that list as well. Both features are disabled by
default. **When using this feature, make sure that this complies with
your privacy policy!**

Both lists are set up separately. Even if you useboth lists, they need
to be set up separately.

You can enter a list of FE user field names that will be displayed in
the registration lists using the TS setup variable
plugin.tx\_seminars\_pi1.showFeUserFieldsInRegistrationsList. The
default is to only display the attendees' names.


Setting up the front-end registration lists for attendees
"""""""""""""""""""""""""""""""""""""""""""""""""""""""""

Please note that there is no fine-grained access rights system: Either
you allow all attendees to view the registration lists for all events
for which they have signed up, or you don't.

#. If there is no “my events” page yet, create one. This page will show
   all events for which a FE user has signed up.

   #. Add a new page.

   #. Set the page access to “show at any login.”

   #. Add a new content element “General Plugin.”

   #. Set the element's plug-in type to “Seminar Manager,” set it to display
      the “my events” list and set the element's starting point to your
      SysFolder(s) with the event records. You'll probably want to also set
      the time-frame for this list to “all events” instead of the default
      value “current and upcoming events.”

#. Now add a second page for the registration lists (preferably a sub
   page of the “my events” page), set it to not appear in the menu and
   set the page access to “show at any login.”

   #. Add a new content element “General Plugin.”

   #. Set the element's plug-in type to “Seminar Manager” and set it to
      display the “list of registrations (for attendees).”

#. Now return to the page with the “my events” list and edit that content
   element again.

   #. Under “Page that contains the list of registrations (for attendees):”,
      select the page you've just created.

#. If you would like the registration lists to be linked from the normal
   list view, edit the seminar list and also select the page with the
   registrations list under “Page that contains the list of registrations
   (for attendees):”.


Setting up the front-end registration lists for managers
""""""""""""""""""""""""""""""""""""""""""""""""""""""""

Please note that this feature has a rather fine-grained access right
system: For each event, you can specify which FE users should be
allowed to view the registration lists of that particular event.

#. Create a “editors” FE-user group.

#. Edit the events for which some FE users should be allowed to view the
   registration lists. Add those FE users in the section“Front-end users
   that are allowed to see the list of registrations” of the
   corresponding event records. For example, you could allow the speakers
   or the organizers to see the registrations list. In addition, add the
   corresponding FE users to the FE user group “editors.”

#. Set up a “my editable events” page. This page will list exactly those
   events for which that particular FE user is set as an editor.

   #. Add a new page.

   #. Set the page access to “editors.”

   #. Add a new content element “General Plugin.”

   #. Set the element's plug-in type to “Seminar Manager,” set it to display
      the “my editable events” list and set the element's starting point to
      your SysFolder(s) with the event records. You'll probably want to also
      set the time-frame for this list to “all events” instead of the
      default value “current and upcoming events.”

#. Now add a second page for the registration lists (preferably a sub
   page of the “my events” page), set it to not appear in the menu and
   set the page access to “editors.”

   #. Add a new content element “General Plugin.”

   #. Set the element's plug-in type to “Seminar Manager” and set it to
      display the “list of registrations (for editors).”

#. Now return to the page with the “my events” list and edit that content
   element again.

   #. Under “Page that contains the list of registrations (for editors):”,
      select the page you've just created.

#. If you would like the registration lists to be linked from the normal
   list view, edit the seminar list and also select the page with the
   registrations list under “Page that contains the list of registrations
   (for editors):”. Please note that in case a FE user is both an
   attendee and an editor for an event, the link to the registration list
   for editors will take precedence.
