# Change log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](https://semver.org/).

## x.y.z

### Added

### Changed
- Convert the first tests to nimut/testing-framework (#194, #195)
- Move to old tests to the "Legacy" namespace (#193)

### Deprecated

### Removed
- Remove unsupported properties from TCA type "select" (#191)

### Fixed
- Allow hiding the unregistration notice in the thank-you email (#185)
- Add more common files to the `.gitignore` (#184)

## 2.1.2

### Changed
- Copy some registration-related methods to the new Event model (#176)
- Clean up the extension icon SVG file (#171)
- Replace the last tabs with spaces (#170)
- Streamline ext_emconf.php (#168)

### Fixed
- Hide the number of vacancies after the registration deadline (#177)
- Also provide the extension icon in `Resources/` (#175)

## 2.1.1

### Removed
- Remove obsolete "checkbox" options from the TCA (#166)

### Fixed
- Fix SQL errors in MySQL strict mode (#165)

## 2.1.0

### Added
- Auto-release to the TER (#153)
- New hook interface and RegistrationEmailHookInterface (#150)
- New hook to post process attendee email in registration manager (#150)
- New hook to post process attendee email text in registration manager (#150)
- New hook to post process organizer email in registration manager (#150)
- New hook to post process additional email in registration manager (#150)
- Automatic prices for subsequent registrations (#144)
- Calculate collisions using the time slots (#139)

### Changed
- Split the TypoScript into several files (#151)

### Deprecated
- XClass hook Tx_Seminars_Service_RegistrationManager::modifyNotificationEmail has been replaced by RegistrationEmailHookInterface::postProcessOrganizerEmail (#150)
- Hook Tx_Seminars_Interface_Hook_Registration::modifyOrganizerNotificationEmail has been replaced by RegistrationEmailHookInterface::postProcessOrganizerEmail (#150)
- Hook Tx_Seminars_Interface_Hook_Registration::modifyAttendeeEmailText has been replaced by RegistrationEmailHookInterface::postProcessAttendeeEmailText (#150)
- Hook modifyThankYouEmail has been replaced by RegistrationEmailHookInterface::postProcessAttendeeEmail (#150)

### Removed
- Remove the "use page browser" switch in the EM (#135, #126)
- Remove the print functionality from the BE module (#119)

### Fixed
- Don't HTML-encode the data from the FE editor on saving (#162)
- Fix the inclusion of the JavaScript file (#161)
- Remove the deprecated _PADDING from TCEforms wizards (#160)
- Use the current composer names of static_info_tables (#159)
- Add a conflict with a PHP-7.0-incompatible static_info_tables version (#156)
- Update the composer package name of static-info-tables (#149)
- Fix crash in the CSV download (#140, #141)
- Make event.timeslots an integer DB field (#138)
- Update the documentation of the hooks (#134)
- Prevent IE from sending the registration form multiple times (#129, #130)
- Add allowed table for dependencies to the TCA (#123)
- Drop the deprecated doc->header() call (#120)
- Stop PHP-linting the removed Migrations/ folder (#118)

## 2.0.1

### Added
- Add some tests for the BE controller (#100)

### Changed
- Make the speaker gender a drop down in the TCA (#108)

### Removed
- Drop the palettes from the TCA (#107)
- Drop the unneeded ConfigurationController (#101)

### Fixed
- Fix more deprecation warnings (#116)
- Adapt the usage of core-provided labels to TYPO3 8.7 (#115)
- Update the BE module icon definition for TYPO3 8.7 (#114)
- Migrate the TCA wizards for TYPO3 8.7 (#113)
- Hide the test tables from BE user table permission lists (#112)
- Remove bogus additional parameter to translate() (#111)
- Update the term "sys folder" to "folder" (#110)
- Make the TCA "speakers" tab gender-neutral (#109)
- Provide empty values for optional selects in the TCA (#106)
- Fix link wizards in TCA in TYPO3 7.6 (#105)
- Fix error messages in the TCA for date/time fields (#104)
- Remove all echo statements from the BE module (#102)
- Fix more deprecation log warnings (#99)
- Improve the PHPDoc (#98)
- Fix the unit tests for the BE email form hooks (#95)

## 2.0.0

### Added
- Convert the BE module to the new format (#93)
- Add support for TYPO3 8.7 (#86)
- Add support for PHP 7.1 and 7.2 (#70)

### Changed
- Convert the BE classes to namespaces (#91)
- Always use a leading slash for fully-qualified class names (#88)
- Skip the tests for the old BE module in TYPO3 >= 8.7 (#75)
- Also allow oelib 3.x (#72)
- Require oelib >= 2.0.0 (#69)
- Require static_info_tables >= 6.4.0 (#68)
- Update to PHPUnit 5.3 (#66)

### Removed
- Drop the class alias map (#73)
- Require TYPO3 7.6 and drop support for TYPO3 6.2 (#67)
- Drop support for PHP 5.5 (#64)

### Fixed
- Adapt the unit tests for hooks and icons to TYPO3 8.7 (#84)
- Make the file link tests on Travis CI more robust (#87)
- Fix TCA deprecations in TYPO3 8.7 (#83)
- Use typesafe comparisons in the BE module (#82)
- Replace the deprecated flash message handling (#81)
- Replace deprecated BE methods (#80)
- Fix more code inspection warnings (#78)
- Replace usage of the deprecated issueCommand method (#77)
- Drop usage of the deprecated extRelPath method (#76)
- Update the content element wizard for TYPO3 8.7 (#72)
- Make the PHPUnit test runner configurable (#71)

## 1.5.0

### Added
- run the unit tests on TravisCI (#12)
- Add an SVG extension icon (#34)

### Changed
- Use more semantic PHPUnit methods (#60)
- Always use ::class for setExpectedException (#59)
- Use new instead of makeInstance for Tx_Oelib_List (#57)
- Always use spaces for indentation (#43)
- Require oelib >= 1.4.0 (#42)

### Removed
- Drop most of the destructors (#55)
- Drop the incorrect TYPO3 Core license headers (#41)

### Fixed
- Fix more PhpStorm code inspection warnings (#62)
- Update and clean up the TCA (#61)
- Fix method name casing and static call code warnings (#58)
- Fix more "undefined" code inspection warnings (#56)
- Fix code inspection warnings about undefined things (#54)
- Always use ::class (#53)
- Update use of deprecated rn_base configuration class (#52)
- Make the tests independent of the local time zone (#51)
- Make the tests independent of oelib dev fixture class (#50)
- Use real GIF files in the unit tests (#49)
- Get the CSV export tests to run on CLI (#48)
- Get the BE module tests to run on CLI (#47)
- Make the unit tests not depend on the current time of day (#46)
- Update the RTE configuration (#45)
- Provide time zone information in the iCal files (#44)
- Provide cli_dispatch.phpsh for 8.7 on Travis (#40)
- Adapt the calls to cObj->IMAGE to TYPO3 8.7 (#28)
- Increase the maximum file sizes for images (#27)

## 1.4.1

### Fixed
- Require typo3/minimal for installing TYPO3 (#38)
- fix the sorting in the daily registration digest (#23)
- require mkforms >= 3.0.14 (#22)

## 1.4.0

### Added
- configurable default value for the "register myself" checkbox (#19)
- Composer script for PHP linting (#7)
- add TravisCI builds

### Changed
- disable the legacy BE module in TYPO3 8LTS (#15)
- require mkforms >= 3.0.0 (#6)
- require static_info_tables >= 6.3.7 (#4)
- move the extension to GitHub

### Fixed
- automatically create the uploads folder on install (#20)
- add missing localized label for organizer in notification email (#18)
- require Scheduler in development mode (#17)
- skip the Scheduler-related tests if that extension is not installed (#16)
- fix autoloading when running the tests in the BE module in non-composer mode (#11)

## 1.3.0

The [change log up to version 1.3.0](Documentation/changelog-archive.txt)
has been archived.
