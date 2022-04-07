# Changelog

All notable changes to `laravel-settings` will be documented in this file

## 2.4.1 - 2022-04-07

- Switch to using scoped instances instead of singletons (#129)

## 2.4.0 - 2022-03-22

## What's Changed

- Add TTL config for settings cache by @AlexVanderbist in https://github.com/spatie/laravel-settings/pull/122

## New Contributors

- @AlexVanderbist made their first contribution in https://github.com/spatie/laravel-settings/pull/122

**Full Changelog**: https://github.com/spatie/laravel-settings/compare/2.3.3...2.4.0

## 2.3.3 - 2022-03-18

- fix debug info method
- convert PHPUnit to Pest (#118)

## 2.3.2 - 2022-02-25

- Allow migrations without a value (#113)

## 2.3.1 - 2022-02-04

- Add support for Laravel 9
- Fix cache implementation with casts
- Remove Psalm
- Add PHPStan

## 2.2.0 - 2021-10-22

- add support for multiple migration paths (#92)

## 2.1.12 - 2021-10-14

- add possibility to check if setting is locked or unlocked (#89)

## 2.1.11 - 2021-08-23

- ignore abstract classes when discovering settings (#84)

## 2.1.10 - 2021-08-17

- add support for `null` in DateTime casts

## 2.1.9 - 2021-07-08

- fix `empty` call not working when properties weren't loaded

## 2.1.8 - 2021-06-21

- fix fake settings not working with `Arrayable`

## 2.1.7 - 2021-06-08

- add support for refreshing settings

## 2.1.6 - 2021-06-03

- add support for defining the database connection table

## 2.1.5 - 2021-05-21

- fix some casting problems
- update php-cs-fixer

## 2.1.4 - 2021-04-28

- added fallback for settings.auto_discover_settings (#63)
- add support for spatie/data-transfer-object v3 (#62)

## 2.1.3 - 2021-04-14

- add support for spatie/temporary-directory v2

## 2.1.2 - 2021-04-08

- skip classes with errors when discovering settings

## 2.1.1 - 2021-04-07

- add better support for nullable types in docblocks

## 2.1.0 - 2021-04-07

- add casts to migrations (#53)
- add original properties to `SavingSettings` event (#57)

## 2.0.1 - 2021-03-05

- add support for lumen

## 2.0.0 - 2021-03-03

- settings classes:
- - properties won't be loaded when constructed but when requested
- 
- 
- 
- 
- - receive a `SettingsMapper` when constructed
- 
- 
- 
- 
- - faking settings will now only request non-given properties from the repository
- 
- 
- 
- 
- 
- rewritten `SettingsMapper` from scratch
- removed `SettingsPropertyData` and `ettingsPropertyDataCollection`
- changed signatures of `SavingSettings` and `LoadingSettings` events
- added support for caching settings
- renamed `cache_path` in settings.php to `discovered_settings_cache_path`

## 1.0.8 - 2021-03-03

- fix for properties without defined type

## 1.0.7 - 2021-02-19

- fix correct 'Event' facade (#30)

## 1.0.6 - 2021-02-05

- add support for restoring settings after a Laravel schema:dump

## 1.0.5 - 2021-01-29

- bump the `doctrine/dbal` dependency

## 1.0.4 - 2021-01-08

- add support for getting the locked settings

## 1.0.3 - 2020-11-26

- add PHP 8 support

## 1.0.2 - 2020-11-26

- fix package namespace within migrations (#9)

## 1.0.1 - 2020-11-18

- fix config file tag (#4)
- fix database migration path exists (#7)

## 1.0.0 - 2020-11-09

- initial release
