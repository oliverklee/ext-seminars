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


Setup for the Seminarmanager front-end plug-in in plugin.tx\_seminars\_pi1
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

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

   Eigenschaft
         Eigenschaft:

   Datentyp
         Datentyp:

   Beschreibung
         Beschreibung:

   Standardwert
         Standardwert:


.. container:: table-row

   Eigenschaft
         enableRegistration

   Datentyp
         boolean

   Beschreibung
         Set this to 0 if you don't use the registration feature for this site
         and would like to disable the configuration check for this.

   Standardwert
         1


.. container:: table-row

   Eigenschaft
         numberOfClicksForRegistration

   Datentyp
         integer

   Beschreibung
         number of clicks to registration (valid options are 2 or 3)

   Standardwert
         3


.. container:: table-row

   Eigenschaft
         what\_to\_display

   Datentyp
         string

   Beschreibung
         The kind of front-end plug-in to display. Allowed values are in:
         *seminar\_list, single\_view, topic\_list, my\_events,
         my\_vip\_events, seminar\_registration, list\_registrations,
         list\_vip\_registrations, edit\_event, my\_entered\_events, countdown,
         category\_list, event\_headline* This must be set using flexforms.

   Standardwert
         seminar\_list


.. container:: table-row

   Eigenschaft
         templateFile

   Datentyp
         string

   Beschreibung
         location of the HTML template for the FE plugin

   Standardwert
         EXT:seminars/pi1/seminars\_pi1.tmpl


.. container:: table-row

   Eigenschaft
         eventEditorTemplateFile

   Datentyp
         string

   Beschreibung
         location of the front end event editor template file

   Standardwert
         EXT:seminars/Resources/Private/Templates/FrontEnd/EventEditor.html


.. container:: table-row

   Eigenschaft
         registrationEditorTemplateFile

   Datentyp
         string

   Beschreibung
         location of the template file for the registration form

   Standardwert
         EXT:seminars/Resources/Private/Templates/FrontEnd/RegistrationEditor.h
         tml


.. container:: table-row

   Eigenschaft
         salutation

   Datentyp
         string

   Beschreibung
         Switch whether to use formal/informal language on the front
         end.Allowed values are:formal \| informal

   Standardwert
         formal


.. container:: table-row

   Eigenschaft
         showSingleEvent

   Datentyp
         integer

   Beschreibung
         The UID of an event record. If an event is selected, the plugin always
         shows the single view of this event and not the list.

         This must be set using flexforms.

   Standardwert


.. container:: table-row

   Eigenschaft
         timeframeInList

   Datentyp
         string

   Beschreibung
         the time-frame from which events should be displayed in the list view.
         Select one of these keywords:all, past, pastAndCurrent, current,
         currentAndUpcoming, upcoming, deadlineNotOver, today

   Standardwert
         currentAndUpcoming


.. container:: table-row

   Eigenschaft
         hideColumns

   Datentyp
         string

   Beschreibung
         comma-separated list of column names that shouldn't be displayed in
         the list view, e.g.  *organizers,price\_special*

         The order of the elements in this list has no influence on the
         output.Allowed values are in: category, title,subtitle,uid,
         event\_type, language, accreditation\_number, credit\_points, teaser,
         speakers, date, time, expiry, place, city, country, seats,
         price\_regular, price\_special, total\_price, organizers,
         target\_groups, attached\_files, vacancies, status\_registration,
         registration, list\_registrations, status, editPlease note that some
         columns will only be shown if a front-end user currently is logged in.

   Standardwert
         category,subtitle,event\_type,language,accreditation\_number,credit\_p
         oints,teaser,time,expiry,place,country,price\_special,speakers,target\
         _groups,attached\_files,status


.. container:: table-row

   Eigenschaft
         hideFields

   Datentyp
         string

   Beschreibung
         comma-separated list of field names that shouldn't be displayed in the
         detail view, e.g.  *organizers,price\_special*

         The order of the elements in this list has no influence on the
         output.Allowed values are in: event\_type, title, subtitle, language,
         description, accreditation\_number, credit\_points, category, date,
         uid, time, place, room, expiry, speakers, partners, tutors, leaders, p
         rice\_regular,price\_board\_regular,price\_special,price\_board\_speci
         al,additional\_information, paymentmethods, target\_groups,
         attached\_files, organizers, vacancies, deadline\_registration,
         otherdates, eventsnextday, registration, back, image, requirements,
         dependencies

   Standardwert
         credit\_points,eventsnextday


.. container:: table-row

   Eigenschaft
         hideSearchForm

   Datentyp
         boolean

   Beschreibung
         whether to show the search form in the list view

   Standardwert
         0


.. container:: table-row

   Eigenschaft
         displaySearchFormFields

   Datentyp
         string

   Beschreibung
         comma-separated list of search options which should be shown in the
         search widget. If no field is displayed the search widget will be
         hidden. Allowed values are in: event\_type, language, country, city,
         place, full\_text\_search, day, age, organizer, price, categories

   Standardwert


.. container:: table-row

   Eigenschaft
         hidePageBrowser

   Datentyp
         boolean

   Beschreibung
         whether to show the page browser in the list view

   Standardwert
         0


.. container:: table-row

   Eigenschaft
         hideCanceledEvents

   Datentyp
         boolean

   Beschreibung
         whether to show canceled events in the list view

   Standardwert
         0


.. container:: table-row

   Eigenschaft
         limitListViewToCategories

   Datentyp
         string

   Beschreibung
         comma-separated list of category UIDs to filter the list view for,
         leave empty to have no such filter

   Standardwert


.. container:: table-row

   Eigenschaft
         limitListViewToPlaces

   Datentyp
         string

   Beschreibung
         comma-separated list of place UIDs to filter the list view for, leave
         empty to have no such filter

   Standardwert


.. container:: table-row

   Eigenschaft
         limitListViewToOrganizers

   Datentyp
         string

   Beschreibung
         comma-separated list of organizer UIDs to filter the list view for,
         leave empty to have no such filter

   Standardwert


.. container:: table-row

   Eigenschaft
         showOnlyEventsWithVacancies

   Datentyp
         boolean

   Beschreibung
         whether to show only events with vacancies on in the list view

   Standardwert
         0


.. container:: table-row

   Eigenschaft
         seminarImageListViewHeight

   Datentyp
         integer

   Beschreibung
         the maximum height of the image of a seminar in the list view

   Standardwert
         43


.. container:: table-row

   Eigenschaft
         seminarImageListViewWidth

   Datentyp
         integer

   Beschreibung
         the maximum width of the image of a seminar in the list view

   Standardwert
         70


.. container:: table-row

   Eigenschaft
         sortListViewByCategory

   Datentyp
         boolean

   Beschreibung
         ob die Listenansicht immer nach Kategorien sortiert werden soll (bevor
         die normale Sortierung angewendet wird)

   Standardwert
         0


.. container:: table-row

   Eigenschaft
         categoriesInListView

   Datentyp
         string

   Beschreibung
         whether to show only the category title, only the category icon or
         both. Allowed values are: icon, text, both

   Standardwert
         both


.. container:: table-row

   Eigenschaft
         generalPriceInList

   Datentyp
         boolean

   Beschreibung
         whether to use the label “Price” as column header for the standard
         price (instead of “Standard price”)

   Standardwert
         0


.. container:: table-row

   Eigenschaft
         generalPriceInSingle

   Datentyp
         boolean

   Beschreibung
         whether to use the label “Price” as heading for the standard price
         (instead of “Standard price”) in the detailed view and on the
         registration page

   Standardwert
         0


.. container:: table-row

   Eigenschaft
         omitDateIfSameAsPrevious

   Datentyp
         boolean

   Beschreibung
         whether to omit the date in the list view if it is the same as the
         previous item's (useful if you often have several events at the same
         date)

   Standardwert
         0


.. container:: table-row

   Eigenschaft
         showOwnerDataInSingleView

   Datentyp
         boolean

   Beschreibung
         whether to show the owner data in the single view,
         @deprecated, will be removed in seminars 5.0

   Standardwert
         0


.. container:: table-row

   Eigenschaft
         ownerPictureMaxWidth

   Datentyp
         integer

   Beschreibung
         the maximum width of the owner picture in the single view,
         @deprecated, will be removed in seminars 5.0

   Standardwert
         250


.. container:: table-row

   Eigenschaft
         accessToFrontEndRegistrationLists

   Datentyp
         string

   Beschreibung
         who is allowed to view the list of registrations on the front end;
         allowed values are: attendees\_and\_managers, login, world

   Standardwert
         attendees\_and\_managers


.. container:: table-row

   Eigenschaft
         allowCsvExportOfRegistrationsInMyVipEventsView

   Datentyp
         boolean

   Beschreibung
         Legt fest ob es erlaubt ist auf den CSV Export der Anmeldungen von der
         „meine verwalteten Veranstaltungen“-Ansicht aus zuzugreifen

   Standardwert
         0


.. container:: table-row

   Eigenschaft
         mayManagersEditTheirEvents

   Datentyp
         boolean

   Beschreibung
         Legt fest ob Verwalter ihre Veranstaltungen bearbeiten dürfen

   Standardwert
         0


.. container:: table-row

   Eigenschaft
         eventFieldsOnRegistrationPage

   Datentyp
         string

   Beschreibung
         list of comma-separated names of event fields that should be displayed
         on the registration page (the order doesn't matter)Allowed values are
         in: uid,title,price\_regular,price\_special,vacancies

   Standardwert
         title,price\_regular,price\_special,vacancies


.. container:: table-row

   Eigenschaft
         showFeUserFieldsInRegistrationForm

   Datentyp
         string

   Beschreibung
         fe\_users DB fields to show for in the registration form

   Standardwert
         name,company,address,zip,city,country,telephone,email


.. container:: table-row

   Eigenschaft
         showFeUserFieldsInRegistrationFormWithLabel

   Datentyp
         string

   Beschreibung
         fe\_users DB fields on the registration form that should be displayed
         with a label

   Standardwert
         telephone,email


.. container:: table-row

   Eigenschaft
         showRegistrationFields

   Datentyp
         string

   Beschreibung
         comma-separated list of tx\_seminars\_attendances DB fields to show
         for the online registrationThe order of the values is  *not*
         relevant.Allowed values are in:step\_counter,
         price,method\_of\_payment, account\_number, bank\_code, bank\_name,
         account\_owner, billing\_address, company, gender, name, address, zip,
         city, country, telephone, email,interests, expectations,
         background\_knowledge, accommodation, food, known\_from, seats,
         registered\_themselves, attendees\_names, kids, lodgings, foods,
         checkboxes, notes, total\_price, feuser\_data, billing\_address,
         registration\_data, terms, terms\_2

         **Note:**  *billing\_address* enabled the summary of all billing
         address fields for the second registration page. To get this to work
         correctly, you also need to enable the particular fields for a
         separate billing addres that should be displayed on the first
         registration page, for example: name, address, zip, city

   Standardwert
         step\_counter,price,method\_of\_payment,lodgings,foods,checkboxes,inte
         rests,expectations,background\_knowledge,known\_from,notes,total\_pric
         e,feuser\_data,billing\_address,registration\_data,terms\_2


.. container:: table-row

   Property
         registerThemselvesByDefaultForHiddenCheckbox

   Data type
         boolean

   Description
         ob sich der eingeloggte Benutzer per Default im Anmeldeformular auch selbst anmeldet
         (nur wirksam, wenn die Checkbox im Anmeldeformular ausgeblendet ist)

   Default
         1


.. container:: table-row

   Eigenschaft
         numberOfFirstRegistrationPage

   Datentyp
         integer

   Beschreibung
         the displayed number of the first registration page (for "step x of
         y")

   Standardwert
         1


.. container:: table-row

   Eigenschaft
         numberOfLastRegistrationPage

   Datentyp
         integer

   Beschreibung
         the displayed number of the last registration page (for "step x of y")

   Standardwert
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

   Eigenschaft
         showSpeakerDetails

   Datentyp
         boolean

   Beschreibung
         whether to show detailed information of the speakers in the single view;
         if disabled, only the names will be shown

   Standardwert
         1


.. container:: table-row

   Eigenschaft
         showSiteDetails

   Datentyp
         boolean

   Beschreibung
         whether to show detailed information of the locations in the single
         viewif disabled, only the name of the locations will be shown

   Standardwert
         1


.. container:: table-row

   Eigenschaft
         limitFileDownloadToAttendees

   Datentyp
         boolean

   Beschreibung
         whether file downloads are limited to attendees only

   Standardwert
         1


.. container:: table-row

   Eigenschaft
         showFeUserFieldsInRegistrationsList

   Datentyp
         string

   Beschreibung
         comma-separated list of FEuser fields to show in the list of
         registrations for an event

   Standardwert
         name


.. container:: table-row

   Eigenschaft
         showRegistrationFieldsInRegistrationList

   Datentyp
         string

   Beschreibung
         comma-separated list of registration fields to show in the list of
         registrations for an event

   Standardwert
         None


.. container:: table-row

   Eigenschaft
         logOutOneTimeAccountsAfterRegistration

   Datentyp
         boolean

   Beschreibung
         Whether one-time FE user accounts will be automatically logged out
         after they have registered for an event.

         **Note:** This does not affect regular FE user accounts in any way.

   Standardwert
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
        @deprecated will be removed in seminars 5.0

   Default
         0


.. container:: table-row

   Eigenschaft
         speakerImageWidth

   Datentyp
         integer

   Beschreibung
         Breite des Speaker-Bildes in der Veranstaltungs-Einzelansicht

   Standardwert
         150


.. container:: table-row

   Eigenschaft
         speakerImageHeight

   Datentyp
         integer

   Beschreibung
         Höhe des Speaker-Bildes in der Veranstaltungs-Einzelansicht

   Standardwert
         150


.. container:: table-row

   Eigenschaft
         pages

   Datentyp
         integer

   Beschreibung
         PID des Ordners, das die Veranstaltungsdatensätze enthält

   Standardwert
         None


.. container:: table-row

   Eigenschaft
         recursive

   Datentyp
         integer

   Beschreibung
         level of recursion that should be used when accessing the
         startingpoint

   Standardwert
         None


.. container:: table-row

   Eigenschaft
         listPID

   Datentyp
         page\_id

   Beschreibung
         PID der FE-Seite, die die Listenansicht enthält

   Standardwert
         None


.. container:: table-row

   Eigenschaft
         detailPID

   Datentyp
         page\_id

   Beschreibung
         PID der FE-Seite, die die Einzelansicht enthält

   Standardwert
         None


.. container:: table-row

   Eigenschaft
         myEventsPID

   Datentyp
         page\_id

   Beschreibung
         PID der „Meine Veranstaltungen“-Seite

   Standardwert
         None


.. container:: table-row

   Eigenschaft
         registerPID

   Datentyp
         page\_id

   Beschreibung
         PID der FE-Seite mit der Veranstaltungsanmeldung

   Standardwert
         None


.. container:: table-row

   Eigenschaft
         thankYouAfterRegistrationPID

   Datentyp
         page\_id

   Beschreibung
         PID der FE-Seite, die man nach erfolgreicher Anmeldung zu einer
         Veranstaltung sehen soll

   Standardwert
         None


.. container:: table-row

   Eigenschaft
         sendParametersToThankYouAfterRegistrationPageUrl

   Datentyp
         boolean

   Beschreibung
         Wether to send GET parameters to the thank-you-after-registration-
         page-URL.

   Standardwert
         1


.. container:: table-row

   Eigenschaft
         pageToShowAfterUnregistrationPID

   Datentyp
         page\_id

   Beschreibung
         PID der Seite, die man nach erfolgreicher Abmeldung von einer Seite
         sehen soll

   Standardwert
         None


.. container:: table-row

   Eigenschaft
         sendParametersToPageToShowAfterUnregistrationUrl

   Datentyp
         boolean

   Beschreibung
         Wether to send GET parameters to the thank-you-after-registration-
         page-URL.

   Standardwert
         1


.. container:: table-row

   Eigenschaft
         createAdditionalAttendeesAsFrontEndUsers

   Datentyp
         boolean

   Beschreibung
         whether to create FE user records for additional attendees (in
         addition to storing them in a text field)

   Standardwert
         0


.. container:: table-row

   Eigenschaft
         sysFolderForAdditionalAttendeeUsersPID

   Datentyp
         page\_id

   Beschreibung
         UID of the sysfolder in which FE users created as additional attendees
         in the registration form get stored

   Standardwert


.. container:: table-row

   Eigenschaft
         userGroupUidsForAdditionalAttendeesFrontEndUsers

   Datentyp
         string

   Beschreibung
         comma-separated list of front-end user group UIDs to which the FE
         users created in the registration form will be assigned

   Standardwert


.. container:: table-row

   Eigenschaft
         loginPID

   Datentyp
         page\_id

   Beschreibung
         PID der FE-Seite mit dem Login bzw. onetimeaccount

   Standardwert
         None


.. container:: table-row

   Eigenschaft
         registrationsListPID

   Datentyp
         page\_id

   Beschreibung
         PID of the page that contains the registrations list for participants

   Standardwert
         None


.. container:: table-row

   Eigenschaft
         registrationsVipListPID

   Datentyp
         page\_id

   Beschreibung
         PID of the page that contains the registrations list for editors

   Standardwert
         None


.. container:: table-row

   Eigenschaft
         eventEditorFeGroupID

   Datentyp
         integer

   Beschreibung
         UID of the FE user group that is allowed to enter and edit event
         records in the FE

   Standardwert
         None


.. container:: table-row

   Eigenschaft
         defaultEventVipsFeGroupID

   Datentyp
         integer

   Beschreibung
         UID of the FE user group that is allowed to see the registrations of
         all events

   Standardwert
         None


.. container:: table-row

   Eigenschaft
         eventEditorPID

   Datentyp
         page\_id

   Beschreibung
         PID of the page where the plug-in for editing events is located

   Standardwert
         None


.. container:: table-row

   Eigenschaft
         createEventsPID

   Datentyp
         page\_id

   Beschreibung
         PID of the sysfolder where FE-created events will be stored

   Standardwert
         None


.. container:: table-row

   Eigenschaft
         createAuxiliaryRecordsPID

   Datentyp
         page\_id

   Beschreibung
         PID of the sysfolder where FE-created auxiliary records will be stored

   Standardwert
         None


.. container:: table-row

   Eigenschaft
         eventSuccessfullySavedPID

   Datentyp
         page\_id

   Beschreibung
         PID of the page that will be shown when an event has been successfully
         entered on the FE

   Standardwert
         None


.. container:: table-row

   Eigenschaft
         displayFrontEndEditorFields

   Datentyp
         String

   Beschreibung
         comma-separated list of the fields to show in the FE-editor allowed
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

   Standardwert
         subtitle,accreditation\_number,credit\_points,categories,event\_type,c
         ancelled,teaser,description,additional\_information,begin\_date,end\_d
         ate,begin\_date\_registration,deadline\_early\_bird,deadline\_registra
         tion,needs\_registration,allows\_multiple\_registrations,queue\_size,a
         ttendees\_min,attendees\_max,target\_groups,offline\_attendees,price\_
         regular,price\_regular\_early,price\_regular\_board,price\_special,pri
         ce\_special\_early,price\_special\_board,payment\_methods,place,room,l
         odgings,foods,speakers,leaders,partners,tutors,checkboxes,uses\_terms\
         _2,attached\_file\_box,notes


.. container:: table-row

   Eigenschaft
         requiredFrontEndEditorFields

   Datentyp
         String

   Beschreibung
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

   Standardwert


.. container:: table-row

   Eigenschaft
         requiredFrontEndEditorPlaceFields

   Datentyp
         String

   Beschreibung
         comma-separated list of the place fields which are required to be
         filled in the FE editor; allowed values are: address, zip, city,
         country, homepage, directions

   Standardwert
         city


.. container:: table-row

   Eigenschaft
         externalLinkTarget

   Datentyp
         string

   Beschreibung
         Das Zielfenster für externe Links in seminars.

   Standardwert
         Nichts


.. container:: table-row

   Eigenschaft
         seminarImageSingleViewWidth

   Datentyp
         integer

   Beschreibung
         the maximum width of the image of a seminar in the single view

   Standardwert
         260


.. container:: table-row

   Eigenschaft
         seminarImageSingleViewHeight

   Datentyp
         integer

   Beschreibung
         the maximum height of the image of a seminar in the single view

   Standardwert
         160


.. container:: table-row

   Eigenschaft
         allowFrontEndEditingOfSpeakers

   Datentyp
         boolean

   Beschreibung
         whether to allow front-end editing of speakers

   Standardwert
         0


.. container:: table-row

   Eigenschaft
         allowFrontEndEditingOfPlaces

   Datentyp
         boolean

   Beschreibung
         whether to allow front-end editing of places

   Standardwert
         0


.. container:: table-row

   Eigenschaft
         allowFrontEndEditingOfCheckboxes

   Datentyp
         boolean

   Beschreibung
         whether to allow front-end editing of checkboxes

   Standardwert
         0


.. container:: table-row

   Eigenschaft
         allowFrontEndEditingOfTargetGroups

   Datentyp
         boolean

   Beschreibung
         whether to allow front-end editing of target groups

   Standardwert
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_seminars\_pi1]
