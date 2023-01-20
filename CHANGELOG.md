# Change log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](https://semver.org/).

## x.y.z

### Added
- Add a diverse gender (#2378)

### Changed
- Move more tests to the new testing framework (#2387, #2388)
- Require feuserextrafields >= 5.3.0 (#2358)
- Require oelib >= 5.1.0 (#2353)

### Deprecated

### Removed

### Fixed
- Fix links to pages in RTE texts (#2374)
- Correct use of the configuration `accessToFrontEndRegistrationLists` (#2368)

## 5.1.0

### Added
- Add support for PHP 8.1 and 8.2 (#2333, #2334, #2335)
- Add support for TYPO3 11LTS (#2328, #2329, #2331)

### Deprecated
- Deprecate the `dateFormat` and `timeFormat` settings (#2343)

### Removed
- Drop unused code from the legacy model classes (#2339)

### Fixed
- Avoid array access warnings in `SalutationAwareTranslateViewHelper` (#2349)
- Stop using the deprecated `strftime` (#2340, #2344, #2345, #2346)
- Fix array access warnings in `SelectorWidget` in PHP 8.1 (#2338)

## 5.0.0

### Added
- Add an upgrade wizard for the separate billing address checkbox (#2325)
- Rewrite the unregistration form (#2273, #2277)
- Use SimpleCSS for the email CSS (#2261, #2262)
- Add dedicated tests for `GeneralEventMailForm::sendEmailToAttendees` (#2249)

### Changed
- Always send HTML and plain text emails (#2296)
- Rename `LegacyRegistrationConfiguration` to `LegacyConfiguration` (#2276)
- Rename `denyRegistrationAction` to `denyAction` (#2270)
- Always show the time of deadlines and of the registration start (#2265)
- !!! Move the localized label
  `plugin.eventRegistration.heading.eventTitleAndDateAndUid`
  to a separate namespace (#2266)
- Switch the FAL-related fields in the DB to int (#2187)
- Shrink some DB fields to save some space (#2187)
- !!! Switch the CSV export to always use UTF-8 (#2181)
- !!! Stop allowing single events as topics for date records (#2180)
- Require oelib >= 5.0.2 (#2178, #2322)
- Require feuserextrafields >= 5.2.2 (#2178, #2322)
- Raise PHPStan to level 9 (#2160)

### Removed
- Do not show the registrations in the event TCEforms anymore (#2321)
- Drop obsolete TypoScript related to `sb_accessiblecontent` (#2318)
- Drop the custom FE user group model (#2318)
- Drop the `Event::STATUS_*` constants (#2316)
- Drop organizer-specific registration storage folders (#2311)
- Remove the `showToBeAnnouncedForEmptyPrice` setting (#2312)
- Drop the `externalLinkTarget` setting (#2310)
- Drop the collision check (#2308)
- Drop currency and tax information from registrations (#2305)
- Drop the requirements check (#2299)
- Remove the `Titled` interface (#2298)
- Drop the gender field for speakers (#2289)
- Drop the automatic gender-specific salutations (#2288)
- Drop the changelog archive (#2284)
- Drop the legacy unregistration form (#2278)
- Drop the `showOwnerDataInSingleView` setting (#2267)
- Drop the `showTimeOf*` settings (#2265)
- Drop the event headline plugin (#2242)
- Drop the event countdown (#2237)
- Drop the CSV export of registrations in the FE (#2222, #2307)
- Drop the custom BE user and BE user group models (#2221)
- Drop the approval workflow for the FE editor (#2214, #2220)
- Drop the hide/unhide FE editor functionality (#2206)
- Drop the legacy BE module
  (#2205, #2207, #2208, #2215, #2219, #2232, #2235, #2236, #2238, #2243, #2245,
  #2247, #2251, #2253)
- Remove the "duplicate event" functionality from the FE editor (#2204)
- Remove the `logOutOneTimeAccountsAfterRegistration` setting (#2199)
- Drop the legacy registration form
  (#2196, #2198, #2199, #2225, #2226, #2244, #2246, #2248, #2250, #2252, #2254,
  #2260, #2263, #2281)
- Drop anything bank-data related (#2192)
- Remove the board-related prices (#2190)
- Drop the upgrade wizards (#2187)
- Drop unused model methods and mapper relations (#2185)
- Drop the legacy FE editor
  (#2184, #2194, #2202, #2203, #2224, #2227, #2231, #2234)
- Drop the unused `CommaSeparatedTitlesViewHelper` (#2182)
- Drop the the `charsetForCsv` setting (#2181)
- Drop obsolete package conflicts (#2171)
- Drop `.htaccess` files (#2164)
- Drop support for Emogrifier 4 and 5 (#2163)
- Drop the SwiftMailer dependency (#2158)
- Drop support for TYPO3 9LTS (#2156, #2165, #2170, #2179)
- Drop the German and Dutch manuals (#2176, #2285)

### Fixed
- Fix crash saving an event in the FE editor (#2257)
- Drop usages of deprecated code (#2160, #2166, #2178, #2183, #2186, #2197)

## 4.4.0

### Added
- Add `EventStatisticsCalculator` and `Event/EventStatistics` (#2101, #2102)
- Add more `Event` properties and repository methods (#2089, #2124)
- Add a rewritten BE module for TYPO3 >= 10LTS
  (#2074, #2075, #2076, #2077, #2078, #2084, #2085, #2088, #2094, #2095, #2097
  #2100, #2105, #2109, #2113, #2114, #2116, #2120, #2122, #2123, #2125, #2126,
  #2129, #2137, #2139, #2145, #2146, #2148, #2151)
- Add an SVG icon for the BE module (#2071)

### Changed
- !!! Move the `EventTitleAndDateAndUid` partial around (#2119)
- Clean up the Fluid templates (#2092)
- Use a different hue of orange in the extension icon (#2070, #2071)

### Deprecated
- Deprecate the `charsetForCsv` setting (#2108)

### Fixed
- Fix the camelCase of a Fluid variable in the FE editor templates (#2091)

## 4.3.0

### Added
- Add TypoScript settings for the Fluid templates (#1927)
- Add a `OneTimeAccountConnector` (#1865, #1946)
- Add a `RegistrationGuard` class (#1838, #1846, #1855, #1901, #1937, #2046)
- Add a rewritten registration form for TYPO3 >= 10LTS
  (#1825, #1830, #1848, #1855, #1861, #1871, #1873, #1886, #1889, #1890, #1892,
  #1893, #1896, #1898, #1899, #1902, #1906, #1914, #1920, #1932, #1942, #1944,
  #1952, #1953, #1957, #1962, #1966, #1967, #1968, #1974, #1977, #1979, #1982,
  #1988, #2001, #2005, #2006, #2008, #2009, #2011, #2017, #2018, #2019, #2030,
  #2031, #2036, #2040, #2048, #2058, #2060, #2062)
- Add `Registration.hasSeparateBillingAddress` (#1821)
- Add salutation-aware localization functionality (#1813, #1818, #1822)
- Add a `PriceFinder` class (#1799)
- Add `Event.isFreeOfCharge()` (#1791)
- Add a (non-persisted) `Price` model (#1771, #1793, #2026, #2057)
- Add registration-specific fields to the `Event` model
  (#1764, #1767, #1829, #1835, #2033)
- Add a `Registration` model and repository
  (#1750, #1752, #1755, #1756, #1757, #1758, #1834, #1840, #1853, #1883, #1950
  #1996, #1997, #2002, #2045)
- Add a `RegistrationCheckbox` model (#1742)
- Add a `PaymentMethod` model (#1740)
- Add a `FoodOption` model (#1738)
- Add an `AccommodationOption` model (#1731, #1736)

### Changed
- Modernize the JavaScript and use less jQuery (#1965, #2049, #2055, #2056)
- Require feuserextrafields >= 3.2.1 (#1902)
- Allow more versions of mkforms (#1651)
- Switch the new models from `DateTime` to `DateTimeImmutable` (#1801)
- Also allow installations with Emogrifier 7 (#1748)

### Deprecated
- Deprecate `Registration.currency` and `Registration.includingTax` (#2032)
- Deprecate the `logOutOneTimeAccountsAfterRegistration` setting (#1983)
- Deprecate the event headline view (#1951)
- Deprecate organizer-specific registration storage folders (#1949)
- Deprecate the `Titled` interface (#1945)
- Deprecate the "duplicate event" functionality in the FE editor (#1940)
- Deprecate the requirements check (#1938)
- Deprecate the event countdown (#1933)
- Deprecate the `CommaSeparatedTitlesViewHelper` (#1928)
- Deprecate the `Event::STATUS_*` constants (#1916)
- Deprecate using single events as topics for event dates (#1913)
- Deprecate the `omitDateIfSameAsPrevious` setting (#1906)
- Deprecate the `showToBeAnnouncedForEmptyPrice` setting (#1897)
- Deprecate seeing the registrations within an event in the TCEforms (#1891)
- Deprecate the FE user email format settings (#1885)
- Deprecate FE editing for managers (#1866)
- Deprecate hiding events in the FE editor (#1864)
- Deprecate the FE editor approval workflow (#1849, #1934)
- Deprecate the legacy registration form (#1844)
- Deprecate the automatic gender-specific salutations (#1839)
- Deprecate the board-related prices (#1831)
- Deprecate all bank-data-related fields (#1828)
- Deprecate the collision check for registrations (#1820)
- Deprecate showing the owner data in the single view (#1811)

### Removed
- Stop using Prophecy (#1734, #1737, #1739, #1741, #1746, #1747)
- Drop the `Event.getOrganizer()` alias method (#1727)

### Fixed
- Add `maxlength` to the `textareas` in the FE forms (#2007)
- Use `DateTime` instead of `DateTimeImmutable` in the models (#1961)
- Streamline the HTML and CSS for the FE editor (#1959)
- Make the `RegistrationManager` injectable (#1915)
- Update the `.editorconfig` and TypoScript lint settings (1875)
- Fix renderings warnings in the documentation (#1826)
- Stop using deprecated oelib functionality (#1819)
- Fix a typo in a `Registration` model setter (#1754)
- Fix `Event.getFirstOrganizer()` for not-rewound storages (#1729)

## 4.2.1

### Added
- Add links to the feature survey in a few places (#1720)

### Changed
- Improve the documentation for the FE editor (#1713)

### Fixed
- Allow saving events without event type in the FE editor (#1712, #1714, #1715)

## 4.2.0

### Added
- Rewrite the FE editor for TYPO3 >= 10LTS
  (#1656, #1657, #1658, #1663, #1666, #1669, #1675, #1680, #1683, #1688, #1695)
- Add an `Event` model and repository
  (#1599, #1600, #1602, #1603, #1604, #1616, #1617, #1618, #1622, #1625, #1626, #1627, #1630, #1631, #1639, #1643)
- Add an `Organizer` model and repository (#1590, #1667)
- Add a `Venue` model and repository (#1586, #1667)
- Add an `EventType` model and repository (#1575, #1667)
- Add a `Speaker` model and repository (#1564, #1667)
- Add `composer normalize` to the CI toolchain (#1559)
- Add a services configuration file (#1548)

### Changed
- Switch the coverage on CI from Xdebug to PCOV (#1614)
- Add `feuserextrafields` as a dependency (#1565)
- Switch to the TYPO3 coding standards package (#1553)
- Rename some Composer scripts (#1552)
- Require oelib >= 4.3.1 (#1525, #1650)
- Disable the legacy BE module in TYPO3 11LTS (#1521)

### Deprecated
- Deprecate the legacy front-end editor (#1699)

### Removed
- Drop the BE time-slot wizard (#1598)

### Fixed
- Do not package the docker-compose configuration file (#1559)
- Get rid of unnecessary properties in the BE modules (#1554)
- 11LTS compatibility fixes (#1526, #1527, #1528)
- Fix type warnings for `str_replace` in the `MailNotifier` (#1524)

## 4.1.6

### Changed
- Allow installations with oelib 5 (#1509)
- Allow a broader version range for dependencies (#1494)
- Run the tests with all warnings enabled (#1485, #1501)
- Rename the `TSConfig` folder to `TsConfig` (#1473)
- Loosen the mkforms/rn_base version requirements (#1469)
- Require oelib >= 4.1.8 (#1409, #1415)

### Fixed
- Stop using removed oelib functionality (#1493, #1502, #1503, #1507)
- Bump the minimal 10.4 Extbase requirement (#1445)
- Only show the configuration check with a logged-in BE admin (#1427)
- Do not rely on transitive Composer dependencies (#1426)
- Fix a flaky test (#1403, #1408)
- Improve the type annotations (#1393, #1401, #1417, #1424, #1429, #1432)

## 4.1.5

### Added
- Advertise the 11LTS crowdfunding campaign (#1338)

### Changed
- Bump the mkforms and rn_base dependencies (#1329)
- Remove the version constraints from the extension suggestions (#1337)
- Require oelib >= 4.1.6 (#1325)

### Fixed
- Avoid crash with empty file titles (#1342)

## 4.1.4

### Fixed
- Fix SQL injection in `EventBagBuilder::limitToOrganizers` (#1322)
- Fix SQL injection in `EventBagBuilder::limitToCategories` (#1321)

## 4.1.3

### Added
- Add more tests for `NullRenderingContext` (#1316)
- Add a code coverage badge (#1313)
- Add a convenience function for localized labels in tests (#1268)

### Changed
- Switch to the TYPO3 Code of Conduct (#1311)
- Require oelib >= 4.1.5 (#1281, #1314)
- Clean up the test (#1267)
- Move more legacy tests to the new testing framework (#1267, #1282, #1290, #1308)
- Use the new configuration classes for the single view link builder (#1265, #1266)
- Upgrade to PHPUnit 8 (#1223)
- Stop using `getAccessibleMock` (#1259)

### Fixed
- Improve the fake frontend in the tests (#1299, #1300, #1301, #1302, #1303, #1304, #1305, #1307, #1309)
- Harden some queries (#1297)
- Use `intExplode` where applicable (#1296)
- Make the Composer dependencies explicit (#1283)
- Avoid spilling over the request in the legacy tests (#1278, #1279)
- Improve the type annotations (#1277, #1280, #1294, #1298, #1315)
- Remove a stray `backupGlobals` from a legacy test (#1274)
- Allow Composer plugins from `helhum/typo3-console-plugin` (#1264)
- Fix PHPUnit warnings (#1262)
- Dev-require and suggest the install tool (#1261)

## 4.1.2

### Added
- Also test with the highest and lowest dependencies in CI (#1237)
- Add a script for creating an installation for legacy tests (#1243)

### Changed
- Move more legacy tests to the new testing framework (#1247, #1249, #1251, #1252, #1254)
- Require oelib >= 4.1.3 (#1242)

### Fixed
- Fix crash with RTE rendering with `typo3fluid/fluid:2.6` (#1253)
- Require `typo3/class-alias-loader` >= 1.1.0 for development (#1245)
- Keep development-only files out of the TER releases (#1241)

## 4.1.1

### Changed
- Switch the TER release to Tailor (#1222)

### Fixed
- Remove the HTML template file settings from the Flexforms (#1221)
- Fix the link generation in the tests in TYPO3 V10 (#1217)

## 4.1.0

### Added
- Add support for TYPO3 10LTS (#1136)

### Changed
- Disable the time slot wizard in V10 (#1200)
- Do not advertise the crowdfunding campaign (#1150)
- Allow installations with Emogrifier 5.x and 6.x (#1147)
- Require oelib >= 4.1.2 (#1143, #1146, #1154, #1183)

### Removed
- Drop the RealURL auto-configuration (#1189)

### Fixed
- Create mkforms-related directories on the fly (#1211)
- Get the BE CSV export to work in TYPO3 V10 (#1198, #1206, #1207)
- Avoid calls to the removed `buildUriFromModule` (#1204)
- Fix crash in the BE module in V10 (#1199)
- Change the icon tests to recognize icon sprites (#1172)
- Improve the type annotations and fix PHPStan warnings (#1165)
- Use the new mailer in TYPO3 10LTS
  (#1153, #1157, #1158, #1161, #1163, #1169, #1170, #1208)
- Properly disable the Core cache in V10 in the tests (#1148)
- Provide the `ContentObjectRenderer` with a logger in the tests (#1145)
- Fix the test setup (#1139, #1162, #1164, #1168)

## 4.0.3

### Added
- Add Rector to the toolchain (#1094)

### Changed
- Use the HTML view helper for rendering RTE (#1131)
- Drop the `TemplateHelper` dependency from `RegistationManager` (#1129)
- Use HTTPS for external links by default (#1127)
- Stop using typolink for external links (#1123)
- Drop the `TemplateHelper` dependency from the legacy models (#1117)
- Use the Extbase localization features in more places (#1116, #1128)
- Switch to the new configuration classes in more places (#1112, #1114, #1126)
- Stop calling `initTemplate()` in the link builder (#1098)
- Use PHP 7.2 features (#1095)
- Raise PHPStan to level 6 (#1093)
- Require mkforms 10 and allow rn_base 1.14 (#1088)

### Removed
- Remove the favorites list dummy placeholder (#1100)

### Fixed
- Construct the FE controller in differently in V10 (#1125)
- Add localized label for event description in the CSV export (#1115)
- Stop using the deprecated `BaseScriptClass` (#1111)
- Stop using the language labels of the lang extension (#1097)
- Improve the type annotations (#1096)
- Use the `LanguageService` factory methods in TYPO3 10LTS (#1091)

## 4.0.2

### Changed
- Allow installations with mkforms 10 (#1086)

## 4.0.1

### Changed
- Allow installations with oelib 4 again (#1080)

### Fixed
- Properly initialize the languages in all views (#1083, #1084)

## 4.0.0

### Changed
- Migrate the seminar attachments to FAL (#1067)
- Migrate the seminar images to FAL (#1065)
- Migrate the category icons to FAL (#1063)
- Switch the autoloading to PSR-4 (#1052)
- Enable the sniff for namespaced classes (#1044)
- !!! Namespace all classes
  (#1004, #1005, #1007, #1010, #1011, #1012, #1014, #1016, #1017, #1019, #1020, #1021, #1025, #1026, #1028, #1029, #1031, #1032, #1033, #1034, #1035, #1036, #1038, #1039, #1040, #1042, #1043)
- Migrate to the new configuration check
  (#935, #937, #939, #940, #943, #944, #946, #948, #949, #950, #951, #953, #954, #955, #956, #957, #958, #959, #962)
- Add sr_feuser_register as a dev dependency (#926)
- Require static_info_tables >= 6.9.5 (#925)
- Add more type declarations (#887)
- Use the new configuration classes
  (#725, #910, #913, #914, #915, #916, #917, #919, #922, #927, #928, #930, #966)
- Require oelib >= 3.6.3 (#877, #1047)
- Use the Core email functions (#674, #874)
- Package and use Emogrifier for inlining CSS into HTML emails (#863)
- Update `simshaun/recurr` to version 5.0.0 (#862)
- !!! Rename the TypoScript files to `*.typoscript` (#861)
- Allow mkforms 10.x (#860)
- Upgrade to PHP-CS-Fixer V3 (#854)
- Upgrade to PHPUnit 7 (#853)
- Require rn_base >= 1.13.13 (#774, #911)
- Move PHPStan from PHIVE to Composer (#849)
- Rename sub-namespace `Interface` to `Interfaces` (#623, #852)

### Removed
- Drop the feature for abbreviated date ranges (#1057)
- Drop time zones and use floating dates for iCal (#1054)
- Remove the file upload from the FE editor (#1053, #1058)
- Drop the unused `TimeRangeViewHelper` (#1006)
- Drop the fax number field from the speakers (#999)
- Drop autogeneratable fields from the SQL file (#905)
- Make the time slot wizard and CSS in emails Composer-only (#904)
- Remove the deprecated hooks (#867)
- Drop support for TYPO3 8LTS (#848, #859)
- Drop support for PHP 7.0 and 7.1 (#847)

### Fixed
- Fix crash in the FE editor (#1076)
- Block mkforms 10 to avoid a crash (#1080)
- Copy `FrontEndUserMapper::findByUserName` from oelib (#1001, #1002)
- Drop the typo3/cms-lang dependency (#980)
- Provide a page to the fake frontend where needed (#969)
- Add more type declarations
  (#967, #968, #970, #971, #972, #973, #977, #983, #984, #985, #988, #989, #991, #993, #994, #998, #1037, #1046, #1050, #1051)
- Stop treating a model PID as current BE page ID (#963)
- Complete `extras/typo3-cms` in the `composer.json` (#918)
- Stop using a deprecated testing framework parameter (#880, #881)
- Stop using patches for dependencies (#875)

## 3.4.0

### Added
- Document the upgrade path (#844)
- Add a BE forms wizard for creating a series of time slots (#820, #832)
- Add PHPStan to the CI builds (#763)
- Allow installations up to PHP 8.0 (#758)

### Changed
- Document the required MySQL/MariaDB settings for the FE editor (#843)
- Simplify the user and group mapper inheritance chains (#806)
- Move more tests to the nimut testing framework (#804)
- Truncate changed tables only for functional tests (#786)
- Raise PHPStan to levels 1, 2, 3, 4 and 5 (#776, #782, #789, #808, #810)
- Update the php-cs-fixer configuration (#773)
- Update the `.editorconfig` to better match the Core (#739)
- Require oelib >= 3.6.1 (#737, #738, #777, #787, #795, #802, #841)

### Removed
- Drop the `approved` flag from locallang labels (#745)

### Fixed
- Use the correct GET parameter for the UID in the event headline (#838)
- Do not duplicate places when copying them from time slots (#833)
- Require `ext-pdo` in the `composer.json` (#837)
- Fix the parameter type in the typolink creation calls (#811)
- Improve the type annotations
  (#790, #791, #792, #793, #796, #797, #798, #799, #800, #801, #805, #807, #809, #812, #813, #814)
- Stop using `PATH_site` in TYPO3 9LTS (#780)
- Stop using the Core-provided whitespace constants (#778)
- Fix PHPStan level warnings (#775, #788)
- Add a missing comma in a language label (#771)
- Drop reference to a removed feature from the manual (#755)
- Fix ReST rendering warnings in the documentation (#750)
- Fix saving of new speakers with MySQL in strict mode (#749)

## 3.3.3

### Fixed
- Relax the dependencies to allow non-Composer installations again (#733)

## 3.3.2

### Added
- Document how to run the tests (#685)
- Add traits for testing email and `makeInstance` instances (#683)

### Changed
- Require oelib >= 3.2.0 (#657)
- Move more tests to the new testing framework (#653, #655)
- Namespace some classes and tests (#599, #624, #627, #626, #625, #628)

### Fixed
- Stop using Core functionality deprecated in 9LTS (#602, #603, #707, #708, #712, #716, #717, #723, #714, #730)
- Stop using deprecated oelib functionality (#630, #631, #632, #636, #633, #638, #640, #641, #639, #642, #643, #644, #675, #676, #660, #715, #719, #724, #714)
- Replace the usage of a deprecated rn_base class (#709)
- Drop the deprecated `cellspacing` HTML attribute (#710)
- Drop the obsolete `dividers2tabs` option from TCA (#722)
- Raise limit of field endtime to 2038-1-1 (#678)
- Use the namespaced oelib classes (#658, #569, #661, #662, #663, #664, #665, #666, #667, #668, #669, #670, #671)
- Add more type declarations (#601, #596)

## 3.3.1

### Fixed
- Add a missing return type declaration (#680)
- Drop some outdated tests (#681)
- Raise limit of field endtime to 2038-1-1 (#678)
- Stop using event model prophecies in the scheduler task tests (#651)
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

The change log up to version 1.3.0
[has been archived](https://github.com/oliverklee/ext-seminars/blob/v4.4.0/Documentation/changelog-archive.txt).
