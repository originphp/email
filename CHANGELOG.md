# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.1.0] - 2021-01-04

### Changed

- Adjusted to work with Defer 3.x

## [2.0.0] - 2021-01-04

### Changed

- Changed minimum PHP version to 7.3
- Change minimum PHPUnit to 9.2

## [1.2.4] - 2020-07-23

### Fixed

- Fixed Message-ID now surrounds this in <> as per rfc2822

## [1.2.3] - 2020-07-23

### Fixed

- Fixed issue with setting from and name using array

## [1.2.2] - 2020-05-16

### Changed

- Changed readme for new instructions

## [1.2.1] - 2020-03-08

### Fixed

- Fixed Google console script autoload path when run from vendor dir
- Fixed saving of oauth2 token to file

### Added

- Added constant names to phpunit.xml.dist for oauth tests

## [1.2.0] - 2020-02-22

### Added

- Added Oauth2 authentication support

## [1.1.1] - 2019-10-14

### Fixed

- Fixed composer.json configurable version

## [1.1.0] - 2019-10-13

### Changed

- Constructor now accepts account name (string)

### Added

- Email account method
- Email::config()

## [1.0.0] - 2019-10-12

This component has been decoupled from the [OriginPHP framework](https://www.originphp.com/).
