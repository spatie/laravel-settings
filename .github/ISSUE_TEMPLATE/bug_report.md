---
name: Bug report
about: Create a report to help us improve laravel-settings
title: ''
labels: ''
assignees: ''
---

**✏️ Describe the bug**
A clear and concise description of what the bug is.

**↪️ To Reproduce**
Provide us a pest test like this one which shows the problem:

```php

it('cannot save settings', function () {
    resolve(SettingsMigrator::class)->inGroup('dummy_simple', function (SettingsBlueprint $blueprint) use ($description, $name): void {
        $blueprint->add('name', $name);
        $blueprint->add('description', $description);
    });
        
    $settings = resolve(DummySimpleSettings::class);
    $settings->name = 'Nina Simone';
    $settings->save();

    // Property is not changed (off course it is but for documentation purposes it is not)
    dd($settings->all());
});
```

Assertions aren't required, a simple dump or dd statement of what's going wrong is good enough 😄

**✅ Expected behavior**
A clear and concise description of what you expected to happen.

**🖥️ Versions**

Laravel:
Laravel settings:
PHP:
