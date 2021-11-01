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


Installing the extension
^^^^^^^^^^^^^^^^^^^^^^^^

**Conflicting extensions:** This extension cannot be installed if you
have one of the following extensions installed:

- sourceopt

**Required extensions:** This extension requires the following TYPO3
extensions to be installed beforehand:

- **static\_info\_tables**

- **oelib**

- **mkforms**

- **felogin** : If you want FE users to be able to log in so that they
  can register for events, you should also install a login extension
  like *felogin* .

- sr\_feuser\_register (optional): If you want FE users to create their
  accounts themselves, a self-registration extension like
  *sr\_feuser\_register* is recommended.

- onetimeaccount (optional): If you want users to be able to register
  for events without having to use a FE login, please use the
  *onetimeaccount* extension.

Then you can install this extension.

In the Extension Manager, there will be some options.  **Make sure to
save these options at least once (even if you don’t change them).**
The default values are good for starters.

- You can disable the automatic configuration check when this extension
  is installed and you have finished the  *complete* configuration for
  the BE and the FE. Disabling the automatic configuration check will
  improve performance a bit. When you upgrade to a newer version of the
  extension, you should enable it again and check whether there are any
  warnings on the FE pages with the plug-in on it.

- Disable “Select topic records from all pages” only in one of the
  following two cases:

  - if you have really a lot (like about 20 or more) event *topic* records

  - if you already have created lots of complete event records and you now
    are just starting to use the topic/date separation for events

- You can enable “Manual sorting of events” if you want to apply manual
  sorting (with the little up/down arrows) to events in the back end. By
  default, events are sorted by begin date. Note: This setting only
  applies to the back end and has no effect on the front end whatsoever.

- You can choose the “Format of e-mails for attendees” to send text
  e-mail, HTML e-mail or HTML e-mail if the user enabled it. So that
  HTML e-mail will be sent to the attendee of an event.

In your TS setup, please set config.language and config.locale\_all so
the extension will use the correct language in the front end.

