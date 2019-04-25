# CHANGELOG

## 3.0.1 - 2019-04-25

### Added

- Shorten cookie/session names to save space in case user uses cookie session handler

## 3.0.0 - 2019-04-17

### Changed

- Require $username parameter in login() service method.

### Added

- Make $username parameter optional for userId() service method.

## 2.3.0 - 2019-04-17

### Added

- Add userId() service method.

## 2.2.0 - 2019-04-16

### Added

- Add otpExpired() service method.

## 2.1.0 - 2019-04-11

### Added

- Make the Builder class work if a child class is extended from Auth class.

## 2.0.2 - 2019-04-04

### Fixed

- Make Repo::getRememberMeHashAndToken() return null instead of empty array if hash/token is not found.
- Remove OTP cookie upon logout.

## 2.0.1 - 2019-04-04

### Fixed

- Add cookie path config setting.

## 2.0.0 - 2019-04-04

### Changed

- Make the library compatible with `metarush/email-fallback` `v3`.

## 1.0.0 - 2019-03-16

- Release first version.