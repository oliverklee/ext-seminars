# Change log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](https://semver.org/).

## x.y.z

### Added

### Changed
- Namespace the legacy tests (#599)

### Deprecated

### Removed

### Fixed
- Stop converting class names to PSR-4 (#597)

## 3.3.0

### Added
- Add support for PHP 7.3 and 7.4 (#571)
- Add an `.editorconfig` file (#568)

### Changed
- Update the dependencies (#569, #570)
- Move the CI from Travis CI to GitHub Actions (#566, #567)MailNotifierConfigurationTest.php
- Change the default git branch from `master` to `main` (#565)

### Fixed
- Fix a test failure with mocks with PHP 7.4 (#578)
- Make date fields in the TCA clearable with MySQL strict mode (#548, #549)
- Add `.0` version suffixes to PHP version requirements (#547)

## 3.2.0

### Added
- Add new registration list CSV hook (#545)
- Add new data sanitization hook (#544)

## 3.1.0

### Added
- Add new date and time span formatting hooks (#460)

### Changed
- Move more tests to the new testing framework (#539)
- Use TYPO3 system mail as sender and current sender as reply to address in Mails (#511)

### Fixed
- Include the company in the billing address in the emails (#540)
- List the names of user groups in emails, not the UIDs (#537)
- Fix the alignment of labels with umlauts in the emails (#536)
- Use proper label tags for the terms checkboxes (#535)
- Check the dates for checkboxes, not the topics (#533)

## 3.0.2

### Added
- Add tests for the checkboxes in the registration form (#530)

### Changed
- Move more tests to the new testing framework (#524, #529)

### Fixed
- Attach the registration checkboxes to dates, not topics (#531)
- Clean up the registration form class a bit (#529)
- Always use Composer-installed versions of the dev tools (#528)
- Stop accessing `FrontEndController::loginUser` in TYPO3 9 (#526, #522)
- Downgrade to PHPUnit 6.5 (#525)
- Remove whitespace around the email salutation (#523, #205)

## 3.0.1

### Added
- Add `Registration::setFrontEndUser()` and cache it (#508)

### Changed
- Upgrade PHPUnit and nimut/testing-framework (#513)
- Merge the testing registration model into the regular model (#510)
- Move more tests to the new testing framework (#507, #509, #510, #516, #517)
- Change the casing of `Registration::setFrontEndUserUID` (#506)
- Allow empty user data for registrations (#505)
- Improve the code autoformatting (#502)

### Fixed
- Display target groups for dates in the single view (#518)
- Display the caption of registration option checkboxes (#515)
- Fix warnings in the `travis.yml` (#514)
- Do not cache `vendor/` on Travis CI (#512)
- Improve the manual (#500, #501, #504)

## 3.0.0

### Added
- Add tests for the DataMapper sanitization (#454, #456)
- Add new backend registration list hook (#437, #458)
- Add new selector widget hook (#436)
- Add documentation rendering using docker-compose (#432)
- Add new seminar list view hooks (#408)
- Add TypoScript linting (#10)
- Add `AbstractModel::comesFromDatabase` (#364)
- Add php-cs-fixer to the CI (#351)
- Add new seminar single view hook (#338, #345)
- Add hook base (#313, #336, #444, #459)
- Support TYPO3 9LTS (#322, #324)
- Add code sniffing and fixing (#319)
- Enable modifying the BagBuilder limitations in hooks (#308)
- Build with PHP 7.2 on Travis CI (#302)
- Display the name of the current functional test (#256)

### Changed
- Update the list of live examples (#498)
- !!! Use an image tag instead of background image in the detail view (#494)
- Move the data handling from the old model to the hook (#483, #484, #485, #493)
- Update registration email hooks (#445)
- Update documentation on hooks (#416)
- Sort the entries in the `.gitignore` and `.gitattributes` (#434)
- Require oelib >= 3.0.3 (#387, #430)
- Clean up the translation handling in the tests (#369, #376)
- !!! Merge the language files (#359, #361, #362, #363)
- Load the topic lazily in the old event model (#349)
- Use PHP 7.2 for the TER release script (#343)
- Require oelib >= 3.0.1 (#342)
- Rework the old model and bag (builder) architecture
  (#328, #329, #330, #331, #332, #333, #337, #339, #340, #341, #344, #346, #350, #354, #355, #356, #357, #374)
- Bump the `static_info_tables` dependency (#320)
- Require oelib 3.0 (#318)
- Refactor registration form footer creation (#309)
- Allow 9.5-compatible versions of mkforms and rn_base (#275)
- Sort the Composer dependencies (#277)
- Clean up the TypoScript (#266, #267)
- Switch more tests to nimut/testing-framework (#264, #375, #377, #378, #380, #439)
- Update the testing libraries (#251, #252, #254)

### Deprecated
- Using `registration` hook index for registration email hooks in general (#445)
- `modifyThankYouEmail()` registration email hook (#445)
- `RegistrationEmailHookInterface` interface (#445)
- `EventListView` interface and `listView` hook index (#408)
- `EventSingleView` interface and `singleView` hook index (#338)

### Removed
- Stop getting the event dates dynamically from the time slots (#489)
- Stop automatically unsetting invalid prices in the BE (#455)
- Drop the title field for time slots (#451)
- Drop deprecated `Tx_Seminars_Interface_Hook_Registration` hooks (#446)
- Drop deprecated `Tx_Seminars_Service_RegistrationManager::modifyNotificationEmail()` hook (#446)
- Drop the unused `Event::getRelatedMmRecordUids` (#405)
- Drop the creation of model instances from legacy DB result (#388)
- Drop AbstractModel::recordExists (#381)
- Drop the context-sensitive help (#358)
- Drop unneeded Travis CI configuration settings (#258, #259, #260, #261)
- Remove the empty update wizard (#250)
- Drop support for PHP 5 (#249, #299, #300, #301, #303)
- Drop support for TYPO3 7.6 (#248, #272, #280)

### Fixed
- Remove colons from the end of TCEforms labels (#492)
- Fix the rendering markup for locations (#490)
- Avoid double colons after labels in organizer notification emails (#481)
- Add the missing label for the date of birth for emails (#479)
- Fix type error with dates in the old registration model (#477)
- Update the locations of the mkforms JavaScript includes (#467)
- Stop using code that was deprecated in TYPO3 8.7 (#463)
- Fix the event begin/end date calculation by timeslots (#462)
- Fix an error when getting the cities in MySQL strict mode (#461)
- Fix a test case namespace (#452)
- Fix image references in the documentation (#448)
- Fix PHP syntax errors in the documentation (#447)
- Fix failing DefaultController tests in 9.5 (#442)
- Fix the scheduler task flash messages in 9.5 (#440)
- Fix failing EventEditor tests in 9.5 (#438)
- Replace the removed `getTabMenu` (#423)
- Replace deprecated BE route methods (#418, #419, #420, #424, #425, #426)
- Fix failing bag builder tests in 9.5 (#414)
- Fix failing speaker bag tests in 9.5 (#413)
- Fix a failing EventMapper test in 9.5 (#412)
- Stop using the deprecated `NullTimeTracker` (#410)
- Move fragile tests to the new testing framework (#384, #389, #390, #391, #392, #396, #428, #429)
- Internally store boolean properties as integers (#386)
- Convert the old model DB accesses to the ConnectionPool
  (#372, #373, #379, #382, #383, #385, #393, #397, #398, #399, #400, #401, #402, #403, #404, #406, #407)
- Fix TypoScript lint warnings (#371)
- Fix the locallang path in the event publication (#367)
- Fix the numbers in the countdown tests (#365)
- Use the correct prefixes for request parameters (#360)
- Allow access to non-persisted model data (#353)
- Avoid using `eval` in the tests (#335)
- Fix type errors in the tests (#334)
- Streamline `ext_localconf.php` and `ext_tables.php` (#327)
- Fix the path for the plugin icon in the BE (#326)
- Move the plugin registration to `Configuration/TCA/Overrides/` (#325)
- Wrap accesses to global variables (#323)
- Use the new class name for mocks (#321)
- Fix code inspection warnings (#315, #348, #352)
- git-ignore the tests-generated `var/log/` folder (#314)
- Use real records in the FE editor tests (#310)
- Always provide flags to `htmlspecialchars` (#295)
- Fix some strict typing errors (#286, #287, #288, #292, #293, #294, #296, #297, #298, #304, #306, #312)
- Fix bogus sorting value in some test cases (#281, #282)
- Explicitly add transitive dependencies (#273)
- Drop a left-over bogus assigment from the TCA (#271)
- Fix the path to the content element icon (#269)
- Use the correct namespace for test cases (#268)
- Stop using removed oelib functionality (#265)
- Allow longer execution time for Composer scripts (#255)
- Use the new TypoScript file paths in the userfunc tests (#253)

Special thanks go to @mk-mxp for his work on the hooks.

## 2.2.1

### Changed
- Update the oelib dependency (#245)
- Upgrade to PHPUnit 5.7 (#231)

### Removed
- Drop the TYPO3 package repository from composer.json (#232)

### Fixed
- Ignore existing records in the EventEditorTest (#243)
- Create a proper fake frontend for the ViewHelper tests (#242)
- Move `Tests/` to the dev autoload in `ext_emconf.php` (#239)
- Keep development files out of the packages (#237)
- Drop the calls to deprecated config check methods (#235)
- Fix code inspection warnings in the tests (#234)
- Mention in which release deprecated code will be removed (#233)
- Update the mkforms dependency (#230)
- Pin the dev dependency versions (#229)
- Initialize a BE user in the BE-related tests (#227)

## 2.2.0

### Added
- Mention the 9LTS campaign in the BE module (#224)
- Display the speaker image in the event single view (#220)
- Add Speaker.image (#217)

### Changed
- Change from GPL V3 to GPL V2+ (#221)
- Pass user object in modifySalutation hook (#215)
- Rename and namespace the DataHandler hook class (#206)
- Skip the DB cleanup in the new functional tests (#204)
- Require oelib >= 2.3.0 (#203)
- Allow testing the old models with fewer DB accesses (#202)
- Rename SUT from "fixture" to "subject" (#196)
- Convert the first tests to nimut/testing-framework (#194, #195, #201)
- Move to old tests to the "Legacy" namespace (#193)

### Removed
- Remove deprecated "replaces" entry from composer.json (#222)
- Remove unsupported properties from TCA type "select" (#191)

### Fixed
- Fix the homepage URL in the composer.json (#228)
- Fix the CSV export label in the BE module in TYPO3 8LTS (#225)
- Fix the build on Travis CI (#223)
- Explicitly provide the extension name in composer.json (#214)
- Fix the casing of the vfsstream package (#197)
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
