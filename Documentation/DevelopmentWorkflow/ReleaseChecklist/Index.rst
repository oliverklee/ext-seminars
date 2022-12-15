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


Release checklist
^^^^^^^^^^^^^^^^^

The following points are checked before a new version is released.
This workflow is started as soon as all open to-do items for the
upcoming version are done. Responsible for this is the chief developer
(Oliver Klee).

#. If this is a major release, drop this extension’s DB tables and re-
   create them.

#. Run all unit tests.

#. Remove completed tasks from the “Known Problems” part of this manual.

#. Enter the release date for the current milestone in the changelog.

#. Remove the directory tests/ and all .svn directories.

#. Check the Extension Manager if there are no warnings.

#. Generate a new ext\_emconf.php (updating the MD5 hashes for all
   files).

#. Upload the extension to the TER.

#. Check in the actual ext\_emconf.php to the SVN (comment: new version).

#. Create an SVN tag.

#. If this is a major release:

   #. Create an SVN branch.

   #. Enter the next milestone in the bug tracker.

   #. Change the version number in ext\_emconf.php to x.y.99 and check in
      the changes to the trunk (not the branch!).

   #. Move maintenance bugs to the next maintenance version.

   #. Remove the old upgrade notes in the trunk manual and new notes for the
      next version.

#. Enter the new (upcoming) version into changelog.txt.

#. Enter the just-released version in the bug tracker.

#. **Important:** Wait until the new version appears in the TER (this may
   take some time).

#. Update the extension on the `translation server
   <http://translation.typo3.org/>`_ (Mario)

#. Inform the persons that are known users of this extension and persons
   that were in contact with the development team (concerning this
   extension).

#. Spread the official announcement to the newsgroup.

#. Do a party :-)
