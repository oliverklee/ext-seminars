==================================
Upgrading from seminars 5.x to 6.x
==================================

The following steps are necessary to upgrade from seminars 5.x to 6.x:

#.  Upgrade to the latest version of seminars 5.x.
#.  Upgrade to the latest versions of feuserextrafields and oelib.
#.  Update the database schema and run all upgrade wizards.
#.  If you are using onetimaccount, switch the plugin to
    "One-time FE account creator without autologin".
#.  Upgrade to the latest version of onetimeaccount.
#.  If you are using the `allowRegistrationForStartedEvents` option, set a
    corresponding registration deadline for all future events for which you
    want to allow registration after the event has started. (This option
    has been removed in seminars 6.0.)
#.  Go through the venue records and make sure that the address field contains
    the full address (including ZIP code, city and country (if relevant)).
#.  Upgrade to seminars 6.x
#.  Update the database schema and run all upgrade wizards.
