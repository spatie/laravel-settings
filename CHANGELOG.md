# Changelog

All notable changes to `laravel-settings` will be documented in this file

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
    - properties won't be loaded when constructed but when requested
    - receive a `SettingsMapper` when constructed
    - faking settings will now only request non-given properties from the repository
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
