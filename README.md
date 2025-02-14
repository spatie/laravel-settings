# Store strongly typed application settings

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-settings.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-settings)
[![Tests](https://github.com/spatie/laravel-settings/actions/workflows/run-tests.yml/badge.svg)](https://github.com/spatie/laravel-settings/actions/workflows/run-tests.yml)
[![PHPStan](https://github.com/spatie/laravel-settings/actions/workflows/phpstan.yml/badge.svg)](https://github.com/spatie/laravel-settings/actions/workflows/phpstan.yml)
[![Style](https://github.com/spatie/laravel-settings/workflows/Check%20&%20fix%20styling/badge.svg)](https://github.com/spatie/laravel-settings/actions?query=workflow%3A%22Check+%26+fix+styling%22)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-settings.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-settings)

This package allows you to store settings in a repository (database, Redis, ...) and use them through an application without hassle. You can create a settings class as such:

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

If you want to use these settings somewhere in your application, you can inject them, since we register them in the Laravel Container. For example, in a controller:

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

You can update the settings as such:

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

Let's take a look at how to create your own settings classes.

## Support us

[![Image](https://github-ads.s3.eu-central-1.amazonaws.com/laravel-settings.jpg)](https://spatie.be/github-ad-click/laravel-settings)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

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
     * Each settings class used in your application must be registered, you can
     * put them (manually) here.
     */
    'settings' => [

    ],

    /*
     * The path where the settings classes will be created.
     */
    'setting_class_path' => app_path('Settings'),

    /*
     * In these directories settings migrations will be stored and ran when migrating. A settings
     * migration created via the make:settings-migration command will be stored in the first path or
     * a custom defined path when running the command.
     */
    'migrations_paths' => [
        database_path('settings'),
    ],

    /*
     * When no repository was set for a settings class the following repository
     * will be used for loading and saving settings.
     */
    'default_repository' => 'database',

    /*
     * Settings will be stored and loaded from these repositories.
     */
    'repositories' => [
        'database' => [
            'type' => Spatie\LaravelSettings\SettingsRepositories\DatabaseSettingsRepository::class,
            'model' => null,
            'table' => null,
            'connection' => null,
        ],
        'redis' => [
            'type' => Spatie\LaravelSettings\SettingsRepositories\RedisSettingsRepository::class,
            'connection' => null,
            'prefix' => null,
        ],
    ],

    /*
     * The encoder and decoder will determine how settings are stored and
     * retrieved in the database. By default, `json_encode` and `json_decode`
     * are used.
     */
    'encoder' => null,
    'decoder' => null,

    /*
     * The contents of settings classes can be cached through your application,
     * settings will be stored within a provided Laravel store and can have an
     * additional prefix.
     */
    'cache' => [
        'enabled' => env('SETTINGS_CACHE_ENABLED', false),
        'store' => null,
        'prefix' => null,
        'ttl' => null,
    ],

    /*
     * These global casts will be automatically used whenever a property within
     * your settings class isn't a default PHP type.
     */
    'global_casts' => [
        DateTimeInterface::class => Spatie\LaravelSettings\SettingsCasts\DateTimeInterfaceCast::class,
        DateTimeZone::class => Spatie\LaravelSettings\SettingsCasts\DateTimeZoneCast::class,
     // Spatie\DataTransferObject\DataTransferObject::class => Spatie\LaravelSettings\SettingsCasts\DtoCast::class,
        Spatie\LaravelData\Data::class => Spatie\LaravelSettings\SettingsCasts\DataCast::class,
    ],

    /*
     * The package will look for settings in these paths and automatically
     * register them.
     */
    'auto_discover_settings' => [
        app_path('Settings'),
    ],

    /*
     * Automatically discovered settings classes can be cached, so they don't
     * need to be searched each time the application boots up.
     */
    'discovered_settings_cache_path' => base_path('bootstrap/cache'),
];
```

## Usage

The package is built around settings classes, which are classes with public properties that extend from `Settings`. They also have a static method `group` that should return a string.

You can create multiple groups of settings, each with their settings class. You could, for example, have `GeneralSettings` with the `general` group and `BlogSettings` with the `blog` group. It's up to you how to structure these groups.

Although it is possible to use the same group for different settings classes, we advise you not to use the same group for multiple settings classes.


```php
use Spatie\LaravelSettings\Settings;

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

You can generate a new settings class using this artisan command. Before you do, please check if the `setting_class_path` is correctly set. You can also specify a `path` option, which is optional.

```bash
    php artisan make:setting SettingName --group=groupName 
```

Now, you will have to add this settings class to the `settings.php` config file in the `settings` array, so it can be loaded by Laravel:

```php
    /*
     * Each settings class used in your application must be registered, you can
     * add them (manually) here.
     */
    'settings' => [
        GeneralSettings::class
    ],
```

Each property in a settings class needs a default value that should be set in its migration. You can create a migration as such:

```bash
php artisan make:settings-migration CreateGeneralSettings
```

This command will create a new file in `database/settings` where you can add the properties and their default values:

```php
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.site_name', 'Spatie');
        $this->migrator->add('general.site_active', true);
    }
}
```

We add the properties `site_name` and `site_active` here to the `general` group with values `Spatie` and `true`. More on migrations [later](https://github.com/spatie/laravel-settings#creating-settings-migrations).

You should migrate your database to add the properties:

```bash
php artisan migrate
```

Without the migration, if you try to load the `GeneralSettings` settings class, it will throw `MissingSettings` exception. To avoid this, you can define default values for each attribute. This can be useful if you have long-running migrations.

```php
    // Will throw an error
    public ?string $site_name;
    // Will return `null`
    public ?string $site_description = null;
    // Will return `false`
    public bool $site_active = false;
```

Now, when you want to use the `site_name` property of the `GeneralSettings` settings class, you can inject it into your application:

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

Or use it to load it somewhere in your application as such:

```php
function getName(): string{
    return app(GeneralSettings::class)->site_name;
}
```

Updating the settings can be done as such:

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

Settings will be stored and loaded from the repository. There are two types of repositories `database` and `redis`. And it is possible to create multiple repositories for these types. For example, you could have two `database` repositories, one that goes to a `settings` table in your database and another that goes to a `global_settings` table.

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

When a repository is not set for a settings class, the `default_repository` in the `settings.php` config file will be used.

### Creating settings migrations

Before you can load/update settings, you will have to migrate them. Though this might sound a bit strange at the beginning, it is quite logical. You want to have some default settings to start with when you're creating a new application. And what would happen if we change a property of a settings class? Our code would change, but our data doesn't.

That's why the package requires migrations each time you're changing/creating your settings classes' structure. These migrations will run next to the regular Laravel database migrations, and we've added some tooling to write them as quickly as possible.

Creating a settings migration works just like you would create a regular database migration. You can run the following command:

```bash
php artisan make:settings-migration CreateGeneralSettings
```

This will add a migration to the `application/database/settings` directory:

```php
use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateGeneralSettings extends SettingsMigration
{
    public function up(): void
    {

    }
}
```

We haven't added a `down` method, but this can be added if desired. In the `up` method, you can change the settings data in the repository when migrating. There are a few default operations supported:

#### Adding a property

You can add a property to a settings group as such:

```php
public function up(): void
{
    $this->migrator->add('general.timezone', 'Europe/Brussels');
}
```

We've added a `timezone` property to the `general` group, which is being used by `GeneralSettings`. You should always give a default value for a newly created setting. In this case, this is the `Europe/Brussels` timezone.

If the property in the settings class is nullable, it's possible to give `null` as a default value.

#### Renaming a property

It is possible to rename a property:

```php
public function up(): void
{
    $this->migrator->rename('general.timezone', 'general.local_timezone');
}
```

You can also move a property to another group:

```php
public function up(): void
{
    $this->migrator->rename('general.timezone', 'country.timezone');
}
```

#### Updating a property

It is possible to update the contents of a property:

```php
public function up(): void
{
    $this->migrator->update(
        'general.timezone', 
        fn(string $timezone) => return 'America/New_York'
    );
}
```

As you can see, this method takes a closure as an argument, which makes it possible to update a value based upon its old value.

#### Deleting a property

```php
public function up(): void
{
    $this->migrator->delete('general.timezone');
}
```

#### Checking a property if it exists

There might be times when you want to check if a property exists in the database. This can be done as such:

```php
public function up(): void
{
    if ($this->migrator->exists('general.timezone')) {
        // do something
    }
}
```

#### Operations in group

When you're working on a big settings class with many properties, it can be a bit cumbersome always to have to prepend the settings group. That's why you can also perform operations within a settings group:

```php
public function up(): void
{
    $this->migrator->inGroup('general', function (SettingsBlueprint $blueprint): void {
        $blueprint->add('timezone', 'Europe/Brussels');
        
        $blueprint->rename('timezone', 'local_timezone');
        
        $blueprint->update('timezone', fn(string $timezone) => return 'America/New_York');
        
        $blueprint->delete('timezone');
    });
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
        return 'regular_type';
    }
}
```

Internally the package will convert these types to JSON and save them as such in the repository. But what about types like `DateTime` and `Carbon` or your own created types? Although these types can be converted to JSON, building them back up again from JSON isn't supported.

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
            'birth_date' => DateTimeInterfaceCast::class
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
            'birth_date' => new DateTimeInterfaceWithTimeZoneCast(DateTime::class, 'Europe/Brussels')
        ];
    }
}
```

As you can see, we provide `DateTime::class` to the cast, so it knows what type of `DateTime` it should use because the `birth_date` property was not typed, and the cast couldn't infer the type to use.

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
            'birth_date' => DateTimeInterfaceCast::class.':'.DateTime::class
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
 // Spatie\DataTransferObject\DataTransferObject::class => Spatie\LaravelSettings\SettingsCasts\DtoCast::class,
    Spatie\LaravelData\Data::class => Spatie\LaravelSettings\SettingsCasts\DataCast::class,
],
```

 A global cast can work on:
 
 - a specific type (`DateTimeZone::class`)
 - a type that implements an interface (`DateTimeInterface::class`)
 - a type that extends from another class (`Data::class`)
 
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

The package will automatically find the cast and will use it to transform the types between the settings class and repository.

#### Typing properties

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
    
    /** @var int|null  */
    public $another_nullable_int;
    
    /** @var int[]|null  */
    public $an_array_of_ints_or_null;
    
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

It is now impossible to update the value of `birth_date`. When trying to overwrite `birth_date` and saving settings, the package will load the old value of `birth_date` from the repository, and it looks like nothing happened.

You can also lock multiple settings at once:

```php
$dateSettings->lock('birth_date', 'name', 'email');
```

You can get all the locked settings:

```php
$dateSettings->getLockedProperties(); // ['birth_date']
```

Unlocking settings can be done as such:

```php
$dateSettings->unlock('birth_date', 'name', 'email');
```

Checking if a setting is currently locked can be done as such:

```php
$dateSettings->isLocked('birth_date');
```

Checking if a setting is currently unlocked can be done as such:

```php
$dateSettings->isUnlocked('birth_date');
```

### Encrypting properties

Some properties in your settings class can be confidential, like API keys, for example. It is possible to encrypt some of your properties, so it won't be possible to read them when your repository data was compromised.

Adding encryption to the properties of your settings class can be done as such. By adding the `encrypted` static method to your settings class and list all the properties that should be encrypted:

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

#### Using encryption in migrations

Creating and updating encrypted properties in migrations works a bit differently than non-encrypted properties.

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

You can make a non-encrypted property encrypted in a migration:

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

### Custom encoders and decoders

It is possible to define custom encoders and decoders instead of the built-in `json_encode` and `json_decode` ones by
changing the package configuration like so:

```php
...
'encoder' => fn($value): string => str_rot13(json_encode($value)),
'decoder' => fn(string $payload, bool $associative) => json_decode(str_rot13($payload), $associative),
...
```

### Faking settings classes

In tests, it is sometimes desired that some settings classes can be quickly used with values different from the default ones you've written in your migrations. That's why you can fake settings. Faked settings classes will be registered in the container. And you can overwrite some or all the properties in the settings class:

```php
DateSettings::fake([
    'birth_date' => new DateTime('16-05-1994')
]);
```

Now, when the `DateSettings` settings class is injected somewhere in your application, the `birth_date` property will be `DateTime('16-05-1994')`.

If all properties are overwritten, no calls to repositories will be made. If only some properties are overwritten, the package will first add the overwritten properties and then load the missing settings from the repository. It is possible to explicitly throw an MissingSettings exception when a property is not overwritten in a fake method call like this:

```php
DateSettings::fake([
    'birth_date' => new DateTime('16-05-1994')
], false);
```

### Caching settings

It takes a small amount of time to load a settings class from a repository. When you've got many settings classes, these added small amounts of time can grow quickly out of hand. The package has built-in support for caching stored settings using the Laravel cache.

You should first enable the cache within the `settings.php` config file:

```php
'cache' => [
    'enabled' => env('SETTINGS_CACHE_ENABLED', false),
    'store' => null,
    'prefix' => null,
],
```

We suggest you enable caching in production by adding `SETTINGS_CACHE_ENABLED=true` to your `.env` file. It is also possible to define a store for the cache, which should be one of the stores you defined in the `cache.php` config file. If no store were defined, the default cache store would be taken. To avoid conflicts within the cache, you can also define a prefix that will be added to each cache entry.

That's it. The package is now smart enough to cache the settings the first time they're loaded. Whenever the settings are edited, the package will refresh the settings.

You can always clear the cached settings with the following command:

```bash
php artisan settings:clear-cache
```

### Auto discovering settings classes

Each settings class you create should be added to the `settings` array within the `settings.php` config file. When you've got a lot of settings, this can be quickly forgotten.

That's why it is also possible to auto-discover settings classes. The package will look through your application and tries to discover settings classes. You can specify the paths where will be searched in the config `auto_discover_settings` array. By default, this is the application's app path.

Autodiscovering settings require some extra time before your application is booted up. That's why it is possible to cache them using the following command:

```bash
php artisan settings:discover
```

You can clear this cache by running:

```bash
php artisan settings:clear-discovered
```

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

A created caster can be used for local and global casts, but there are slight differences between them. The package will always try to inject the type of property it is casting. This type is a class string and will be provided as a first argument when constructing the caster. When it cannot deduce the type, `null` will be used as the first argument.

An example of such caster with a type injected is a simplified `DtoCast`:

```php
class DtoCast implements SettingsCast
{
    private string $type;

    public function __construct(?string $type)
    {
        $this->type = $type;
    }

    public function get($payload): Data
    {
        return $this->type::from($payload);
    }

    public function set($payload): array
    {
        return $payload->toArray();
    }
}
```

The above is a caster for the [spatie/laravel-data](https://github.com/spatie/laravel-data) package, within its constructor, the type will be a specific Data class, for example, `SongData::class`. In the `get` method, the caster will construct a `Data::class` with the repository properties. The caster receives a `Data::class` as payload in the `set` method and converts it to an array for safe storing in the repository.

#### Local casts

When using a local cast, there are a few different possibilities to deduce the type:

```php
// By the type of property

class CastSettings extends Settings 
{
    public DateTime $birth_date;
    
    public static function casts(): array
    {
        return [
            'birth_date' => DateTimeInterfaceCast::class
        ];
    }
    
    ...
}
```

```php
// By the docblock of a property

class CastSettings extends Settings
{
    /** @var \DateTime  */
    public $birth_date;
    
    public static function casts(): array
    {
        return [
            'birth_date' => DateTimeInterfaceCast::class
        ];
    }
    
    ...
}
```


```php
// By explicit definition

class CastSettings extends Settings
{
    public $birth_date;
    
    public static function casts(): array
    {
        return [
            'birth_date' => DateTimeInterfaceCast::class.':'.DateTime::class
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
            'birth_date' => DateTimeWthTimeZoneInterfaceCast::class.':'.DateTime::class.',Europe/Brussels'
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
            'birth_date' => new DateTimeWthTimeZoneInterfaceCast(DateTime::class, 'Europe/Brussels')
        ];
    }
    
    ...
}
```

#### Global casts

When using global casts, the package will again try to deduce the type of property it's casting. In this case, it can only use the property type or infer the type of the property's docblock.

A global cast should be configured in the `settings.php` config file and always has a specific (set) of type(s) it works on. These types can be a particular class, a group of classes implementing an interface, or a group of classes extending from another class.

A good example here is the `DateTimeInterfaceCast` we've added by default in the config. It is defined in the config as such:

```php
    ...

    'global_casts' => [
        DateTimeInterface::class => Spatie\LaravelSettings\SettingsCasts\DateTimeInterfaceCast::class,
    ],
    
    ...
```

Whenever the package detects a `Carbon`, `CarbonImmutable`, `DateTime`, or `DateTimeImmutable` type as the type of one of a settings class's properties. It will use the `DateTimeInterfaceCast` as a caster. This because `Carbon`, `CarbonImmutable`, `DateTime` and `DateTimeImmutable` all implement `DateTimeInterface`. The key that was used in `settings.php` to represent the cast.

The type injected in the caster will be the type of the property. So let's say you have a property with the type `DateTime` within your settings class. When casting this property, the `DateTimeInterfaceCast` will receive `DateTime:class` as a type. 


### Repositories

There are two types of repositories included in the package, the `redis` and `database` repository. You can create multiple repositories for one type in the `setting.php` config file. And each repository can be configured.

#### Database repository

The database repository has two optional configuration options:

- `model` the Eloquent model used to load/save properties to the database
- `table` the table used in the database
- `connection` the connection to use when interacting with the database

#### Redis repository

The Redis repository also has two optional configuration options:

- `prefix` an optional prefix that will be prepended to the keys
- `connection` the connection to use when interacting with Redis

#### Caching

It is possible to add a custom caching configuration per repository, by adding a cache configuration like the default one to your repository config within the `settings.php` config file:

```php
    'repositories' => [
        'landlord' => [
            'type' => Spatie\LaravelSettings\SettingsRepositories\DatabaseSettingsRepository::class,
            'model' => null,
            'table' => null,
            'connection' => 'landlord',
            'cache' => [
                'enabled' => env('SETTINGS_CACHE_ENABLED', false),
                'store' => null,
                'prefix' => 'landlord',
                'ttl' => null,
            ],
        ],
        
        ...
    ],
```

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
     * Update the payloads of properties within a group.
     */
    public function updatePropertiesPayload(string $group, array $properties): void;

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

#### Refreshing settings

You can refresh the values and locked properties within the settings class. This can be useful if you change something within your repository and want to see it reflected within your settings:

```php
$settings->refresh();
```

You should only refresh settings when the repository values were changed when the settings class was already loaded.

### Events

The package will emit a series of events when loading/saving settings classes:

- `LoadingSettings` whenever settings are loaded from the repository but not yet inserted in the settings class
- `SettingsLoaded` after settings are loaded into the settings class
- `SavingSettings` whenever settings are saved to the repository but are not yet cast or encrypted
- `SettingsSaved` after settings are stored within the repository

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security

If you've found a bug regarding security please mail [security@spatie.be](mailto:security@spatie.be) instead of using the issue tracker.

## Credits

- [Ruben Van Assche](https://github.com/rubenvanassche)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

