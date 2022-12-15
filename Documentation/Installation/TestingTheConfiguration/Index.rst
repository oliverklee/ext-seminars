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


Testing the configuration
^^^^^^^^^^^^^^^^^^^^^^^^^

This extension has an automatic configuration checking feature which
will check pretty much all configuration settings of this extension
for sanity. If it has found anything that needs to be fixed, it will
display a big red box with a message. This message will contain
information about the following things:

- what that particular setting is about,

- which values are allowed, and

- which values are incorrect.

To make sure that your configuration is correct, please log in as a
front-end user and visit all of your pages that contain the Seminar
Manager plugin-in. In the back end, please visit the back-end module.

The configuration check slightly decreases the performance of this
extension. When your configuration is finished and approved by the
checking feature, you can disable the feature in the extension
manager.

**Note:** This feature still is pretty new and edgy. If you think that
a particular warning message isn't correct (or you think that a a
particular check is missing), please take a minute and file a bug in
the bug tracker.
