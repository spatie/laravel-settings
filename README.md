# Store strongly typed application settings

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-settings.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-settings)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/spatie/laravel-settings/run-tests?label=tests)](https://github.com/spatie/laravel-settings/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![GitHub Psalm Action Status](https://github.com/spatie/laravel-settings/workflows/Psalm/badge.svg)](https://github.com/spatie/laravel-settings/actions?query=workflow%3APsalm)
[![GitHub Check and fix styling](https://github.com/spatie/laravel-settings/workflows/Check%20&%20fix%20styling/badge.svg)](https://github.com/spatie/laravel-settings/actions?query=workflow%3A%22Check+%26+fix+styling%22)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-settings.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-settings)

This package allows you to store settings in a repository (database, Redis, ...) and use them through an application without hassle. You create a settings class as such:

```php
class GeneralSettings extends Settings
{
    public string $site_name;
    
    public bool $site_active;
    
    public static function group(): string
    {
        return 'general';
    }
}
```

When you want to use these settings somewhere in your application, you can inject them since we register them in the Laravel Container. For example, in a controller:

```php
class GeneralSettingsController
{
    public function show(GeneralSettings $settings){
        return view('settings.show', [
            'site_name' => $settings->site_name,
            'site_active' => $settings->site_active    
        ]);
    }
}
```

Here's how you can update the settings

```php
class GeneralSettingsController
{
    public function update(
        GeneralSettingsRequest $request,
        GeneralSettings $settings
    ){
        $settings->site_name = $request->input('site_name');
        $settings->site_active = $request->input('site_active');
        
        $settings->save();
        
        return redirect()->back();
    }
}
```

## Support us

[![Image](https://github-ads.s3.eu-central-1.amazonaws.com/laravel-settings.jpg)](https://spatie.be/github-ad-click/laravel-settings)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via Composer:

```bash
composer require spatie/laravel-settings
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --provider="Spatie\LaravelSettings\LaravelSettingsServiceProvider" --tag="migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --provider="Spatie\LaravelSettings\LaravelSettingsServiceProvider" --tag="config"
```

This is the contents of the published config file:

```php
return [

    /*
     * You can register all the settings classes here.
     */
    'settings' => [

    ],

    /*
     * When you create a new settings migration via the `make:settings-migration`
     * command the package will store these migrations in this directory.
     */
    'migrations_path' => database_path('settings'),

    /*
     * When no repository was set for a settings class this repository will be
     * used for loading and saving settings.
     */
    'default_repository' => 'database',

    /*
     * Settings will be stored and loaded from these repositories. There are
     * two types of repositories: database and Redis. But its always
     * possible to create your specific types of repositories.
     */
    'repositories' => [
        'database' => [
            'type' => Spatie\LaravelSettings\SettingsRepositories\DatabaseSettingsRepository::class,
            'model' => null,
            'connection' => null,
        ],
        'redis' => [
            'type' => Spatie\LaravelSettings\SettingsRepositories\RedisSettingsRepository::class,
            'connection' => null,
            'prefix' => null,
        ],
    ],

    /*
     * When the package discovers a setting with a type other than the PHP built
     * in types, it should be cast. These casts will automatically cast types
     * when they occur in a settings class.
     */
    'global_casts' => [
        DateTimeInterface::class => Spatie\LaravelSettings\SettingsCasts\DateTimeInterfaceCast::class,
        DateTimeZone::class => Spatie\LaravelSettings\SettingsCasts\DateTimeZoneCast::class,
        Spatie\DataTransferObject\DataTransferObject::class => Spatie\LaravelSettings\SettingsCasts\DtoCast::class,
    ],

    /*
     * The package will look for settings in these paths and automatically
     * register them.
     */
    'auto_discover_settings' => [
        app()->path(),
    ],

    /*
     * When in production, it is advised to cache the automatically discovered
     * and registered setting classes will be cached in this path.
     */
    'cache_path' => storage_path('app/laravel-settings'),
];


```

## Usage

The package is built around settings classes wit public properties that will store the settings. Each settings class extends from `Settings` and also has a static method `group` that should return a string uniquely grouping settings.

You can create multiple groups of settings, each with their own settings class. You could, for example, have `GeneralSettings` with the `general` group and `BlogSettings` with the `blog` group. It's up to you how to structure these groups.

Although it is possible to use the same group for different settings classes we advise you to give each settings class a unique group.


```php
class GeneralSettings extends Settings
{
    public string $site_name;
    
    public bool $site_active;
    
    public static function group(): string
    {
        return 'general';
    }
}
```

Now, you will have to add this settings class to the `settings.php` config file in the `settings` array so it can be loaded by Laravel:

```php
    /*
     * You can register all the settings classes here.
     */
    'settings' => [
        GeneralSettings::class
    ],
```

Each property in a settings class needs a default value that should be set in its migration, you can create a migration as such:

```bash
php artisan make:settings-migration CreateGeneralSettings
```

This will create a new file in `database/settings` where you can add the properties and their default values:

```php
use Spatie\LaravelSettings\SettingsMigration;

class CreateGeneralSettings extends SettingsMigration
{
    public function up(): void
    {
		$this->migrator->add('general.site_name', 'Spatie');
		$this->migrator->add('general.site_active', true);
    }
}
```

We add the properties `site_name` and `site_active` here to the `general` group with values `Spatie` and `true`. We'll cover a lot more on migrations [later](https://github.com/spatie/laravel-settings#creating-settings-migrations).

You should run the migration to add the properties to the database:

```bash
php artisan migrate
```

In the `settings` table of your database these properties are added as such:

| id | group   | name        | payload  | ... |
|----|---------|-------------|----------|-----|
| 1  | general | site_name   | "Spatie" | ... |
| 2  | general | site_active | true     | ... |

Now when you want to use the `site_name` property of the `GeneralSettings` class, you can inject it in your application. For example, in a constructor:

```php
class IndexController
{
    public function __invoke(GeneralSettings $settings){
        return view('index', [
            'site_name' => $settings->site_name,
        ]);
    }
}
```

Or load it somewhere in your application as such:

```php
function getName(): string{
	return app(GeneralSettings::class)->site_name
}
```

Updating settings can be done by changing the public property values of a settings class and calling `save` on it to persist them:

```php
class SettingsController
{
    public function __invoke(GeneralSettings $settings, GeneralSettingsRequest $request){
        $settings->site_name = $request->input('site_name');
        $settings->site_active = $request->boolean('site_active');
        
        $settings->save();
        
        return redirect()->back();
    }
}
```

### Selecting a repository

Settings will be stored and loaded from a repository. There are two types of repositories `database` and `redis`. And it is possible to create multiple repositories for these types. For example, you could have two `database` repositories, one that goes to a `settings` table in your database and another that goes to a `global_settings` table.

You can explicitly set the repository of a settings class by implementing the `repository` method:

```php
class GeneralSettings extends Settings
{
    public string $site_name;
    
    public bool $site_active;
    
    public static function group(): string
    {
        return 'general';
    }
    
    public static function repository(): ?string
    {
        return 'global_settings';
    }
}
```

When a repository is not set for a settings class, the `default_repository` in the `settings.php` config file will be used. More information about the types of repositories and how they can be configured [here](https://github.com/spatie/laravel-settings#repostitories).

### Creating settings migrations

Before you can load/update settings, you will have to migrate them. Though this might sound a bit strange at the beginning, it is quite logical. You want to have some default settings to start with when you're creating a new application. And what would happen if we change a property's name of a settings class? Our code would change, but our data doesn't.

That's why the package requires migrations each time you're changing the properties of your settings class or create a new settings class. These migrations will run next to the regular Laravel database migrations, and we've added some tooling to write them as quickly as possible.

Creating a settings migration works just like you would create a regular database migration. You can run the following command:

```bash
php artisan make:settings-migration CreateGeneralSettings
```

This will add a migration to the `application/database/settings` directory:

```php
use Spatie\LaravelSettings\SettingsMigration;

class CreateGeneralSettings extends SettingsMigration
{
    public function up(): void
    {

    }
}
```

By default there isn't a `down` method in the migration, but this can be added if required. In the `up` method, you can change the settings data in a repository when migrating. There are a few default operations supported:

#### Adding a property

You can add a property to a settings group as such:

```php
public function up(): void
{
    $this->migrator->add('general.site_name', 'Spatie');
}
```

This will add a `site_name` property to the `general` group, which is being used by `GeneralSettings`. You should always give a default value for a newly created setting. In this case, we will call the site name: `Spatie`.

If the property in the settings class is nullable, it's possible to give `null` as a default value. But, do not forget to make the corresponding property in the settings class also nullable.

#### Renaming a property

It is possible to rename a property:

```php
public function up(): void
{
    $this->migrator->rename('general.site_name', 'general.name');
}
```

You can also move a property to another group:

```php
public function up(): void
{
    $this->migrator->rename('general.site_name', 'site.name');
}
```

#### Updating a property

It is possible to update the contents of a property:

```php
public function up(): void
{
    $this->migrator->update(
        'general.site_name', 
        fn(string $site_name) => return strtolower($site_name)
    );
}
```

As you can see, this method takes a closure as an argument, which makes it possible to update a value based upon its old value.

#### Deleting a property

```php
public function up(): void
{
    $this->migrator->delete('general.site_name');
}
```

#### Operations in group

When you're working on a big settings class with a lot of properties, it can be a bit cumbersome always to have to prepend the settings group. That's why you can also perform operations within a settings group:

```php
public function up(): void
{
    $this->settingsMigrator->inGroup('general', function (SettingsBlueprint $blueprint): void {
        $blueprint->add('timzone', 'Europe/Brussels');
        
        $blueprint->rename('timezone', 'local_timezone');
        
        $blueprint->update('timezone', fn(string $timezone) => return 'America/New_York');
        
        $blueprint->delete('timezone');
    });
}
```

#### Migrating multiple repositories

These migrations will run on the `default_repository` in the `settings.php` config file. It is possible to change the repository they run on like this:

```php
public function up(): void
{
    $this->migrator->repository('redis');

    $this->migrator->add('general.site_name', 'Spatie');
    $this->migrator->rename('general.site_name', 'site.name');
    
    // ...
}
```

### Typing properties

It is possible to create a settings class with regular PHP types:


```php
class RegularTypeSettings extends Settings
{
    public string $a_string;
    
    public bool $a_bool;
    
    public int $an_int;
    
    public float $a_float;
    
    public array $an_array;
    
    public static function group(): string
    {
        return 'regular_ype';
    }
}
```

Internally the package will convert the values of these types to JSON and save them as such in a repository. But what about types like `DateTime` and `Carbon` or your own created types? Although some of these these types can be converted to JSON, constructing them again from JSON when they're loaded isn't supported.

That's why you can specify casts within this package. There are two ways to define these casts: locally or globally.

#### Local casts

Local casts work on one specific settings class and should be defined for each property:

```php
class DateSettings extends Settings
{
    public DateTime $birth_date;
    
    public static function group(): string
    {
        return 'date';
    }
    
    public static function casts(): array
    {
        return [
            'bith_date' => DateTimeInterfaceCast::class
        ];
    }
}
```

The `DateTimeInterfaceCast` can be used for properties with types like `DateTime`, `DateTimeImmutable`, `Carbon` and `CarbonImmutable`. You can also use an already constructed cast. It becomes handy when you need to pass some extra arguments to the cast:


```php
class DateSettings extends Settings
{
    public $birth_date;
    
    public static function group(): string
    {
        return 'date';
    }
    
    public static function casts(): array
    {
        return [
            'bith_date' => new DateTimeInterfaceWithTimeZoneCast(DateTimeImmutable::class)
        ];
    }
}
```

As you can see, we provide `DateTimeImmutable::class` to the cast, so it knows what type of `DateTime` it should use because the `birth_date` property was not typed, and the cast couldn't infer the type to use.

You can also provide arguments to a cast without constructing it:

```php
class DateSettings extends Settings
{
    public $birth_date;
    
    public static function group(): string
    {
        return 'date';
    }
    
    public static function casts(): array
    {
        return [
            'bith_date' => DateTimeInterfaceCast::class.':'. DateTimeImmutable::class
        ];
    }
}
```

#### Global casts

Local casts are great for defining types for specific properties of the settings class. But it's a lot of work to define a local cast for each regularly used type like a `DateTime`. Global casts try to simplify this process.

You can define global casts in the `global_casts` array of the package configuration. We've added some default casts to the configuration that can be handy:

```php
'global_casts' => [
    DateTimeInterface::class => Spatie\LaravelSettings\SettingsCasts\DateTimeInterfaceCast::class,
    DateTimeZone::class => Spatie\LaravelSettings\SettingsCasts\DateTimeZoneCast::class,
    Spatie\DataTransferObject\DataTransferObject::class => Spatie\LaravelSettings\SettingsCasts\DtoCast::class,
],
```

 A global cast can work on:
 
 - a specific type (`DateTimeZone::class`)
 - a type that implements an interface (`DateTimeInterface::class`)
 - a type that extends from another class (`DataTransferObject::class`)
 
In your settings class, when you use a `DateTime` property (which implements `DateTimeInterface`), you no longer have to define local casts:

```php
class DateSettings extends Settings
{
    public DateTime $birth_date;
    
    public static function group(): string
    {
        return 'date';
    }
}
```

It is also possible to create your own casters, you can read more about that [here](https://github.com/spatie/laravel-settings#writing-your-own-casters).

#### Using types

There are quite a few options to type properties. You could type them in PHP:

```php
class DateSettings extends Settings
{
    public DateTime $birth_date;
    
    public ?int $a_nullable_int;
    
    public static function group(): string
    {
        return 'date';
    }
}
```

Or you can use docblocks:

```php
class DateSettings extends Settings
{
    /** @var \DateTime  */
    public $birth_date;
    
    /** @var ?int  */
    public $a_nullable_int;
    
    public static function group(): string
    {
        return 'date';
    }
}
```

Docblocks can be very useful to type arrays of objects:

```php
class DateSettings extends Settings
{
    /** @var array<\DateTime>  */
    public array $birth_dates;
    
    // OR

    /** @var \DateTime[]  */
    public array $birth_dates_alternative;

    public static function group(): string
    {
        return 'date';
    }
}
```

### Locking properties

When you want to disable the ability to update the value of a setting, you can add a lock to it:

```php
$dateSettings->lock('birth_date');
```

It is now impossible to update the value of `birth_date`. When trying to overwrite `birth_date` and saving settings, the package will load the old value of `birth_date ` from the repository and it looks like nothing happened.

You can also lock multiple settings at once:

```php
$dateSettings->lock('birth_date', 'name', 'email');
```

Unlocking settings can be done as such:

```php
$dateSettings->unlock('birth_date', 'name', 'email');
```

### Encrypting properties

Some properties in your settings class can be confidential, like API keys, for example. It is possible to encrypt some of your properties, so it won't be possible to read them when your repository data was compromised.

Adding encryption to properties can be done by adding the `encrypted` static method to your settings class and list all the properties that should be encrypted:

```php
class GeneralSettings extends Settings
{
    public string $site_name;
    
    public bool $site_active;
    
    public static function group(): string
    {
        return 'general';
    }
    
    public static function encrypted(): array
    {
        return [
            'site_name'
        ];
    }
}
```

The default values for each settings class you create should now also be encrypted from the beginning. Let's take a look on how to do that.

#### Using encryption in migrations

Creating and updating encrypted properties in migrations work a little bit differently than non-encrypted properties.

Instead of calling the `add` method to create a new property, you should use the `addEncrypted` method:

```php
public function up(): void
{
    $this->migrator->addEncrypted('general.site_name', 'Spatie');
}
```

The same goes for the `update` method, which should be replaced by `updateEncrypted`:

```php
public function up(): void
{
    $this->migrator->updateEncrypted(
        'general.site_name', 
        fn(string $siteName) => return 'Space'
    );
}
```

Whenever you want to change a non-encrypted property to an encrypted property or vice versa you should change the data in a migration. You can make a non-encrypted property encrypted as such:

```php
public function up(): void
{
    $this->migrator->add('general.site_name', 'Spatie');

    $this->migrator->encrypt('general.site_name');
}
```

Or make an encrypted property non-encrypted:

```php
public function up(): void
{
    $this->migrator->addEncrypted('general.site_name', 'Spatie');

    $this->migrator->decrypt('general.site_name');
}
```

Of course, you can use these methods when using `inGroup` migration operations.

### Faking settings classes

In tests, it is sometimes desired that some settings classes can be quickly used with values that are different from default ones you've written in your migrations. That's why you can fake settings. Faked settings classes will be registered in the container. And you can overwrite some or all the properties in the settings class:

```php
DateSettings::fake([
    'birthdate_date' => new DateTime('16-05-1994')
]);
```

Now, when the `DateSettings` settings class is injected somewhere in your application, the `birth_date` property will be `DateTime('16-05-1994')`. Properties not overwritten by the `fake` method will have the default value created in the migrations.

### Auto discovering settings classes

Each settings class you create should be added to the `settings` array within the `settings.php` config file. When you've got a lot of settings this can be quickly forgotten.

That's why it is also possible to auto-discover settings classes. The package will look through your application and tries to discover settings classes. You can specify the paths where will be searched in the config `auto_discover_settings` array. By default, this is the application's app path.

Autodiscovering settings requires some extra miliseconds before your application is booted up. That's why it is possible to cache them using the following command:

```bash
php artisan settings:discover
```

You can clear this cache by running:

```bash
php artisan settings:clear-discovered
```

### Repostitories

There are two types of repositories included in the package, the `redis` and `database` repository. Let's take a closer look at these types.

#### Database repository

The database repository has two optional configuration options:

- `model` the Eloquent model used to load/save properties to the database
- `connection` the connection to use when interacting with the database

It will save each property from a settings class as a different single row with following columns:

- `group`
- `name`
- `locked`: a boolean indicating wether the property is locked or not
- `payload`: a JSON representation of the value

#### Redis repository

The Redis repository also has two optional configuration options:

- `prefix` an optional prefix that will be prepended to the keys
- `connection` the connection to use when interacting with Redis

The Redis repository will store the properties of a settings class as a hash with the key `{{ group }}`. There will also be a set for each group which contains the locked properties with key `locks.{{ group }}`.

For the `general` group these keys would be `general` and `locks.general`. When the prefix is set to, for example, `spatie`. Then the keys would be `spatie.general` and `spatie.locks.general`.

#### Creating your own repository type

It is possible to create your own types of repositories. A repository is a class which implements `SettingsRepository`:

```php
interface SettingsRepository
{
    /**
     * Get all the properties in the repository for a single group
     */
    public function getPropertiesInGroup(string $group): array;

    /**
     * Check if a property exists in a group
     */
    public function checkIfPropertyExists(string $group, string $name): bool;

    /**
     * Get the payload of a property
     */
    public function getPropertyPayload(string $group, string $name);

    /**
     * Create a property within a group with a payload
     */
    public function createProperty(string $group, string $name, $payload): void;

    /**
     * Update the payload of a property within a group
     */
    public function updatePropertyPayload(string $group, string $name, $value): void;

    /**
     * Delete a property from a group
     */
    public function deleteProperty(string $group, string $name): void;

    /**
     * Lock a set of properties for a specific group
     */
    public function lockProperties(string $group, array $properties): void;

    /**
     * Unlock a set of properties for a group
     */
    public function unlockProperties(string $group, array $properties): void;

    /**
     * Get all the locked properties within a group
     */
    public function getLockedProperties(string $group): array;
}
```

All these functions should be implemented to interact with the type of storage you're using. The `payload` parameters are raw values(`int`, `bool`, `float`, `string`, `array`). Within the `database`, and `redis` repository types, These raw values are converted to JSON. But this is not required. 

It is required to return raw values again in the `getPropertiesInGroup` and `getPropertyPayload` methods.

Each repository's constructor will receive a `$config` array that the user-defined for the repository within the application `settings.php` config file. It is possible to add other dependencies to the constructor. They will be injected when the repository is created.

### Writing your own casters

A caster is a class implementing the `SettingsCast` interface:

```php
interface SettingsCast
{
    /**
     * Will be used to when retrieving a value from the repository, and
     * inserting it into the settings class.
     */
    public function get($payload);

    /**
     * Will be used to when retrieving a value from the settings class, and
     * inserting it into the repository.
     */
    public function set($payload);
}
```

A caster can be used for local and global casts, but there are slight differences between them. The package will always try to inject the type of property it is casting. This is a string(the name of the class + namespace) and will be provided as a first argument when constructing the caster. When it cannot deduce the type, `null` will be used as first argument.

An example of such caster with a type injected is a simplified `DtoCast`:

```php
class DtoCast implements SettingsCast
{
    private string $type;

    public function __construct(?string $type)
    {
        $this->type = $type;
    }

    public function get($payload): DataTransferObject
    {
        return new $this->type($payload);
    }

    public function set($payload): array
    {
        return $payload->toArray();
    }
}
```

The above is a caster for the [spatie/data-transfer-object](https://github.com/spatie/data-transfer-object) package, within its constructor, the type will be a specific DTO class, for example, `DateDto::class`. In the `get` method, the caster will construct a `DateDto::class` with the repository properties. The caster receives a `DateDto::class` as payload in the `set` method and convert it to an array for safe storing in the repository.

#### Local casts

When using a local cast, there are a few different possibilities to deduce the type:

By the type of property:

```php
class CastSettings extends Settings 
{
    public DateTime $birth_date;
    
    public static function casts(): array
    {
        return [
            'bith_date' => DateTimeInterfaceCast::class
        ];
    }
    
    ...
}
```

By the docblock of a property:

```php
class CastSettings extends Settings
{
    /** @var \DateTime  */
    public $birth_date;
    
    public static function casts(): array
    {
        return [
            'bith_date' => DateTimeInterfaceCast::class
        ];
    }
    
    ...
}
```

By explicit definition:

```php

class CastSettings extends Settings
{
    public $birth_date;
    
    public static function casts(): array
    {
        return [
            'bith_date' => DateTimeInterfaceCast::class.':'.DateTime::class
        ];
    }
    
    ...
}
```

In that last case: by explicit definition, it is possible to provide extra arguments that will be passed to the constructor:

```php
class CastSettings extends Settings
{
    public $birth_date;
    
    public static function casts(): array
    {
        return [
            'bith_date' => DateTimeWthTimeZoneInterfaceCast::class.':'.DateTime::class.',Europe/Brussels'
        ];
    }
    
    ...
}
```

Although in this case, it might be more readable to construct the caster within the settings class:

```php
class CastSettings extends Settings
{
    public $birth_date;
    
    public static function casts(): array
    {
        return [
            'bith_date' => new DateTimeWthTimeZoneInterfaceCast(DateTime::class, 'Europe/Brussels')
        ];
    }
    
    ...
}
```

#### Global casts

When using global casts, the package will again try to deduce the type of property it's casting. In this case, it can only use the property type or try to infer the type of the property's docblock.

A global cast should be configured in the `settings.php` config file and always has a specific (set) of type(s) it works on. These types can be a particular class, a group of classes implementing an interface, or a group of classes extending from another class.

A good example here is the `DateTimeInterfaceCast` we've added by default in the config. It is defined in the config as such:

```php
    ...

    'global_casts' => [
        DateTimeInterface::class => Spatie\LaravelSettings\SettingsCasts\DateTimeInterfaceCast::class,
    ],
    
    ...
```

Whenever the package detects a `Carbon`, `CarbonImmutable`, `DateTime` or `DateTimeImmutable` type as the type of one of the properties of a settings class. It will use the `DateTimeInterfaceCast` as a caster. This because `Carbon`, `CarbonImmutable`, `DateTime` and `DateTimeImmutable` all implement `DateTimeInterface`. The key that was used in `settings.php` to represent the cast.

The type injected in the caster will be the type of the property. So let's say you have a property with the type `DateTime` within your settings class. When casting this property, the `DateTimeInterfaceCast` will receive `DateTime:class` as a type. 


## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Credits

- [Ruben Van Assche](https://github.com/rubenvanassche)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

