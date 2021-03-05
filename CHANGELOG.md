# Changelog

All notable changes to `laravel-settings` will be documented in this file

## 2.0.1 - 2020-03-05

- add support for lumen

## 2.0.0 - 2020-03-03

- settings classes:
    - properties won't be loaded when constructed but when requested
    - receive a `SettingsMapper` when constructed
    - faking settings will now only request non-given properties from the repository
- rewritten `SettingsMapper` from scratch
- removed `SettingsPropertyData` and `ettingsPropertyDataCollection`
- changed signatures of `SavingSettings` and `LoadingSettings` events
- added support for caching settings
- renamed `cache_path` in settings.php to `discovered_settings_cache_path`

## 1.0.8 - 2020-03-03

- fix for properties without defined type

## 1.0.7 - 2020-02-19

- fix correct 'Event' facade (#30)

## 1.0.6 - 2020-02-05

- add support for restoring settings after a Laravel schema:dump

## 1.0.5 - 2020-01-29

- bump the `doctrine/dbal` dependency

## 1.0.4 - 2020-01-08

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
