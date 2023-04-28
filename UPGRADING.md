# Upgrading

Because there are many breaking changes an upgrade is not that easy. There are many edge cases this guide does not cover. We accept PRs to improve this guide.

## From v2 to v3

This should be a quick update:

- When creating a new project, the default search location for settings classes will be in the `app_path('Settings')` directory. If you want to keep the old location, then you can set the `auto_discover_settings` option to `app_path()`. For applications which already have published their config, nothing changes.
- If you're implementing custom repositories, then update them according to the interface. The method `updatePropertyPayload` is renamed to `updatePropertiesPayload` and should now update multiple properties at once.
