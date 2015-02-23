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


Stylesheets (CSS) konfigurieren
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The extension provides its own basic set of CSS styles (which work
best with a white background and if you're already using a CSS-based
design and css\_styled\_content). These stylesheets usually get
included automatically. However, if your have set
*disableAllHeaderCode = 1* and want to use the provided stylesheet *,*
you need to include the stylesheet
*typo3conf/ext/seminars/pi1/seminars\_pi1.css* manually into your page
header.

When looking to the source of the HTML output of this extension, you
will see that there are some parts of the output (for example table
cells) marked to belong to a certain class. But not every part has as
'class=”xyz”'—we have only those classes that are actually used in the
CSS file so that the HTML code stays lean. If needed, you can add
classes to these parts by adding them to your TypoScript setup (in the
template):

By default, the organizer field in the ListView has no class added to
keep the HTML-Output as clean as possible. It looks like this:

[...]<td>[name of the organizer]<td>[...]

If you add the following line to your setup,

plugin.tx\_seminars\_pi1.class\_listorganizers = organizers

the output shows us the following:

[...]<td class="tx-seminars-pi1-organizers">[name of the
organizer]</td>[...]

So the resulting class name will be tx-seminars-pi1 and the value from
the corresponding TS rule appended. Please not that the last part of
the name in the TS setup (“class\_listorganizers”) needs to match the
string in the HTML template (“###CLASS\_LISTORGANIZERS###”). The only
difference lies in the capitalization and the “###”.

Then you can add a rule like this to your CSS file:

.tx-seminars-pi1-organizers {...;}


Classes for table rows
""""""""""""""""""""""

The TR elements of the list view already have a few classes
automatically set:

- listrow-odd for every other row, starting with the second row

- tx-seminars-pi1-canceled for canceled events

- tx-seminars-pi1-owner if the logged-in FE user had entered this event
  record


Configuring the colored square for the number of vacancies
""""""""""""""""""""""""""""""""""""""""""""""""""""""""""

In the list view, the color of the squares in the vacancies column is
configured using CSS. The table cell for the vacancies has three CSS
classes:

- tx-seminars-pi1-vacancies

- tx-seminars-pi1-vacancies-x with x being replaced by the exact number
  of vacancies (which may be 0)

- tx-seminars-pi1-vacancies-available if there is at least one vacancy

- tx-seminars-pi1-vacancies-cancelled if the event has been canceled

- tx-seminars-pi1-deadline-over if the registration deadline for that
  event has passed

The square itself also has a CSS class:

- tx-seminars-pi1-square

This allows you to configure the color of the square in detail,
depending on the number of vacancies. The default stylesheet uses:

- *green* for more at least three vacancies

- *yellow* for one or two vacancies

- *red* for “no vacancies” and for canceled seminars

The corresponding part of the default CSS file looks like this. You
can do this likewise in your own style sheet:

.tx-seminars-pi1-vacancies-available .tx-seminars-pi1-square {

background-color: #00a500;

color: inherit;

}

.tx-seminars-pi1-vacancies-2 .tx-seminars-pi1-square, .tx-seminars-
pi1-vacancies-1 .tx-seminars-pi1-square {

background-color: #ffff3c;

color: inherit;

}

.tx-seminars-pi1-vacancies-0 .tx-seminars-pi1-square, .tx-seminars-
pi1-cancelled .tx-seminars-pi1-square {

background-color: #c30000;

color: inherit;

}
