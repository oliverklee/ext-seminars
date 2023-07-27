.. _seo:

===
SEO
===

Routing configuration
=====================

.. _single-view-urls:

Nice URLs for the single view
-----------------------------

..  attention::
    Please enable the seminars "URL segment" (slug) field for your editors.
    Otherwise, you might get exceptions in the frontend if an event does
    not have a slug.

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

Exclude the single view page without an event URL from getting indexed
======================================================================

#.  Edit the page properties of your single view page(s).
#.  Navigate to the "SEO" tab.
#.  Disable "Index this page".
#.  Then add this code to your TypoScript setup:

.. code-block:: typoscript

    # Re-enable indexing for event single view pages, but not for the (empty)
    # detail page without any event UID parameter
    [traverse(request.getQueryParams(), 'tx_seminars_pi1/showUid') > 0]
      page.meta {
        robots = index,follow
        robots.replace = 1
      }
    [global]
