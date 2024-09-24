Configuring CSS
^^^^^^^^^^^^^^^

The extension provides its own basic set of CSS styles (which work
best with a white background and if you're already using a CSS-based
design and css\_styled\_content). These stylesheets usually get
included automatically. However, if you have set
*disableAllHeaderCode = 1* and want to use the provided stylesheet *,*
you need to include the stylesheet
*typo3conf/ext/seminars/pi1/seminars\_pi1.css* manually into your page
header.



Classes for table rows
""""""""""""""""""""""

The TR elements of the list view already have a few classes
automatically set:

- listrow-odd for every other row, starting with the second row

- tx-seminars-pi1-canceled for canceled events


Configuring the colored square for the number of vacancies
""""""""""""""""""""""""""""""""""""""""""""""""""""""""""

In the list view, the color of the squares in the vacancies column is
configured using CSS. The table cell for the vacancies has three CSS
classes:

- tx-seminars-pi1-vacancies

- tx-seminars-pi1-vacancies-x with x being replaced by the exact number
  of vacancies (which may be 0)

- tx-seminars-pi1-vacancies-available if there is at least one vacancy

- tx-seminars-pi1-vacancies-cancelled if the event has been cancelled

- tx-seminars-pi1-deadline-over if the registration deadline for that
  event has passed

The square itself also has a CSS class:

- tx-seminars-pi1-square

This allows you to configure the color of the square in detail,
depending on the number of vacancies. The default style sheet uses:

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
