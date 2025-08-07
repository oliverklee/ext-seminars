..  include:: /Includes.rst.txt
..  index:: Events; AfterSlugGeneratedEvent
..  _AfterSlugGeneratedEvent:

=======================
AfterSlugGeneratedEvent
=======================

The PSR-14 event :php:`\OliverKlee\Seminars\Seo\Event\AfterSlugGeneratedEvent`
gets triggered after a slug has been generated or updated for an event record,
and before the slug gets written to the database.
Listeners may overwrite the slug if desired.

Example
=======

Registration of the event listener in the extension's :file:`Services.yaml`:

..  literalinclude:: _AfterSlugGeneratedEvent/_Services.yaml
    :language: yaml
    :caption: EXT:my_extension/Configuration/Services.yaml

The corresponding event listener class:

..  literalinclude:: _AfterSlugGeneratedEvent/_SlugGeneratorEventListener.php
    :language: php
    :caption: EXT:my_extension/Classes/EventListener/Seo/SlugGeneratorEventListener.php
