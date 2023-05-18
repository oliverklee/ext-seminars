.. _seo:

===
SEO
===

Routing configuration
=====================

Nice URLs for the single view
-----------------------------

First run the upgrade wizards to generate the slugs for all event records.
(You can change them later to suit your needs.)

Then add this to your site configuration file:

.. code-block:: yaml

  routeEnhancers:
    EventSingleView:
      type: Plugin
      limitToPages:
        - 17
      routePath: '/{slug}'
      namespace: 'tx_seminars_pi1'
      _arguments:
        slug: showUid
      requirements:
        slug: '[a-z0-9/\-]+'
      aspects:
        slug:
          type: PersistedAliasMapper
          tableName: 'tx_seminars_seminars'
          routeFieldName: 'slug'

If you already have route enhancers configured, add the `EventSingleView`
enhancer next to your other router enhancers.

The `limitToPages` setting is optional, but required for better performance.
The given page UID(s) should be the page(s) on which the seminars single view
content element is located.

Nice URLs for the registration form
-----------------------------------

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

Automatic page titles for the single view
=========================================

Add this to your TypoScript setup:

.. code-block:: typoscript

  config.pageTitleProviders {
    eventTitle {
      provider = OliverKlee\Seminars\Seo\SingleViewPageTitleProvider
      before = record
    }
  }