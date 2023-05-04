.. include:: /Includes.rst.txt

.. _routing-configuration:

=====================
Routing configuration
=====================

Nice URLs for the registration form
===================================

Add this to your site configuration file:

.. code-block:: yaml

  routeEnhancers:
    EventRegistration:
      type: Extbase
      limitToPages:
        - 18
      extension: Seminars
      plugin: EventRegistration
      defaultController: 'EventRegistration::checkPrerequisites'
      routes:
        - routePath: '/check/{event}'
          _controller: 'EventRegistration::checkPrerequisites'
          _arguments:
            event: event
        - routePath: '/new/{event}'
          _controller: 'EventRegistration::new'
          _arguments:
            event: event
      aspects:
        event:
          type: PersistedAliasMapper
          tableName: 'tx_seminars_seminars'
          routeFieldName: 'uid'
        registration:
          type: PersistedAliasMapper
          tableName: 'tx_seminars_attendances'
          routeFieldName: 'uid'

If you already have route enhancers configured, add the `EventRegistration`
enhancer next to your other router enhancers.

The `limitToPages` setting is optional, but required for better performance.
The given page UID(s) should be the page(s) on which the registration form
content element is located.
