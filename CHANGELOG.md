# Changelog

All notable changes to `laravel-settings` will be documented in this file

# Unreleased

- Make `spatie/data-transfer-object` dependency optional. (#160)

## 3.1.0 - 2023-05-11

- Add support for nullable enum properties
- Updates to the upgrade guide

## 3.0.0 - 2023-04-28

- Allow repositories to update multiple settings at once (#213 )
- The default location where searching for settings happens is now `app_path('Settings')` instead of `app_path()`
- The default `discovered_settings_cache_path` is changed

## 2.8.3 - 2023-03-30

- Remove doctrine as a dependency

## 2.8.2 - 2023-03-10

- Fix remigration problems with anonymous settings migrations

## 2.8.1 - 2023-03-02

- Show message and target path after setting migration created (#203)
- Follow Laravel's namespace convention in MakeSettingCommand (#200)
- Update MakeSettingsMigrationCommand.php (#205)
- Revert "Add support for structure discoverer"( #207)

## 2.8.0 - 2023-02-10

- Drop Laravel 8 support
- Drop PHP 8.0 support
- Use spatie/structures-discoverer for finding settings

## 2.7.0 - 2023-02-01

- Add Laravel 10 Support (#192)
- Update make:settings migration class as anonymous class (#189)
- Use correct namespace in make:settings command (#190)

## 2.6.1 - 2023-01-06

- Add current date to the settings migration file (#178)
- Add command to make new settings (#181)

## 1.6.1 - 2022-12-21

- create settings migration with current date (#179)

## 2.6.0 - 2022-11-24

- Add support for caching on repository level

## 2.5.0 - 2022-11-10

- Remove deprecated package
- Add laravel data cast
- Add support for PHP 8.2
- Remove PHP 7.4 support
- Remove dto cast from default config

## 2.4.5 - 2022-09-28

- Add deleteIfExists() method to migrator (#154)

## 2.4.4 - 2022-09-07

- cache encrypted settings

Please, be sure to clear your cache since settings classes with encrypted properties will crash due to the cached versions missing a proper encrypted version of the property. Clearing and caching again after installing this version resolves this problem and is something you probably should always do when deploying to production!

## 2.4.3 - 2022-08-10

- add rollback to migration

## 2.4.2 - 2022-06-17

- use Facade imports instead of aliases (#132)

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
