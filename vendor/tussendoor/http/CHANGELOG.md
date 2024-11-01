# Changelog

All notable changes to `Http` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## 0.2.0 - 2019-04-23

### Added
- Custom RequestException class for converting a WP_Error object.
- The exception supports the messages created in WP_Error.

### Fixed
- Fixed a bug where a relative URL would not be properly resolved.

## 0.1.2

### Fixed
- Fixed an issue where where the headers and arguments were not properly separated while sending the request.

## 0.1.1

### Fixed
- Fixed an issue where arguments and headers were not properly separated
- Fixed an issue where a warning was emitted when no headers were set

## 0.1.0 - 2018-03-26

### Added
- Initial release