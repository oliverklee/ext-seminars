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


Setup common for front-end plug-in and back-end module in plugin.tx\_seminars
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You can configure the plug-in using your TS template setup in the form
plugin.tx\_seminars. *property = value.* The values in this table can
only be configured using your TypoScript setup, but not via flexforms.

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
         skipRegistrationCollisionCheck

   Data type
         boolean

   Description
         whether the registration collision check should be skipped for all
         events

   Default
         0


.. container:: table-row

   Property
         templateFile

   Data type
         string

   Description
         File name of the HTML template for e-mail

   Default
         EXT:seminars/Resources/Private/Templates/Mail/e-mail.html


.. container:: table-row

   Property
         salutation

   Data type
         string

   Description
         switch whether to use formal/informal language for some shared code
         (in e-mails, some labels and some error messages).Allowed values
         are:formal \| informal

   Default
         formal


.. container:: table-row

   Property
         hideFieldsInThankYouMail

   Data type
         string

   Description
         comma-separated list of section names that shouldn't be displayed in
         the thank-you e-mail to the user

         allowed values are in: hello, title, uid, ticket\_id, price, seats, to
         tal\_price,attendees\_names,lodgings,accommodation,foods,food,checkbox
         es, kids, accreditation\_number, credit\_points, date, time, place,
         room,paymentmethod, billing\_address,interests,url,
         footer,planned\_disclaimer,unregistration\_notice

   Default
         credit\_points,billing\_address,kids,planned\_disclaimer


.. container:: table-row

   Property
         ::

            cssFileForAttendeeMail

   Data type
         string

   Description
         the CSS file for the HTML e-mail to the attendees

   Default
         EXT:seminars/Resources/Private/CSS/thankYouMail.css


.. container:: table-row

   Property
         generalPriceInMail

   Data type
         boolean

   Description
         whether to use the label “Price” for the standard price (instead of
         “standard price”) in e-mail to the participant

   Default


.. container:: table-row

   Property
         hideFieldsInNotificationMail

   Data type
         string

   Description
         Comma-separated list of section names from the registration that
         shouldn't be displayed in the notification e-mail to the organizers.
         These fields are the big blocks in that e-mail, and some are further
         divided.

         Allowed values are in:

         - summary: the attendee's name, the event title and the event date

         - seminardata: date from the seminar record, configurable via
           *showSeminarFieldsInNotificationMail*

         - feuserdata: data from the front-end user record, configurable via
           *showFeUserFieldsInNotificationMail*

         - attendancedata: data from the attendance record, configurable via
           *showAttendanceFieldsInNotificationMail*

   Default


.. container:: table-row

   Property
         showSeminarFieldsInNotificationMail

   Data type
         string

   Description
         comma-separated list of field names from seminars that should be
         mentioned in the notification e-mail to the organizers (in the
         “seminardata” section)allowed values are in: uid, event\_type, title,
         subtitle, titleanddate, date, time, accreditation\_number,
         credit\_points, room, place, speakers, price\_regular,
         price\_regular\_early, price\_special, price\_special\_early,
         attendees,allows\_multiple\_registrations,attendees\_min,
         attendees\_max, vacancies, enough\_attendees, is\_full, notes

   Default
         title,uid,event\_type,date,place,price\_regular,price\_regular\_early,
         price\_special,price\_special\_early,attendees,vacancies,enough\_atten
         dees,is\_full


.. container:: table-row

   Property
         showFeUserFieldsInNotificationMail

   Data type
         string

   Description
         comma-separated list of field names from fe\_users that should be
         mentioned in the notification e-mail to the organizers (in the
         “feuserdata” section)allowed values are all column names from
         fe\_users.

   Default
         username,name,email,address,zip,city,telephone


.. container:: table-row

   Property
         showAttendanceFieldsInNotificationMail

   Data type
         string

   Description
         comma-separated list of field names from attendances that should be
         mentioned in the notification e-mail to the organizers (in the
         “attendancedata” section)allowed values are in: uid, interests,
         expectations, background\_knowledge, lodgings, accommodation, foods,
         food, known\_from, notes, checkboxes, price, seats, total\_price,
         attendees\_names, kids, method\_of\_payment, gender, name, address,
         zip, city, country, telephone, email

   Default
         uid,price,seats,total\_price,method\_of\_payment,gender,name,address,z
         ip,city,country,telephone,email,interests,expectations,background\_kno
         wledge,known\_from,notes


.. container:: table-row

   Property
         sendAdditionalNotificationEmails

   Data type
         boolean

   Description
         Whether to send the additional notification e-mails to the organizers
         or not. Additional notification mails are sent if for example an event
         gets full.

   Default
         1 (= active)


.. container:: table-row

   Property
         sendNotification

   Data type
         boolean

   Description
         Whether to send a notification to the organizers if a user has
         registered.

   Default
         1 (= active)


.. container:: table-row

   Property
         sendNotificationOnUnregistration

   Data type
         boolean

   Description
         Whether to send a notification to the organizers if a user has
         unregistered.

   Default
         1 (= active)


.. container:: table-row

   Property
         sendNotificationOnRegistrationForQueue

   Data type
         boolean

   Description
         Whether to send a notification to the organizers if someone registered
         for the queue.

   Default
         1 (= active)


.. container:: table-row

   Property
         sendNotificationOnQueueUpdate

   Data type
         boolean

   Description
         Whether to send a notification to the organizers if the queue has been
         updated.

   Default
         1 (= active)


.. container:: table-row

   Property
         sendConfirmation

   Data type
         boolean

   Description
         Whether to send a confirmation to the user after the user has
         registered.

   Default
         1 (= active)


.. container:: table-row

   Property
         sendConfirmationOnUnregistration

   Data type
         boolean

   Description
         Whether to send a confirmation to the user if the user has
         unregistered.

   Default
         1 (= active)


.. container:: table-row

   Property
         sendConfirmationOnRegistrationForQueue

   Data type
         boolean

   Description
         Whether to send a confirmation to the user if the user has registered
         for the queue.

   Default
         1 (= active)


.. container:: table-row

   Property
         sendConfirmationOnQueueUpdate

   Data type
         boolean

   Description
         Whether to send a confirmation to the user if the queue has been
         updated.

   Default
         1 (= active)


.. container:: table-row

   Property
         addRegistrationCsvToOrganizerReminderMail

   Data type
         boolean

   Description
         Whether to add the CSV file of the registrations when sending the
         reminder e-mail to the organizers.

   Default
         0 (=inactive)


.. container:: table-row

   Property
         timeFormat

   Data type
         string

   Description
         the time format (in  *strftime* format)

   Default
         %H:%M


.. container:: table-row

   Property
         dateFormatYMD

   Data type
         string

   Description
         the  *strftime* format code for the full date *(change this to your
         local date format)*

   Default
         %d.%m.%Y


.. container:: table-row

   Property
         currency

   Data type
         string

   Description
         ISO 4217 alpha 3 code of the currency to be used, must be valid

   Default
         EUR


.. container:: table-row

   Property
         showTimeOfRegistrationDeadline

   Data type
         boolean

   Description
         whether to also show the time of the registration deadline instead of
         just the date

   Default
         0


.. container:: table-row

   Property
         showTimeOfEarlyBirdDeadline

   Data type
         boolean

   Description
         whether to also show the time of the early bird deadline instead of
         just the date

   Default
         0


.. container:: table-row

   Property
         showTimeOfUnregistrationDeadline

   Data type
         boolean

   Description
         whether to also show the time of the unregistration deadline instead
         of just the date

   Default
         0


.. container:: table-row

   Property
         unregistrationDeadlineDaysBeforeBeginDate

   Data type
         integer

   Description
         Number of days before the start of an event until unregistration is
         possible. (If you want to disable this feature just leave the value
         empty.)

   Default


.. container:: table-row

   Property
         allowRegistrationForStartedEvents

   Data type
         boolean

   Description
         whether registration should be possible even if an event has already
         started

   Default
         0


.. container:: table-row

   Property
         allowRegistrationForEventsWithoutDate

   Data type
         Boolean

   Description
         Whether registration for events without a date is possible

   Default
         0


.. container:: table-row

   Property
         allowUnregistrationWithEmptyWaitingList

   Data type
         Boolean

   Description
         Whether unregistration is possible even when there are no
         registrations on the waiting list yet.

   Default
         0


.. container:: table-row

   Property
         showVacanciesThreshold

   Data type
         integer

   Description
         If there are at least this many vacancies, “enough” (localized) is
         displayed instead of the exact number.

         Set this to a number higher than the highest number of vacancies if
         you want the exact number to be always displayed.

   Default
         10


.. container:: table-row

   Property
         showToBeAnnouncedForEmptyPrice

   Data type
         boolean

   Description
         whether events that have no standard price set should have “to be
         announced” as price instead of “free”,
         @deprecated #1786 will be removed in seminars 5.0

   Default
         0


.. container:: table-row

   Property
         charsetForCsv

   Data type
         string

   Description
         The charset for the CSV export, e.g., utf-8, iso-8859-1 or
         iso-8859-15. The default is iso-9959-15 because Excel has problems
         with importing utf-8.

   Default
         Iso-8859-15


.. container:: table-row

   Property
         filenameForEventsCsv

   Data type
         string

   Description
         the filename proposed for CSV export of event lists

   Default
         events.csv


.. container:: table-row

   Property
         filenameForRegistrationsCsv

   Data type
         string

   Description
         the filename proposed for CSV export of registration lists

   Default
         registrations.csv


.. container:: table-row

   Property
         fieldsFromEventsForCsv

   Data type
         string

   Description
         comma-separated list of field names from tx\_seminars\_seminars that
         will be used for CSV exportAllowed values are in:uid, tstamp, crdate,
         title, subtitle, teaser, description, event\_type,
         accreditation\_number, credit\_points, date, time,
         deadline\_registration, deadline\_early\_bird, place, room, lodgings,
         foods, speakers, partners, tutors, leaders, price\_regular,
         price\_regular\_early, price\_regular\_board, price\_special,
         price\_special\_early, price\_special\_board, additional\_information,
         payment\_methods, organizers, attendees\_min, attendees\_max,
         attendees, vacancies, enough\_attendees, is\_full, cancelled

   Default
         uid,title,subtitle,description,event\_type,date,time,place,room,speake
         rs,price\_regular,attendees,attendees\_max,vacancies,is\_full


.. container:: table-row

   Property
         fieldsFromFeUserForCsv

   Data type
         string

   Description
         comma-separated list of field names from fe\_users that will be used
         for CSV export

   Default
         name,company,address,zip,city,country,telephone,email


.. container:: table-row

   Property
         fieldsFromAttendanceForCsv

   Data type
         string

   Description
         comma-separated list of field names from tx\_seminars\_attendances
         that will be used for CSV export

   Default
         uid,price,total\_price,method\_of\_payment,interests,expectations,back
         ground\_knowledge,known\_from,notes


.. container:: table-row

   Property
         showAttendancesOnRegistrationQueueInCSV

   Data type
         boolean

   Description
         wether to show attendances on the registration queue in the CSV export
         or not

   Default
         0


.. container:: table-row

   Property
         fieldsFromFeUserForEmailCsv

   Data type
         string

   Description
         comma-separated list of field names from fe\_users that will be used
         for CLI CSV export

   Default
         name,company,address,zip,city,country,telephone,email


.. container:: table-row

   Property
         fieldsFromAttendanceForEmailCsv

   Data type
         string

   Description
         comma-separated list of field names from tx\_seminars\_attendances
         that will be used for CLI CSV export

   Default
         uid,price,total\_price,method\_of\_payment,interests,expectations,back
         ground\_knowledge,known\_from,notes


.. container:: table-row

   Property
         showAttendancesOnRegistrationQueueInEmailCsv

   Data type
         boolean

   Description
         whether to show attendances on the registration queue in the CLI CSV
         export or not

   Default
         0


.. container:: table-row

   Eigenschaft
         addExcelSpecificSeparatorLineToCsv

   Datentyp
         boolean

   Beschreibung
         whether to add the Excel-specific "sep=;" line to the CSV

   Standardwert
         0


.. container:: table-row

   Property
         sendCancelationDeadlineReminder

   Data type
         boolean

   Description
         whether to send a cancellation deadline reminder to the organizers

   Default
         0


.. container:: table-row

   Property
         sendEventTakesPlaceReminderDaysBeforeBeginDate

   Data type
         integer

   Description
         how many days before an events' begin date the organizers should be
         reminded about this event via e-mail, zero disables the reminder

   Default
         0


.. container:: table-row

   Property
         automaticSpecialPriceForSubsequentRegistrationsBySameUser

   Data type
         boolean

   Description
         Set this to 1 to hide the special price for the first registration of a
         user and to automatically offer the special price for the 2nd, 3rd etc.
         registrations of the same user.

   Default
         0


.. container:: table-row

   Property
         attendancesPID

   Data type
         page\_id

   Description
         PID of the sysfolder where event registrations (attendances) will be
         stored

   Default
         None


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_seminars]
