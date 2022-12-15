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


Publishing events created in the front-end editor
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Depending on the publish settings in the editors group, you can use a
publishing workflow for events that are created in the FE-editor. To
establish a workflow you need to set the following configurations:

#. set the publish settings of the editor group in the backend. Possible
   settings are:

   - publish immediately

   - hide newly created records

   - hide edited and new records

#. Add a reviewer to the editor group. This reviewer must be a back-end
   user with a valid e-mail address.

After setting this, depending on the publish settings of the editor
group, events which will be created in the front-end event editor will
be hidden by default and have the status 'pending.' In this case the
reviewer will receive an e-mail with a publishing link when the event
is hidden by the FE-editor. This mail contains a link to publish this
event. When the event is published it is no longer hidden, and can be
seen by all website visitors.
