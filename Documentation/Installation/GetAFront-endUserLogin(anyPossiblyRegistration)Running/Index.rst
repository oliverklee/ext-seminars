.. ==================================================
.. DEFINE SOME TEXTROLES
.. --------------------------------------------------
.. role::   underline
.. role::   typoscript(code)
.. role::   ts(typoscript)
   :class:  typoscript
.. role::   php(code)

.. _login-page:

=======================
Setting up a login page
=======================

Some of this extension's features require a FE user to be logged in, for example
the event registration, the event management in the FE or access to the attendee
lists. If you are not using any of these feature, you can skip this chapter.

Selecting which login plugins to use
====================================

There are two possible scenarios for the login page:

1.  You have (recurring) visitors that log in to your site in order to register
    for events, to unregister again, or to manage events.
    In this case, you will need regular login form using the **felogin**
    extension that comes with the TYPO3 Core.
    Make sure to enable the "Redirect defined by GET/POST parameters" in the
    login plugin settings.

2.  You want people to register for your events, but you do not want bother them
    with having to create an account. In this case, you will need the
    **onetimeaccount** extension.

If you want to cover both scenarios, you can also use both plugins (on the same
page.)

Allowing users to create an account with a double-opt-in process before they can
log in is *not*, supported, though. (In that case, the redirect parameter to
the registration page in the link to the login page will get lost.)

Selecting which onetimeaccount plugin to use
============================================

The onetimeaccount extension comes with two plugins:

One-time FE account creator with autologin
------------------------------------------

Do not use this version anymore. (It was needed for the legacy seminars
registration form that got removed in seminars 5.0.

One-time FE account creator without autologin
---------------------------------------------

Use this version with the the rewritten seminars registration form that
was introduced in seminars 4.3.0.
