# Store your application settings

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-settings.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-settings)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/spatie/laravel-settings/run-tests?label=tests)](https://github.com/spatie/laravel-settings/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-settings.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-settings)

**Under development, do not use!**

This package allows you to store settings in a repository (database, redis, ...) and use them through an application without hassle. You create a settings DTO as such:

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

Now, when you want to use these settings somewhere in your application, you can inject them since we register them in the Laravel Container. For example in a controller:

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

You can update settings as such:

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

Let's take a look on how to create your own settings.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-settings.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/laravel-settings)

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
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    |
    | You can register all the settings DTO's here.
    |
    */

    'settings' => [

    ],

    /*
    |--------------------------------------------------------------------------
    | Migrations path
    |--------------------------------------------------------------------------
    |
    | When you create a new settings migration via the `make:settings-migration`
    | command the package will store these migrations in this directory.
    |
    */

    'migrations_path' => database_path('settings'),

    /*
    |--------------------------------------------------------------------------
    | Default repository
    |--------------------------------------------------------------------------
    |
    | When no repository was set for a settings DTO this repository will be
    | used for loading and saving settings.
    |
    */

    'default_repository' => 'database',

    /*
    |--------------------------------------------------------------------------
    | Repositories
    |--------------------------------------------------------------------------
    |
    | Settings will be stored and loaded from these repositories. There are
    | two types of repositories: database and redis. But its always
    | possible to create your own repositories.
    |
    */

    'repositories' => [
        'database' => [
            'type' => Spatie\LaravelSettings\SettingsRepositories\DatabaseSettingsRepository::class,
            'model' => \Spatie\LaravelSettings\Models\SettingsProperty::class,
            'connection' => null,
        ],
        'redis' => [
            'type' => Spatie\LaravelSettings\SettingsRepositories\RedisSettingsRepository::class,
            'connection' => null,
            'prefix' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global casts
    |--------------------------------------------------------------------------
    |
    | When the package discovers a setting with a type other than the PHP built
    | in types, it should be casted. These casts will automatically cast types
    | when they occur in a settings DTO.
    |
    */

    'global_casts' => [
        DateTimeInterface::class => Spatie\LaravelSettings\SettingsCasts\DateTimeInterfaceCast::class,
        DateTimeZone::class => Spatie\LaravelSettings\SettingsCasts\DateTimeZoneCast::class,
        Spatie\DataTransferObject\DataTransferObject::class => Spatie\LaravelSettings\SettingsCasts\DtoCast::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto discover setting
    |--------------------------------------------------------------------------
    |
    | The package will look for settings in these paths and automatically
    | register them.
    |
    */

    'auto_discover_settings' => [
        app()->path(),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache path
    |--------------------------------------------------------------------------
    |
    | When in production it is advised to cache the automatically discovered
    | and registered setting DTO's these will be cached in this path.
    |
    */

    'cache_path' => storage_path('app/laravel-settings'),
];

```

## Usage

The package is built arround setting DTO's, basicly these are classes with some public properties that extend from `Settings`. They also have a static method `group` that should return a string.

You can create multiple groups of settings each with their own DTO, you could for example have `GeneralSettings` with the `general` group and `BlogSettings` with the `blog` group. It's up to you how to structure these settings.

Although it is possible to use the same group for different DTO's we advise you not to use the same group for multiple setting DTO's.


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

Each property should be typed or partially typed by a docblock, more on that later.

In the end you will have to add this DTO to the `settings.php` config file in the `settings` section so it can be loaded by Laravel:

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    |
    | You can register all the settings dto's here.
    |
    */

    'settings' => [
		GeneralSettings::class
    ],
```

It is also possible to autodiscover settings DTO's, the package will look through your application and tries to discover Settings DTO's. You can specify the paths where these settings will be searched in the config `auto_discover_settings` array. By default this is the application's app path.

Autodiscovering settings requires some extra time before you're application is booted up, that's why it is possible to cache them using the following command:

```bash
php artisan settings:discover
```

You can clear this cache by running:

```bash
php artisan settings:clear-discovered
```

### Selecting a repository

Settings will be stored and loaded from a repository, there are two types of repositories `database` and `redis`. And it is possible to create multiple repositories for these types. For example you could have two `database` repositories, one that goes to a `settings` table in your database and one that goes to a `global_settings` table.

You can explicitly set the repository of a settings DTO by implementing the `repository` method:

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

When a repository is not set for a settings DTO, the `default_repository` in the `settings.php` config file will be used.

### Creating settings migrations

Before you will be able to load/update settings you will have to migrate them, though this might sound a bit strange at the beginning it is actually quite logic. You want to have some default settings to begin with when you're creating a fresh application. And what would happen if we change a settings DTO? Our code would change but our data doesn't.

This is why the package requires migrations each time you're changing/creating the structure of your settings DTO. These migrations will run next to the regular Laravel database migrations and we've added some tooling to write them as quickly as possible.

Creating a settings migration works just like you would create a regular database migration, you can run the following command:

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

We haven't added a `down` method but this can be added manually if required. In the `up` method you can change the settings data in a repository when migrating. There are a few default operations supported:

#### Adding a property

You can add a property to a settings group as such

```php
public function up(): void
{
	$this->migrator->add('general.timezone', 'Europe/Brussels');
}
```

Now we've added a `timezone` property to the `general` group which is being used by the `GeneralSettings` DTO. You should always give a default value for a newly created setting in this case this is the `Europe/Brussels` timezone.

If the property in the settings DTO is nullable, then it's perfectly possible to give `null` as a default value.

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

As you can see, this method takes a closure as an argument. This makes it possible to update a value based upon its old value.

#### Deleting a property

```php
public function up(): void
{
	$this->migrator->delete('general.timezone');
}
```

#### Operations in group

When you're working on a big DTO with a lot of properties it can be a bit cumbersome to always have to prepend the settings group. That's why you can also perform operations within a settings group:

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

### Typing properties

It is possible to create a settings DTO with regular PHP types:


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

Internally the package will convert these types to JSON and save them as such in the repository. But what about types like `DateTime` and `Carbon` or your own created types? Although these types can be converted to JSON, building them back up again from JSON isn't supported.

That's why you can specify casts within this package, there are two ways to define these casts: locally or glablly.

#### Local casts

Local casts work on one specific settings DTO and should be defined for each property:

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

The `DateTimeInterfaceCast` can be used for properties with types like `DateTime`, `DateTimeImmutable`, `Carbon` and `CarbonImmutable`. You can also use an already constructed cast, this becomes handy when you need to pass some extra arguments to the cast:



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
        	'bith_date' => new DateTimeInterfaceCast(DateTime::class)
        ];
    }
}
```

As you can see we provide `DateTime::class` to the cast so it knows what type of `DateTime` it should use, this is because the `birth_date` property was not typed and the cast couldn't infer the type to use.

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
        	'bith_date' => DateTimeInterfaceCast::class.':'.DateTime::class
        ];
    }
}
```

#### Global casts

Local casts are great for defining types for specific properties of the settings DTO. But it's a lot of work to define a local cast for each regullary used type like a `DateTime`. Global casts try to simplify this process.

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
 - a type that extends from another class `DataTransferObject::class`
 
In your settings DTO when you use a `DateTime` property (which implements `DateTimeInterface`) you no longer have to define local casts:

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

The package will automatically find the cast and will use it to transform types between the settings DTO and repository.

### Typing properties

There are quite a few options to type properties, you could type them in PHP:

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

Docblocks can be very usefull to type arrays of objects:

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

When you want to disable the ability to update the value of a setting you can add a lock to it:

```php
$dateSettings->lock('birth_date');
```

It is now impossible to update the value of `birth_date`. When `birthdate` was overwritten and settings were saved then the package will load the old value of `birthdate` from the repository.

You can also lock multiple settings at once:

```php
$dateSettings->lock('birth_date', 'name', 'email');
```

Unlocking settings can be done as such:

```php
$dateSettings->unlock('birth_date', 'name', 'email');
```

### Encrypting properties

### Faking settings

### Creating casts

### Writing your own casts

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
