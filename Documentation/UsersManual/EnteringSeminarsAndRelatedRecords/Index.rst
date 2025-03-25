Entering seminars and related records
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You must set one organizer for each seminar. In addition, you can set
one or more speakers and one or more seminar sites (real places this
time) for a seminar. If you want to enter a seminar with an organizer,
speakers or seminar sites that haven't been entered into the database
yet, it's a good idea to enter those first before entering the
seminar.


Entering and managing organizers
""""""""""""""""""""""""""""""""

At the beginning, you should enter some basic records. Usually, the
users who organize and manage the seminars don't need to alter these
records any more, so it would be a good idea to store them in a system
folder which most back-end users cannot write to.

Add one or more organizer records to the page that will hold the
organizer records.

An organizer record will hold the basic information about the person
(or the team) who organizes a seminar. Note that the organizer doesn't
need to be the same as the speaker (and usually isn't the same).
Instead, the organizer is the person who reserves the seminar site,
collects the fee, manages the registrations and so on.

An organizer record contains the following fields:

- **name** (required), will be shown on the front end

- homepage URL including the http://, will be shown on the front end

- **email address** (required, must be valid), notifications of new
  registrations will be sent to this address, this address will be used
  as From: address for the confirmation emails when someone registers
  for a workshop, will not be shown on the front end

- footer text that will be put at the end of all emails sent by this
  organizer

- a sys folder where registration records for events should be stored
  for which this organizer is listed first (leave this empty to use the
  default folder set via TS setup)

If you intend to manage payments and you would like to records which
method of payment has been used, you can enter records for those. They
include the following fields:

- **title** (required)

- detailed description (will be included in the confirmation email on
  registration)


Entering and managing speakers
""""""""""""""""""""""""""""""

On the system folder that should contain the speaker records, you can
create speaker records.

The following fields are public and will be displayed on the front
end:

- **name** (required)

- the speaker's organization or company

- homepage URL, including the http://

- description (you can use HTML in this field)

The following fields are for your internal purposes only and don't get
displayed on the front end:

- internal notes

- address

- private phone number

- work phone number

- mobile phone number

- email address

Speakers can be listed in event record in four different roles: As
speakers, partners, tutors or leaders. Only the first role will be
visible in the list view whereas all roles are visible in the single
view. Apart from that, the only difference is under which heading the
speakers will be listed.


Entering and managing event types
"""""""""""""""""""""""""""""""""

On the same system folder that contains the speakers and organizers,
you can create event types. At the moment, an event type record
consists of only a title field. You can assign none or exactly one
event type to an event record. If you assign no event type to an
event, the default event type from TS setup will be used.

The field is hidden in the list view by default.


Entering and managing categories
""""""""""""""""""""""""""""""""

On the same system folder that contains the speakers and organizers,
you can create categories. At the moment, a category record consists
of a title field and an icon field. You can assign none or multiple
categories to an event record.


Entering and managing target groups
"""""""""""""""""""""""""""""""""""

On the same system folder that contains the speakers and organizers,
you can create target groups. At the moment, a target group record
consists of only a title field. You can assign none or multiple target
groups to an event record.


Entering and managing seminar sites
"""""""""""""""""""""""""""""""""""

The following fields are public and will be displayed on the front
end:

- **title** (required)

- address (you can use HTML in this field)

- country (from pre-filled list)

- homepage URL, including the http://

- directions (you can use HTML in this field)

The field “internal notes” is only for your internal use and doesn't
get displayed on the front end.

Note that in a seminar record, you can add a room number in addition
to the general seminar site(s).


Entering and managing seminars
""""""""""""""""""""""""""""""

You can add the seminar records to the page(s) that should contain
these records. Note that you have to add those pages as the DB
starting point when adding a Seminar Manager content elements or the
seminars won't get listed.

**Note:** If you have events that occur more than once, it is highly
recommended to enter one event topic and then just enter event date
records, selecting the already entered topic record. You'll save a lot
of typing (or copying and pasting) that way.

You can enter the following data for a seminar, which will be used for
the front end:

- record type: single event (= complete event record), only a topic or a
  date for a topic

- hide the seminar (default: disabled)

- when to display the seminar in the front end (don't confuse this with
  the hours when the seminars take place,  **only enable this
  excludefield for users who are not apt to get these fields mixed** )

- **title** (required) (don't use HTML in this field)

- subtitle

- image (only available for record types single event and topic)

- categories

- teaser text (you can use some HTML in this field)

- description (you can use HTML in this field)

- event type

- separate details page  **(using this field will in effect disable
  online registration for this event)**

- accreditation number according to the Akkreditierungsverordnung Hessen
  *(excludefield)*

- number of credit points  *(excludefield)*

- first seminar day and the beginning time in the format hh:mm dd-mm-
  yyyy, semi-required (events without a start day technically are
  considered to be sometime in the future)

- last seminar day and the closing time in the format hh:mm dd-mm-yyyy
  (if you have an open-ended event, just leave this field empty)

- registration deadline in the format hh:mm dd-mm-yyyy (Set this date if
  users shouldn't be allowed to register for this event after this
  date/time. If not set, the seminar starting time will be the deadline
  instead.) Please enter a date/time smaller than the starting time.

- early bird deadline in the format hh:mm dd-mm-yyyy (Set this date if
  users should be able to get a better price before this deadline. If
  not set, no early bird prices will be used at all!). Please enter a
  date/time smaller than the starting time.

- License expiry: how long a registration will be valid for event
  dependencies

- the site(s) where the seminar takes place, select one or more sites
  from the database (not required), when the seminar takes place on
  different sites, add to the description which site will be used on
  which day

- room number (if the seminar site has more than one room or the room is
  hard to find)

- additional informations about time and place(s) (not required, no HTML
  allowed)

- speaker(s), select one or more speakers from the database (not
  required)

- partner(s) (which are in fact relations to speaker records), the same
  as speakers, but they will be displayed under a different heading

- tutor(s) (which are in fact relations to speaker records), the same as
  speakers, but they will be displayed under a different heading

- leader(s) (which are in fact relations to speaker records), the same
  as speakers, but they will be displayed under a different heading

- default price, without the currency name

- default price (early bird), without the currency name

- special price (will only get displayed if it is not 0.00), without the
  currency name

- special price (early bird, will only get displayed if it is not 0.00),
  without the currency name

- additional information about the event, payment workflow etc. can be
  entered in this RTE enabled field (you can use HTML here)

- any checkbox options to show in the registration form (you can select
  any previously entered checkbox records here)

- whether the “traveling terms” (the second “terms” checkbox) should be
  displayed in the registration form

- **allowed payment methods** for this seminar (they will be listed in
  the details page and in the confirmation email to the attendee, so
  **you must set at least the allowed payment methods if you want to
  have them to be mentioned via email to the attendees** )

- **organizer(s)** , select one or more organizers from the database
  (required).

- whether it is possible for FE user to register more than once for this
  event (this is off by default)

- how many registration are necessary for the seminar to be full enough
  to take place

- the maximal number of registrations before the seminar is completely
  full

- lodging options that will be available for selection in the
  registration form

- topics that are required for registering for this event (only for
  topic records)

- topics for which this topic is required (only for topic records)

Note that the beginning and end date/time include both the date of the
first and last day as well as the seminar times. If the seminar times
are different on some days, please add a little overview in the
“additional times and places” field. (For a later version of this
extension, it is planned to have allow for different time slots on
different days.)

If you don't know the seminar hours yet, enter 00:00 as starting and
closing time. If the event is open-ended, just leave the end date/time
field empty.

In addition, you can put internal notes into the seminar record. The
internal notes don't get published on the front end.

The following fields are automatically calculated (and get updated
each time a seminar record is saved):

- current number of registrations, including unpaid registrations

- whether the seminar already has enough registrations to take place

- whether the seminar is full

The following fields can be searched using the search box in the list
view:

- title

- subtitle

- description

- accreditation number

**Teaser text:** This field will only be displayed in the list view
and usually is hidden. It is intended to be used with a user-tailored
HTML template for the list view where a teaser text fits in better.


Entering registration (attendance) records
""""""""""""""""""""""""""""""""""""""""""

Each registration to an event creates an attendance record. These
records are used internally and not directly shown in the front end.
The only fields you need to manually change in an attendance record
are the payment date and whether the person has really attended. All
other fields should not be changed manually!  **This will change in
the future! We plan to implement some functions in the new back-end
module that assist the organizer.**

- title

- user

- seminar

- price

- total price

- datepaid

- method\_of\_payment

- Bank data:

  - account\_number

  - bank\_code

  - bank\_name

  - account\_owner

- Billing address:

  - name

  - address

  - zip

  - city

  - country

  - telephone

  - email

- been\_there

- interests

- expectations

- background\_knowledge

- selected lodging options

- accommodation (text)

- food

- known\_from

- notes

- seats

- attendees\_names

- kids

- lodgings


Using lodging and food options
""""""""""""""""""""""""""""""

You can create “lodging options” and “food options” records that will
be available in the registration form. After you have created these
records, you can select them in the event records; the corresponding
options then will be displayed in the registration for for this event
and get saved in the registration record.


Canceling events
""""""""""""""""

In case the speaker is ill or there are not enough registrations, you
can mark an event as canceled by checking “Has been canceled” in the
seminar record. This will mark the event as canceled in the front end
(the default style in the list view is stricken through plus a message
in the single view). You still need to manually notify and refund the
attendees who have registered so far.


Assigning event numbers
"""""""""""""""""""""""

There are two common ways for assigning numbers to your event:

#. If you just want to have automatically assigned, unique, numeric
   numbers for your events, you can use the UID field of the event
   record.

#. If you would like to assign the numbers yourself or you need to have
   non-numeric event IDs, you can use the “accreditation number” field
   and change the front-end and email labels accordingly (see the
   corresponding section in this manual about how to do this). In this
   case, you need to make sure yourself that the IDs are unique.
