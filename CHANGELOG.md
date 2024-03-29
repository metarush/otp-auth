# CHANGELOG

## 4.0.3 - 2023-05-25

### Fixed

- Update laminas/diactoros to 2.25 due to security advisory https://github.com/advisories/GHSA-xv3h-4844-9h36

### Fixed

## 4.0.2 - 2022-06-12

- Replace aura/web with laminas/laminas-diactoros due to incompatibility issues with PHP 8

## 4.0.1 - 2021-09-23

### Fixed

- Remove the '+' string prepended in the 3rd parameter of setcookie() function which may cause warnings in PHP 8

## 4.0.0 - 2020-09-26

### Changed

- Upgrade metarush/email-fallback to v4.

### Fixed

- Fix unparenthesized expressions which may cause warnings in PHP 8

## 3.0.1 - 2019-04-25

### Fixed

- Shorten cookie/session names to save space in case user uses cookie session handler.

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