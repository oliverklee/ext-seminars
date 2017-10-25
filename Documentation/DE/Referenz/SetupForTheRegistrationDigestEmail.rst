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


Einstellungen für die Email mit der Anmeldungsübersicht
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Diese Einstellungen können ausschließlich per TypoScript-Setup
im Namespace plugin.tx\_seminars.registrationDigestEmail
gesetzt werden, nicht per Flexforms.

Diese Einstellungen wirken sich ausschließlich auf den seminars-Scheduler-Task
aus.

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
        enable

    Datentyp
        boolean

    Beschreibung
        ob der seminars-Scheduler-Task die Anmeldungszusammenfassungen verschicken soll

    Standardwert
        0


.. container:: table-row

    Eigenschaft
        fromEmail

    Datentyp
        string

    Beschreibung
        Mailadresse des Absenders

    Standardwert



.. container:: table-row

    Eigenschaft
        fromName

    Datentyp
        string

    Beschreibung
        Name des Absenders (optional)

    Standardwert


.. container:: table-row

    Eigenschaft
        toEmail

    Datentyp
        string

    Beschreibung
        Mailadresse des Empfängers

    Standardwert



.. container:: table-row

    Eigenschaft
        toName

    Datentyp
        string

    Beschreibung
        Name des Empfängers (optional)

    Standardwert


.. container:: table-row

    Property
        htmlTemplate

    Data type
        string

    Description
        Pfad zum Fluid-Template für die HTML-Email

    Default
        EXT:seminars/Resources/Private/Templates/Mail/RegistrationDigest.html


.. container:: table-row

    Property
        plaintextTemplate

    Data type
        string

    Description
        path to the fluid template for the plaintext email

    Default
        Pfad zum Fluid-Template für die Text-Email



.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_seminars.registrationDigestEmail]
