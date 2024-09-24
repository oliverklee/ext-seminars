Setup for the registration digest email
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

These configuration options can only be set via TypoScript setup
within the plugin.tx\_seminars.registrationDigestEmail namespace,
not via flexforms.

These settings only affect the seminars Scheduler task.

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
        enable

    Data type
        boolean

    Description
        whether to send out the emails when the seminars Scheduler task is executed

    Default
        0


.. container:: table-row

    Property
        fromEmail

    Data type
        string

    Description
        email address of the sender

    Default



.. container:: table-row

    Property
        fromName

    Data type
        string

    Description
        name of the sender (optional)

    Default


.. container:: table-row

    Property
        toEmail

    Data type
        string

    Description
        email address of the recipient

    Default



.. container:: table-row

    Property
        toName

    Data type
        string

    Description
        name of the recipient (optional)

    Default


.. container:: table-row

    Property
        htmlTemplate

    Data type
        string

    Description
        path to the fluid template for the HTML email

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
        EXT:seminars/Resources/Private/Templates/Mail/RegistrationDigest.txt


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_seminars.registrationDigestEmail]
