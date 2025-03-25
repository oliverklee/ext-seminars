Preselection in the list view URL
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

If you are linking to the list view from other pages, you can
preselect stuff in the URL (by using the variables from the list view
filter form):

::

   &tx_seminars_pi1[place][]=1
   &tx_seminars_pi1[event_type][]=3
   &tx_seminars_pi1[category]=1
   &tx_seminars_pi1[city][]=Berlin

This also works with combinations of these:

::

   &tx_seminars_pi1[event_type][]=3&tx_seminars_pi1[place][]=3
   &tx_seminars_pi1[place][]=1&tx_seminars_pi1[category]=1

You can not only preselect categories, but also specify search phrases
directly in the URL. To do so, just add this to the URL of the page
that contains the seminars list view:

::

   &tx_seminars_pi1[sword]=searchphrase

If you want to use whitespaces make sure you replace them with %20,
e.g.:

::

   &tx_seminars_pi1[sword]=I%20am%20searching%20something

It's not necessary to have a search field on the list view activated.
So you can specify search phrases in the URL to filter your results
without offering the opportunity to specify other search phrases.

If you want to specify search phrases for a page by default without
specifying parameters in the URL, just enter this to your Typoscript
Setup:

::

   plugin.tx_seminars_pi1._DEFAULT_PI_VARS.sword = searchphrase

If anyone opens this page, only trainings that match your search will
be displayed.
