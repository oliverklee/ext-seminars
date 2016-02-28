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


Das HTML-Template ändern
^^^^^^^^^^^^^^^^^^^^^^^^

Die extension benutzt verschiedene Templates:

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   template path
         template path

   contents
         contents

   TS setup variable
         TS setup variable


.. container:: table-row

   template path
         Resources/Private/Templates/Mail/e-mail.html

   contents
         automatic e-mails

   TS setup variable
         plugin.tx\_seminars.templateFile ( *not* in flexforms)


.. container:: table-row

   template path
         pi1/seminars\_pi1.tmpl

   contents
         most front-end output

   TS setup variable
         plugin.tx\_seminars **\_pi1** .templateFile (also in flexforms)


.. container:: table-row

   template path
         Resources/Private/Templates/FrontEnd/RegistrationEditor.html

   contents
         event registration form

   TS setup variable
         plugin.tx\_seminars **\_pi1** .registrationEditorTemplateFile


.. container:: table-row

   template path
         Resources/Private/Templates/FrontEnd/EventEditor.html

   contents
         event editing form

   TS setup variable
         plugin.tx\_seminars **\_pi1** .eventEditorTemplateFile


.. ###### END~OF~TABLE ######

Note: Do  *not* change the HTML templates directly in the extension
directory as then your changes will be overwritten when you upgrade
the extension to a new version. Make a copy and modify the copy
instead:

#. Copy the corresponding \*.tmpl file to a convenient directory, e.g.to
   fileadmin/template/.

#. Set the corresponding TS setup variable to the path of your new
   template. For the pi1 template, you can also use the flexforms of the
   plug-in for setting the location.

#. Change the template to your liking.
