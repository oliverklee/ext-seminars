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

    This is the new front-end editor that was introduced in seminars 4.2.0.

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
