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


Setup for the Seminar Manager front-end plug-in in plugin.tx\_seminars\_pi1
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You can configure the plug-in using flexforms of the front-end plug-in
(for most values) or your TS template setup in the form
plugin.tx\_seminars\_pi1. *property = value.*

If your want to set a value for all instances of the plug-in in one
place, use the TS template setup. If you use flexforms, make sure to
set the values at all relevant instances of the plug-in: It doesn't do
to specify the fields for the online registration in the seminar list
front-end plug-in—you need to set these fields in the online
registration front-end plug-in.

**Note: If you set any non-empty value in the flexforms, this will
override the corresponding value from TS Setup.**

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:

   Data type
         Data type:

   Description
         Description:

   Default
         Default:


.. container:: table-row

   Property
         enableRegistration

   Data type
         boolean

   Description
         Set this to 0 if you don't use the registration feature for this site
         and would like to disable the configuration check for this.

   Default
         1


.. container:: table-row

   Property
         numberOfClicksForRegistration

   Data type
         integer

   Description
         number of clicks to registration (valid options are 2 or 3)

   Default
         3


.. container:: table-row

   Property
         what\_to\_display

   Data type
         string

   Description
         The kind of front-end plug-in to display. Allowed values are in:
         *seminar\_list, single\_view, topic\_list,*  *my\_events,
         my\_vip\_events, seminar\_registration, list\_registrations,
         list\_vip\_registrations, edit\_event, my\_entered\_events, countdown,
         category\_list, event\_headline* This must be set using flexforms.

   Default
         seminar\_list


.. container:: table-row

   Property
         templateFile

   Data type
         string

   Description
         location of the HTML template for the FE plugin

   Default
         EXT:seminars/pi1/seminars\_pi1.tmpl


.. container:: table-row

   Property
         eventEditorTemplateFile

   Data type
         string

   Description
         location of the front-endevent editor template file

   Default
         EXT:seminars/Resources/Private/Templates/FrontEnd/EventEditor.html


.. container:: table-row

   Property
         registrationEditorTemplateFile

   Data type
         string

   Description
         location of the template file for the registration form

   Default
         EXT:seminars/Resources/Private/Templates/FrontEnd/RegistrationEditor.h
         tml


.. container:: table-row

   Property
         salutation

   Data type
         string

   Description
         Switch whether to use formal/informal language on the front
         end.Allowed values are:formal \| informal

   Default
         formal


.. container:: table-row

   Property
         showSingleEvent

   Data type
         integer

   Description
         The UID of an event record. If an event is selected, the plug-inalways
         shows the single view of this event and not the list.

         This must be set using flexforms.

   Default


.. container:: table-row

   Property
         timeframeInList

   Data type
         string

   Description
         the time-frame from which events should be displayed in the list view.
         Select one of these keywords:all, past, pastAndCurrent, current,
         currentAndUpcoming, upcoming, deadlineNotOver, today

   Default
         currentAndUpcoming


.. container:: table-row

   Property
         hideColumns

   Data type
         string

   Description
         comma-separated list of column names that shouldn't be displayed in
         the list view, e.g.  *organizers,price\_special*

         The order of the elements in this list has no influence on the
         output.Allowed values are in: category, title,subtitle,uid,
         event\_type, language, accreditation\_number, credit\_points, teaser,
         speakers, date, time, expiry, place, city, country, seats,
         price\_regular, price\_special, total\_price, organizers,
         target\_groups, attached\_files, vacancies, status\_registration,
         registration, list\_registrations, status, edit, imagePlease note that
         some columns will only be shown if a front-end user currently is
         logged in.

   Default
         Image,category,subtitle,event\_type,language,accreditation\_number,cre
         dit\_points,teaser,time,expiry,place,country,price\_special,speakers,t
         arget\_groups,attached\_files,status


.. container:: table-row

   Property
         hideFields

   Data type
         string

   Description
         comma-separated list of field names that shouldn't be displayed in the
         detail view, e.g.  *organizers,price\_special*

         The order of the elements in this list has no influence on the
         output.Allowed values are in: event\_type, title, subtitle, language,
         description, accreditation\_number, credit\_points, category, date,
         uid, time, place, room, expiry, speakers, partners, tutors, leaders, p
         rice\_regular,price\_board\_regular,price\_special,price\_board\_speci
         al,additional\_information, target\_groups, attached\_files,
         paymentmethods, target\_groups, organizers, vacancies,
         deadline\_registration, otherdates, eventsnextday, registration, back,
         image, requirements, dependencies

   Default
         credit\_points,eventsnextday


.. container:: table-row

   Property
         hideSearchForm

   Data type
         boolean

   Description
         whether to show the search form in the list view

   Default
         0


.. container:: table-row

   Property
         displaySearchFormFields

   Data type
         string

   Description
         comma-separated list of search options which should be shown in the
         search widget. If no field is displayed the search widget will be
         hidden. Allowed values are in: event\_type, language, country, city,
         place, full\_text\_search, date, age, organizer, price, categories

   Default


.. container:: table-row

   Property
         limitListViewToCategories

   Data type
         string

   Description
         comma-separated list of category UIDs to filter the list view for,
         leave empty to have no such filter

   Default


.. container:: table-row

   Property
         limitListViewToPlaces

   Data type
         string

   Description
         comma-separated list of place UIDs to filter the list view for, leave
         empty to have no such filter

   Default


.. container:: table-row

   Property
         limitListViewToOrganizers

   Data type
         string

   Description
         comma-separated list of organizer UIDs to filter the list view for,
         leave empty to have no such filter

   Default


.. container:: table-row

   Property
         showOnlyEventsWithVacancies

   Data type
         boolean

   Description
         whether to show only events with vacancies on in the list view

   Default
         0


.. container:: table-row

   Property
         seminarImageListViewHeight

   Data type
         integer

   Description
         the maximum height of the image of a seminar in the list view

   Default
         43


.. container:: table-row

   Property
         seminarImageListViewWidth

   Data type
         integer

   Description
         the maximum width of the image of a seminar in the list view

   Default
         70


.. container:: table-row

   Property
         hidePageBrowser

   Data type
         boolean

   Description
         whether to show the page browser in the list view

   Default
         0


.. container:: table-row

   Property
         hideCanceledEvents

   Data type
         boolean

   Description
         whether to show canceled events in the list view

   Default
         0


.. container:: table-row

   Property
         sortListViewByCategory

   Data type
         boolean

   Description
         whether the list view should always be sorted by category (before
         applying the normal sorting)

   Default
         0


.. container:: table-row

   Property
         categoriesInListView

   Data type
         string

   Description
         whether to show only the category title, only the category icon or
         both. Allowed values are: icon, text, both

   Default
         both


.. container:: table-row

   Property
         generalPriceInList

   Data type
         boolean

   Description
         whether to use the label “Price” as column header for the standard
         price (instead of “Standard price”)

   Default
         0


.. container:: table-row

   Property
         generalPriceInSingle

   Data type
         boolean

   Description
         whether to use the label “Price” as heading for the standard price
         (instead of “Standard price”) in the detailed view and on the
         registration page

   Default
         0


.. container:: table-row

   Property
         omitDateIfSameAsPrevious

   Data type
         boolean

   Description
         whether to omit the date in the list view if it is the same as the
         previous item's (useful if you often have several events at the same
         date), @deprecated #1788 will be removed in seminars 5.0

   Default
         0


.. container:: table-row

   Property
         showOwnerDataInSingleView

   Data type
         boolean

   Description
         whether to show the owner data in the single view,
         @deprecated #1811, will be removed in seminars 5.0

   Default
         0


.. container:: table-row

   Property
         ownerPictureMaxWidth

   Data type
         integer

   Description
         the maximum width of the owner picture in the single view,
         @deprecated #1811, will be removed in seminars 5.0

   Default
         250


.. container:: table-row

   Property
         accessToFrontEndRegistrationLists

   Data type
         string

   Description
         who is allowed to view the list of registrations on the front end;
         allowed values are: attendees\_and\_managers, login, world

   Default
         attendees\_and\_managers


.. container:: table-row

   Property
         allowCsvExportOfRegistrationsInMyVipEventsView

   Data type
         boolean

   Description
         whether to allow the CSV export in the "my VIP events" view

   Default
         0


.. container:: table-row

   Property
         mayManagersEditTheirEvents

   Data type
         boolean

   Description
         whether managers may edit their events,
         @deprecated #1633 will be removed in seminars 5.0

   Default
         0


.. container:: table-row

   Property
         eventFieldsOnRegistrationPage

   Data type
         string

   Description
         list of comma-separated names of event fields that should be displayed
         on the registration page (the order doesn't matter)Allowed values are
         in: uid,title,price\_regular,price\_special,vacancies

   Default
         title,price\_regular,price\_special,vacancies


.. container:: table-row

   Property
         showRegistrationFields

   Data type
         string

   Description
         comma-separated list of tx\_seminars\_attendances DB fields to show
         for the online registrationThe order of the values is  *not*
         relevant.Allowed values are in:step\_counter,
         price,method\_of\_payment, account\_number, bank\_code, bank\_name,
         account\_owner, billing\_address, company, gender, name, address, zip,
         city, country, telephone, email,interests, expectations,
         background\_knowledge, accommodation, food, known\_from, seats,
         registered\_themselves,attendees\_names, kids, lodgings, foods,
         checkboxes, notes, total\_price, feuser\_data, billing\_address,
         registration\_data, terms, terms\_2

         **Note:**  *billing\_address* enabled the summary of all billing
         address fields for the second registration page. To get this to work
         correctly, you also need to enable the particular fields for a
         separate billing addres that should be displayed on the first
         registration page, for example: name, address, zip, city

   Default
         step\_counter,price,method\_of\_payment,lodgings,foods,checkboxes,inte
         rests,expectations,background\_knowledge,known\_from,notes,total\_pric
         e,feuser\_data,billing\_address,registration\_data,terms\_2


.. container:: table-row

   Property
         registerThemselvesByDefaultForHiddenCheckbox

   Data type
         boolean

   Description
         whether the logged-in user should be registered themselves by default
         in the registration form (only applicable if the checkbox is hidden)

   Default
         1


.. container:: table-row

   Property
         showFeUserFieldsInRegistrationForm

   Data type
         string

   Description
         fe\_users DB fields to show for in the registration form

   Default
         name,company,address,zip,city,country,telephone,email


.. container:: table-row

   Property
         showFeUserFieldsInRegistrationFormWithLabel

   Data type
         string

   Description
         fe\_users DB fields on the registration form that should be displayed
         with a label

   Default
         telephone,email


.. container:: table-row

   Property
         numberOfFirstRegistrationPage

   Data type
         integer

   Description
         the displayed number of the first registration page (for "step x of
         y")

   Default
         1


.. container:: table-row

   Property
         numberOfLastRegistrationPage

   Data type
         integer

   Description
         the displayed number of the last registration page (for "step x of y")

   Default
         2


.. container:: table-row

   Property
         maximumBookableSeats

   Data type
         integer

   Description
         the maximum number of seats that can be booked in one registration

   Default
         10


.. container:: table-row

   Property
         showSpeakerDetails

   Data type
         boolean

   Description
         whether to show detailed information of the speakers in the single view;
         if disabled, only the names will be shown

   Default
         1


.. container:: table-row

   Property
         showSiteDetails

   Data type
         boolean

   Description
         whether to show detailed information of the locations in the single
         viewif disabled, only the name of the locations will be shown

   Default
         1


.. container:: table-row

   Property
         limitFileDownloadToAttendees

   Data type
         boolean

   Description
         whether file downloads are limited to attendees only

   Default
         1


.. container:: table-row

   Property
         showFeUserFieldsInRegistrationsList

   Data type
         string

   Description
         comma-separated list of FEuser fields to show in the list of
         registrations for an event

   Default
         name


.. container:: table-row

   Property
         showRegistrationFieldsInRegistrationList

   Data type
         string

   Description
         comma-separated list of registration fields to show in the list of
         registrations for an event

   Default
         None


.. container:: table-row

   Property
         logOutOneTimeAccountsAfterRegistration

   Data type
         boolean

   Description
         Whether one-time FE user accounts will be automatically logged out
         after they have registered for an event.

         **Note:** This does not affect regular FE user accounts in any way.

         @deprecated #1947 will be removed in seminars 5.0

   Default
         1


.. container:: table-row

   Property
         enableSortingLinksInListView

   Data type
         boolean

   Description
        whether to add sorting links to the headers in the list view

   Default
         1


.. container:: table-row

   Property
         linkToSingleView

   Data type
         string

   Description
        when to link to the single view: always, never, onlyForNonEmptyDescription

   Default
         always


.. container:: table-row

   Property
         whether to send an additional notification e-mail from the FE editor to the reviewers when a new record has been created

   Data type
         boolean

   Description
        sendAdditionalNotificationEmailInFrontEndEditor,
        @deprecated #1543, will be removed in seminars 5.0

   Default
         0


.. container:: table-row

   Property
         speakerImageWidth

   Data type
         integer

   Description
         width of the speaker image in the event single view

   Default
         150


.. container:: table-row

   Property
         speakerImageHeight

   Data type
         integer

   Description
         height of the speaker image in the event single view

   Default
         150


.. container:: table-row

   Property
         pages

   Data type
         integer

   Description
         PID of the sysfolder that contains all the event records (e.g. the
         starting point)

   Default
         None


.. container:: table-row

   Property
         recursive

   Data type
         integer

   Description
         level of recursion that should be used when accessing the
         startingpoint

   Default
         None


.. container:: table-row

   Property
         listPID

   Data type
         page\_id

   Description
         PID of the FE page that contains the event list

   Default
         None


.. container:: table-row

   Property
         detailPID

   Data type
         page\_id

   Description
         PID of the FE page that contains the single view

   Default
         None


.. container:: table-row

   Property
         myEventsPID

   Data type
         page\_id

   Description
         PID of the FE page that contains the "my events" list

   Default
         None


.. container:: table-row

   Property
         registerPID

   Data type
         page\_id

   Description
         PID of the FE page that contains the seminar registration plug-in

   Default
         None


.. container:: table-row

   Property
         thankYouAfterRegistrationPID

   Data type
         page\_id

   Description
         PID of the thank-you page that will be displayed after a FE user has
         registered for an event

   Default
         None


.. container:: table-row

   Property
         sendParametersToThankYouAfterRegistrationPageUrl

   Data type
         boolean

   Description
         Whether to send GET parameters to the thank-you-after-registration-
         page-URL.

   Default
         1


.. container:: table-row

   Property
         createAdditionalAttendeesAsFrontEndUsers

   Data type
         boolean

   Description
         whether to create FE user records for additional attendees (in
         addition to storing them in a text field)

   Default
         0


.. container:: table-row

   Property
         sysFolderForAdditionalAttendeeUsersPID

   Data type
         page\_id

   Description
         UID of the sysfolder in which FE users created as additional attendees
         in the registration form get stored

   Default


.. container:: table-row

   Property
         userGroupUidsForAdditionalAttendeesFrontEndUsers

   Data type
         string

   Description
         comma-separated list of front-end user group UIDs to which the FE
         users created in the registration form will be assigned

   Default


.. container:: table-row

   Property
         pageToShowAfterUnregistrationPID

   Data type
         page\_id

   Description
         PID of the page that will be displayed after a FE user has
         unregistered from an event

   Default
         None


.. container:: table-row

   Property
         sendParametersToPageToShowAfterUnregistrationUrl

   Data type
         boolean

   Description
         Whether to send GET parameters to the thank-you-after-registration-
         page-URL.

   Default
         1


.. container:: table-row

   Property
         loginPID

   Data type
         page\_id

   Description
         PID of the FE page that contains the login form or onetimeaccount

   Default
         None


.. container:: table-row

   Property
         registrationsListPID

   Data type
         page\_id

   Description
         PID of the page that contains the registrations list for participants

   Default
         None


.. container:: table-row

   Property
         registrationsVipListPID

   Data type
         page\_id

   Description
         PID of the page that contains the registrations list for editors

   Default
         None


.. container:: table-row

   Property
         eventEditorFeGroupID

   Data type
         integer

   Description
         UID of the FE user group that is allowed to enter and edit event
         records in the FE

   Default
         None


.. container:: table-row

   Property
         defaultEventVipsFeGroupID

   Data type
         integer

   Description
         UID of the FE user group that is allowed to see the registrations of
         all events

   Default
         None


.. container:: table-row

   Property
         eventEditorPID

   Data type
         page\_id

   Description
         PID of the page where the plug-in for editing events is located

   Default
         None


.. container:: table-row

   Property
         createEventsPID

   Data type
         page\_id

   Description
         PID of the sysfolder where FE-created events will be stored

   Default
         None


.. container:: table-row

   Property
         createAuxiliaryRecordsPID

   Data type
         page\_id

   Description
         PID of the sysfolder where FE-created auxiliary records will be stored

   Default
         None


.. container:: table-row

   Property
         eventSuccessfullySavedPID

   Data type
         page\_id

   Description
         PID of the page that will be shown when an event has been successfully
         entered on the FE

   Default
         None


.. container:: table-row

   Property
         displayFrontEndEditorFields

   Data type
         String

   Description
         comma-separated list of the fields to show in the FE-editor; allowed
         values are: subtitle,accreditation\_number, credit\_points,
         categories, event\_type, cancelled, teaser,description,
         additional\_information, begin\_date, end\_date,
         begin\_date\_registration, deadline\_early\_bird,
         deadline\_registration, needs\_registration,
         allows\_multiple\_registrations, queue\_size, offline\_attendees,
         attendees\_min, attendees\_max, target\_groups, price\_regular,
         price\_regular\_early, price\_regular\_board, price\_special,
         price\_special\_early, price\_special\_board, payment\_methods, place,
         room, lodgings, foods, speakers, leaders, partners, tutors,
         checkboxes, uses\_terms\_2, attached\_file\_box, notes

   Default
         subtitle,accreditation\_number,credit\_points,categories,event\_type,c
         ancelled,teaser,description,additional\_information,begin\_date,end\_d
         ate,begin\_date\_registration,deadline\_early\_bird,deadline\_registra
         tion,needs\_registration,allows\_multiple\_registrations,queue\_size,o
         ffline\_attendees,attendees\_min,attendees\_max,target\_groups,price\_
         regular,price\_regular\_early,price\_regular\_board,price\_special,pri
         ce\_special\_early,price\_special\_board,payment\_methods,place,room,l
         odgings,foods,speakers,leaders,partners,tutors,checkboxes,uses\_terms\
         _2,attached\_file\_box,notes


.. container:: table-row

   Property
         requiredFrontEndEditorFields

   Data type
         String

   Description
         comma-separated list of the event fields which are required to be
         filled in the FE editor; allowed values are: subtitle,
         accreditation\_number, credit\_points, categories, event\_type,
         cancelled, teaser, description, additional\_information, begin\_date,
         end\_date, begin\_date\_registration, deadline\_early\_bird,
         deadline\_registration, needs\_registration,
         allows\_multiple\_registrations, queue\_size, attendees\_min,
         attendees\_max, offline\_attendees, target\_groups, price\_regular,
         price\_regular\_early, price\_regular\_board, price\_special,
         price\_special\_early, price\_special\_board, payment\_methods, place,
         room, lodgings, foods, speakers, leaders, partners, tutors,
         checkboxes, uses\_terms\_2, attached\_file\_box, notes

   Default


.. container:: table-row

   Property
         requiredFrontEndEditorPlaceFields

   Data type
         String

   Description
         comma-separated list of the place fields which are required to be
         filled in the FE editor; allowed values are: address, zip, city,
         country, homepage, directions

   Default
         city


.. container:: table-row

   Property
         bankTransferUID

   Data type
         record\_id

   Description
         UID of the payment method that corresponds to "bank transfer", used
         for input validation in the registration form,
         @deprecated #1571, will be removed in seminars 5.0

   Default
         None


.. container:: table-row

   Property
         externalLinkTarget

   Data type
         string

   Description
         The target for external links in seminars.

   Default
         None


.. container:: table-row

   Property
         seminarImageSingleViewWidth

   Data type
         integer

   Description
         the maximum width of the image of a seminar in the single view

   Default
         260


.. container:: table-row

   Property
         seminarImageSingleViewHeight

   Data type
         integer

   Description
         the maximum height of the image of a seminar in the single view

   Default
         160


.. container:: table-row

   Property
         allowFrontEndEditingOfSpeakers

   Data type
         boolean

   Description
         whether to allow front-end editing of speakers

   Default
         0


.. container:: table-row

   Property
         allowFrontEndEditingOfPlaces

   Data type
         boolean

   Description
         whether to allow front-end editing of places

   Default
         0


.. container:: table-row

   Property
         allowFrontEndEditingOfCheckboxes

   Data type
         boolean

   Description
         whether to allow front-end editing of checkboxes

   Default
         0


.. container:: table-row

   Property
         allowFrontEndEditingOfTargetGroups

   Data type
         boolean

   Description
         whether to allow front-end editing of target groups

   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_seminars\_pi1]
