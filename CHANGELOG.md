# Change log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](https://semver.org/).

## x.y.z

### Added
- Add support for PHP 7.1 and 7.2 (#70)

### Changed
- Skip the tests for the old BE module in TYPO3 >= 8.7 (#75)
- Also allow oelib 3.x (#72)
- Update to PHPUnit 5.3 (#66)

### Deprecated

### Removed
- Drop the class alias map (#73)
- Require TYPO3 7.6 and drop support for TYPO3 6.2 (#67)
- Drop support for PHP 5.5 (#64)

### Fixed
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
- Require oelib >= 2.0.0 (#69)
- Require static_info_tables >= 6.4.0 (#68)
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
