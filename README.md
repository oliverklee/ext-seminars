# Seminars TYPO3 extension

[![GitHub CI Status](https://github.com/oliverklee/ext-seminars/workflows/CI/badge.svg?branch=main)](https://github.com/oliverklee/ext-seminars/actions)
[![Latest Stable Version](https://poser.pugx.org/oliverklee/seminars/v/stable.svg)](https://packagist.org/packages/oliverklee/seminars)
[![Total Downloads](https://poser.pugx.org/oliverklee/seminars/downloads.svg)](https://packagist.org/packages/oliverklee/seminars)
[![Latest Unstable Version](https://poser.pugx.org/oliverklee/seminars/v/unstable.svg)](https://packagist.org/packages/oliverklee/seminars)
[![License](https://poser.pugx.org/oliverklee/seminars/license.svg)](https://packagist.org/packages/oliverklee/seminars)

This TYPO3 extension allows you to create and manage a list of seminars,
workshops, lectures, theater performances and other events, allowing front-end
users to sign up. FE users also can create and edit events.

Most of the documentation is in ReST format
[in the Documentation/ folder](Documentation/) and is rendered
[as part of the TYPO3 documentation](https://docs.typo3.org/typo3cms/extensions/seminars/).

## Give it a try!

If you would like to test the extension yourself, there is a
[DDEV-based TYPO3 distribution](https://github.com/oliverklee/TYPO3-testing-distribution)
with this extension installed and some test records ready to go.

## Running the tests locally

You will need to have a Git clone of the extension for this
with the Composer dependencies installed.

### Running the unit tests

#### On the command line

To run all unit tests on the command line:

```bash
composer ci:tests:unit
```

To run all unit tests in a directory or file (using the directory
`Tests/Unit/Model/` as an example):

```bash
.Build/vendor/bin/phpunit -c .Build/vendor/nimut/testing-framework/res/Configuration/UnitTests.xml Tests/Unit/Model/
```

#### In PhpStorm

First, you need to configure the path to PHPUnit in the settings:

Languages & Frameworks > PHP > Test Frameworks

In this section, configure PhpStorm to use the Composer autoload and
the script path `.Build/vendor/autoload.php` within your project.

In the Run/Debug configurations for PHPUnit, use an alternative configuration file:

`.Build/vendor/nimut/testing-framework/res/Configuration/UnitTests.xml`

### Running the functional tests

You will need a local MySQL user that has the permissions to create new
databases.

In the examples, the following credentials are used:
- user name: `typo3`
- password: `typo3pass`
- DB name prefix: `typo3_test` (optional)
- DB host: `localhost` (omitted as this is the default)

You will need to provide those credentials as environment variables when
running the functional tests:
- `typo3DatabaseUsername`
- `typo3DatabasePassword`
- `typo3DatabaseName`

#### On the command line

To run all functional tests on the command line:

```bash
typo3DatabaseUsername=typo3 typo3DatabasePassword=typo3pass typo3DatabaseName=typo3_test composer ci:tests:functional
```

To run all functional tests in a directory or file (using the directory
`Tests/Functional/Authentication/` as an example):

```bash
typo3DatabaseUsername=typo3 typo3DatabasePassword=typo3pass typo3DatabaseName=typo3_test .Build/vendor/bin/phpunit -c .Build/vendor/nimut/testing-framework/res/Configuration/FunctionalTests.xml Tests/Functional/Authentication/
```

#### In PhpStorm

First, you need to configure the path to PHPUnit in the settings:

Languages & Frameworks > PHP > Test Frameworks

In this section, configure PhpStorm to use the Composer autoload and
the script path `.Build/vendor/autoload.php` within your project.

In the Run/Debug configurations for PHPUnit, use an alternative configuration file:

`.Build/vendor/nimut/testing-framework/res/Configuration/FunctionalTests.xml`

Also set the following environment variables in your runner configuration:
- `typo3DatabaseUsername`
- `typo3DatabasePassword`
- `typo3DatabaseName`

### Running the legacy tests

It is only possible to run the legacy tests on the command line, but not
in PhpStorm.

As with the functional tests, you will need a local MySQL user that
has the permissions to create new databases.

First, you will need to create a TYPO3 instance within the extension directory:

```bash
.Build/vendor/bin/typo3cms install:setup
```

You need to create a new database in this step. Using an existing database
from an existing TYPO3 instance with content will lead to all sorts of
problems.

Use `site` as the site setup type as some tests require that a root page
exists. You do not need to create a web server configuration.

The admin user credentials do not matter here. Hence, you can provide any
random credentials (as long as the password is complex enough for the
installer to accept it).

For all tests, you will need to provide the following environment variables:
- `DATABASE_USER`
- `DATABASE_PASSWORD`
- `DATABASE_NAME`

To run all legacy tests, you can use the composer script:

```bash
DATABASE_USER=typo3 DATABASE_PASSWORD=typo3test DATABASE_NAME=typo3_seminars_legacy composer ci:tests:unit-legacy
```

Running all tests from a directory or file looks like this
(using the directory `Tests/LegacyUnit/Email/` as an example):

```bash
DATABASE_USER=typo3 DATABASE_PASSWORD=typo3test DATABASE_NAME=typo3_seminars_legacy .Build/vendor/bin/typo3 phpunit:run Tests/LegacyUnit/Email/
```

For running a single test and for running all tests from a particular group,
you can provide additional options:

```bash
DATABASE_USER=typo3 DATABASE_PASSWORD=typo3test DATABASE_NAME=typo3_seminars_legacy .Build/vendor/bin/typo3 phpunit:run --options="--filter testCreateLogInAndAddFeUserAsOwnerCreatesFeUser" Tests/LegacyUnit/FrontEnd/EventEditorTest.php
DATABASE_USER=typo3 DATABASE_PASSWORD=typo3test DATABASE_NAME=typo3_seminars_legacy .Build/vendor/bin/typo3 phpunit:run --options="--group sendEMailToReviewer" Tests/LegacyUnit/FrontEnd/EventEditorTest.php
```
