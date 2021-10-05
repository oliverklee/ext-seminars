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
         skipRegistrationCollisionCheck

   Datentyp
         boolean

   Beschreibung
         whether the registration collision check should be skipped for all
         events

   Standardwert
         0


.. container:: table-row

   Eigenschaft
         templateFile

   Datentyp
         string

   Beschreibung
         File name of the HTML template for e-mail

   Standardwert
         EXT:seminars/Resources/Private/Templates/Mail/e-mail.html


.. container:: table-row

   Eigenschaft
         salutation

   Datentyp
         string

   Beschreibung
         switch whether to use formal/informal language for some shared code
         (in e-mails, some labels and some error messages).Allowed values
         are:formal \| informal

   Standardwert
         formal


.. container:: table-row

   Eigenschaft
         hideFieldsInThankYouMail

   Datentyp
         string

   Beschreibung
         comma-separated list of section names that shouldn't be displayed in
         the thank-you e-mail to the user

         allowed values are in: hello, title, uid, ticket\_id, price, seats, to
         tal\_price,attendees\_names,lodgings,accommodation,foods,food,checkbox
         es, kids, accreditation\_number, credit\_points, date, time, place,
         room, paymentmethod, billing\_address,interests,url,
         footer,planned\_disclaimer,unregistration\_notice

   Standardwert
         credit\_points,billing\_address,kids,planned\_disclaimer


.. container:: table-row

   Eigenschaft
         ::

            cssFileForAttendeeMail

   Datentyp
         string

   Beschreibung
         the CSS file for the HTML e-mail to the attendees

   Standardwert
         EXT:seminars/Resources/Private/CSS/thankYouMail.css


.. container:: table-row

   Eigenschaft
         generalPriceInMail

   Datentyp
         boolean

   Beschreibung
         whether to use the label “Price” for the standard price (instead of
         “standard price”) in e-mail to the participant

   Standardwert


.. container:: table-row

   Eigenschaft
         hideFieldsInNotificationMail

   Datentyp
         string

   Beschreibung
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

   Standardwert


.. container:: table-row

   Eigenschaft
         showSeminarFieldsInNotificationMail

   Datentyp
         string

   Beschreibung
         comma-separated list of field names from seminars that should be
         mentioned in the notification e-mail to the organizers (in the
         “seminardata” section)allowed values are in: uid, event\_type, title,
         subtitle, titleanddate, date, time, accreditation\_number,
         credit\_points, room, place, speakers, price\_regular,
         price\_regular\_early, price\_special, price\_special\_early,
         attendees,
         needs\_registration,allows\_multiple\_registrations,attendees\_min,
         attendees\_max, vacancies, enough\_attendees, is\_full, notes

   Standardwert
         title,uid,event\_type,date,place,price\_regular,price\_regular\_early,
         price\_special,price\_special\_early,attendees,vacancies,enough\_atten
         dees,is\_full


.. container:: table-row

   Eigenschaft
         showFeUserFieldsInNotificationMail

   Datentyp
         string

   Beschreibung
         comma-separated list of field names from fe\_users that should be
         mentioned in the notification e-mail to the organizers (in the
         “feuserdata” section)allowed values are all column names from
         fe\_users.

   Standardwert
         username,name,email,address,zip,city,telephone


.. container:: table-row

   Eigenschaft
         showAttendanceFieldsInNotificationMail

   Datentyp
         string

   Beschreibung
         comma-separated list of field names from attendances that should be
         mentioned in the notification e-mail to the organizers (in the
         “attendancedata” section)allowed values are in: uid, interests,
         expectations, background\_knowledge, lodgings, accommodation, foods,
         food, known\_from, notes, checkboxes, price, seats, total\_price,
         attendees\_names, kids, method\_of\_payment, gender, name, address,
         zip, city, country, telephone, email

   Standardwert
         uid,price,seats,total\_price,method\_of\_payment,gender,name,address,z
         ip,city,country,telephone,email,interests,expectations,background\_kno
         wledge,known\_from,notes


.. container:: table-row

   Eigenschaft
         sendAdditionalNotificationEmails

   Datentyp
         boolean

   Beschreibung
         Whether to send the additional notification e-mails to the organizers
         or not. Additional notification mails are sent if for example an event
         gets full.

   Standardwert
         1 (= active)


.. container:: table-row

   Eigenschaft
         sendNotification

   Datentyp
         boolean

   Beschreibung
         Whether to send a notification to the organizers if a user has
         registered.

   Standardwert
         1 (= active)


.. container:: table-row

   Eigenschaft
         sendNotificationOnUnregistration

   Datentyp
         boolean

   Beschreibung
         Whether to send a notification to the organizers if a user has
         unregistered.

   Standardwert
         1 (= active)


.. container:: table-row

   Eigenschaft
         sendNotificationOnRegistrationForQueue

   Datentyp
         boolean

   Beschreibung
         Whether to send a notification to the organizers if someone registered
         for the queue.

   Standardwert
         1 (= active)


.. container:: table-row

   Eigenschaft
         sendNotificationOnQueueUpdate

   Datentyp
         boolean

   Beschreibung
         Whether to send a notification to the organizers if the queue has been
         updated.

   Standardwert
         1 (= active)


.. container:: table-row

   Eigenschaft
         sendConfirmation

   Datentyp
         boolean

   Beschreibung
         Whether to send a confirmation to the user after the user has
         registered.

   Standardwert
         1 (= active)


.. container:: table-row

   Eigenschaft
         sendConfirmationOnUnregistration

   Datentyp
         boolean

   Beschreibung
         Whether to send a confirmation to the user if the user has
         unregistered.

   Standardwert
         1 (= active)


.. container:: table-row

   Eigenschaft
         sendConfirmationOnRegistrationForQueue

   Datentyp
         boolean

   Beschreibung
         Whether to send a confirmation to the user if the user has registered
         for the queue.

   Standardwert
         1 (= active)


.. container:: table-row

   Eigenschaft
         sendConfirmationOnQueueUpdate

   Datentyp
         boolean

   Beschreibung
         Whether to send a confirmation to the user if the queue has been
         updated.

   Standardwert
         1 (= active)


.. container:: table-row

   Eigenschaft
         addRegistrationCsvToOrganizerReminderMail

   Datentyp
         boolean

   Beschreibung
         Whether to add the CSV file of the registrations when sending the
         reminder e-mails to the organizers.

   Standardwert
         0 (=inactive)


.. container:: table-row

   Eigenschaft
         timeFormat

   Datentyp
         string

   Beschreibung
         the time format (in  *strftime* format)

   Standardwert
         %H:%M


.. container:: table-row

   Eigenschaft
         dateFormatY

   Datentyp
         string

   Beschreibung
         the  *strftime* format code to extract the year from a date string
         *(usually this shouldn't be changed)*

   Standardwert
         %Y


.. container:: table-row

   Eigenschaft
         dateFormatM

   Datentyp
         string

   Beschreibung
         the  *strftime* format code to extract the month from a date string
         *(usually this shouldn't be changed)*

   Standardwert
         %m.


.. container:: table-row

   Eigenschaft
         dateFormatD

   Datentyp
         string

   Beschreibung
         the  *strftime* format code to extract the day of month from a date
         string *(usually this shouldn't be changed)*

   Standardwert
         %d.


.. container:: table-row

   Eigenschaft
         dateFormatYMD

   Datentyp
         string

   Beschreibung
         the  *strftime* format code for the full date *(change this to your
         local date format)*

   Standardwert
         %d.%m.%Y


.. container:: table-row

   Eigenschaft
         dateFormatMD

   Datentyp
         string

   Beschreibung
         the  *strftime* format code for the month and day of month *(change
         this to your local date format)*

   Standardwert
         %d.%m.


.. container:: table-row

   Eigenschaft
         abbreviateDateRanges

   Datentyp
         boolean

   Beschreibung
         whether date ranges should be shortened when possible, for example

         **11.10.2005-13.10.2005** becomes  **11.-13.10.2005**

   Standardwert
         1


.. container:: table-row

   Eigenschaft
         currency

   Datentyp
         string

   Beschreibung
         ISO 4217 alpha 3 code of the currency to be used, must be valid

   Standardwert
         EUR


.. container:: table-row

   Eigenschaft
         showTimeOfRegistrationDeadline

   Datentyp
         boolean

   Beschreibung
         whether to also show the time of the registration deadline instead of
         just the date

   Standardwert
         0


.. container:: table-row

   Eigenschaft
         showTimeOfEarlyBirdDeadline

   Datentyp
         boolean

   Beschreibung
         whether to also show the time of the early bird deadline instead of
         just the date

   Standardwert
         0


.. container:: table-row

   Eigenschaft
         showTimeOfUnregistrationDeadline

   Datentyp
         boolean

   Beschreibung
         whether to also show the time of the unregistration deadline instead
         of just the date

   Standardwert
         0


.. container:: table-row

   Eigenschaft
         unregistrationDeadlineDaysBeforeBeginDate

   Datentyp
         integer

   Beschreibung
         Number of days before the start of an event until unregistration is
         possible. (If you want to disable this feature just leave the value
         empty.)

   Standardwert


.. container:: table-row

   Eigenschaft
         allowRegistrationForStartedEvents

   Datentyp
         boolean

   Beschreibung
         whether registration should be possible even if an event has already
         started

   Standardwert
         0


.. container:: table-row

   Eigenschaft
         allowRegistrationForEventsWithoutDate

   Datentyp
         Boolean

   Beschreibung
         Whether registration for events without a date is possible

   Standardwert
         0


.. container:: table-row

   Eigenschaft
         allowUnregistrationWithEmptyWaitingList

   Datentyp
         Boolean

   Beschreibung
         Whether unregistration is possible even when there are no
         registrations on the waiting list yet.

   Standardwert
         0


.. container:: table-row

   Eigenschaft
         showVacanciesThreshold

   Datentyp
         integer

   Beschreibung
         If there are at least this many vacancies, “enough” (localized) is
         displayed instead of the exact number.

         Set this to a number higher than the highest number of vacancies if
         you want the exact number to be always displayed.

   Standardwert
         10


.. container:: table-row

   Eigenschaft
         showToBeAnnouncedForEmptyPrice

   Datentyp
         boolean

   Beschreibung
         whether events that have no standard price set should have “to be
         announced” as price instead of “free”

   Standardwert
         0


.. container:: table-row

   Eigenschaft
         charsetForCsv

   Datentyp
         string

   Beschreibung
         The charset for the CSV export, e.g., utf-8, iso-8859-1 or
         iso-8859-15. The default is iso-9959-15 because Excel has problems
         with importing utf-8.

   Standardwert
         Iso-8859-15


.. container:: table-row

   Eigenschaft
         filenameForEventsCsv

   Datentyp
         string

   Beschreibung
         the filename proposed for CSV export of event lists

   Standardwert
         events.csv


.. container:: table-row

   Eigenschaft
         filenameForRegistrationsCsv

   Datentyp
         string

   Beschreibung
         the filename proposed for CSV export of registration lists

   Standardwert
         registrations.csv


.. container:: table-row

   Eigenschaft
         fieldsFromEventsForCsv

   Datentyp
         string

   Beschreibung
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

   Standardwert
         uid,title,subtitle,description,event\_type,date,time,place,room,speake
         rs,price\_regular,attendees,attendees\_max,vacancies,is\_full


.. container:: table-row

   Eigenschaft
         fieldsFromFeUserForCsv

   Datentyp
         string

   Beschreibung
         comma-separated list of field names from fe\_users that will be used
         for CSV export

   Standardwert
         name,company,address,zip,city,country,telephone,email


.. container:: table-row

   Eigenschaft
         fieldsFromAttendanceForCsv

   Datentyp
         string

   Beschreibung
         comma-separated list of field names from tx\_seminars\_attendances
         that will be used for CSV export

   Standardwert
         uid,price,total\_price,method\_of\_payment,interests,expectations,back
         ground\_knowledge,known\_from,notes


.. container:: table-row

   Eigenschaft
         showAttendancesOnRegistrationQueueInCSV

   Datentyp
         boolean

   Beschreibung
         wether to show attendances on the registration queue in the CSV export
         or not

   Standardwert
         0


.. container:: table-row

   Eigenschaft
         fieldsFromFeUserForEmailCsv

   Datentyp
         string

   Beschreibung
         comma-separated list of field names from fe\_users that will be used
         for CLI CSV export

   Standardwert
         name,company,address,zip,city,country,telephone,email


.. container:: table-row

   Eigenschaft
         fieldsFromAttendanceForEmailCsv

   Datentyp
         string

   Beschreibung
         comma-separated list of field names from tx\_seminars\_attendances
         that will be used for CLI CSV export

   Standardwert
         uid,price,total\_price,method\_of\_payment,interests,expectations,back
         ground\_knowledge,known\_from,notes


.. container:: table-row

   Eigenschaft
         showAttendancesOnRegistrationQueueInEmailCsv

   Datentyp
         boolean

   Beschreibung
         whether to show attendances on the registration queue in the CLI CSV
         export or not

   Standardwert
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

   Eigenschaft
         sendCancelationDeadlineReminder

   Datentyp
         boolean

   Beschreibung
         whether to send a cancelation deadline reminder to the organizers

   Standardwert
         0


.. container:: table-row

   Eigenschaft
         sendEventTakesPlaceReminderDaysBeforeBeginDate

   Datentyp
         integer

   Beschreibung
         how many days before an events' begin date the organizers should be
         reminded about this event via e-mail, zero disables the reminder

   Standardwert
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

   Eigenschaft
         attendancesPID

   Datentyp
         page\_id

   Beschreibung
         PID des Ordners, in dem Anmeldungen gespeichert werden

   Standardwert
         None


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_seminars]
