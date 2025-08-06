..  include:: /Includes.rst.txt
..  index:: Events; BeforeAttendeeDownloadSentEvent
..  _BeforeAttendeeDownloadSentEvent:

===============================
BeforeAttendeeDownloadSentEvent
===============================

The PSR-14 event
:php:`\OliverKlee\Seminars\Controller\Event\BeforeAttendeeDownloadSentEvent`
gets triggered after the stream with the contents for a file download for an
attendee has been created, but before the stream gets returned with the
HTTP response.

Listeners may provide a stream with different contents if desired, for example
for adding a watermark or logo.

Example
=======

Registration of the event listener in the extension's :file:`Services.yaml`:

..  literalinclude:: _BeforeAttendeeDownloadSentEvent/_Services.yaml
    :language: yaml
    :caption: EXT:my_extension/Configuration/Services.yaml

The corresponding event listener class:

..  literalinclude:: _BeforeAttendeeDownloadSentEvent/_BeforeAttendeeDownloadSentEventListener.php
    :language: php
    :caption: EXT:my_extension/Classes/EventListener/Controller/BeforeAttendeeDownloadSentEventListener.php
