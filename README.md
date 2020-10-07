# Store your application settings

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-settings.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-settings)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/spatie/laravel-settings/run-tests?label=tests)](https://github.com/spatie/laravel-settings/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-settings.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-settings)

**Under development, do not use!**

So you've got an application with some settings, these settings are stored within your database, a redis instance or something else and you want to have access to them through your whole application. Wouldn't it be cool if these settings were typed objects that you could inject everywhere in your application? With as an added bonus that your ide can typehint these settings?

In this package you can create a settings DTO:

```php
class GlobalSettings extends Settings
{
    public string $timezone;

	 public bool $enable_submissions;

    public static function group(): string
    {
        return 'global';
    }
}
```

Now wherever you can inject something in your Laravel application (for example in the controller) and get values from the settings:

```php
public function currentTime(GlobalSettings $settings){
	return Carbon::now()->withTimezone($settings->timezone);
}
```

Saving settings can be done as such:

```php
public function updateTimezone(GlobalSettings $settings, Request $request){
	$settings->timezone = $request->input('timezone');
	$settings->save();
	
	return redirect()->back();
}
```

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
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    |
    | You can register all the settings dto's here
    |
    */
    'settings' => [

    ],

    /*
    |--------------------------------------------------------------------------
    | Migrations path
    |--------------------------------------------------------------------------
    |
    | When creating new setting migrations, the files will be stored in this
    | directory
    |
    */
    'migrations_path' => database_path('settings'),

    /*
    |--------------------------------------------------------------------------
    | Default repository
    |--------------------------------------------------------------------------
    |
    | When no repository explicitly was given to a settings dto this
    | repository will be used for loading and saving settings.
    |
    */
    'default_repository' => 'database',

    /*
    |--------------------------------------------------------------------------
    | Repositories
    |--------------------------------------------------------------------------
    |
    | In these repositories you can store you own settings, types of
    | repositories include database and redis, or you can create
    | your own repository types.
    |
    */
    'repositories' => [
        'database' => [
            'type' => Spatie\LaravelSettings\SettingsRepositories\DatabaseSettingsRepository::class,
            'model' => \Spatie\LaravelSettings\Models\SettingsProperty::class,
            'connection' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Repositories
    |--------------------------------------------------------------------------
    |
    | Types other than the primitive PHP types can be converted from and to
    | repositories by these casts.
    |
    */
    'casts' => [
        DateTime::class => Spatie\LaravelSettings\SettingsCasts\DateTimeInterfaceCast::class,
        DateTimeImmutable::class => Spatie\LaravelSettings\SettingsCasts\DateTimeImmutableCast::class,
        Carbon::class => Spatie\LaravelSettings\SettingsCasts\CarbonCast::class,
        CarbonImmutable::class => Spatie\LaravelSettings\SettingsCasts\CarbonImmutableCast::class,
    ],
];
```

## Usage

Let's get started by creating a Settings Dto, under the hood this is actually a data-transfer-object from our [data-transfer-object](https://github.com/spatie/data-transfer-object) package. A Settings Dto is a class that extends `Settings` and has a static function `group`, that's a string describing to which group of settings it belongs. 

```php
class GlobalSettings extends Settings
{
	public string $timezone;

 	public bool $enable_submissions;

    public static function group(): string
    {
        return 'global';
    }
}
```

You can create multiple groups of settings each with their own Dto, you could for example have `GlobalSettings`with the `global` group and `BlogSettings` with the `blog` group. It's up to you how to structure these settings.

Although it is possible to use the same group for different Dto's we don't recommand using a group identifier more than once.

You can add this settings DTO to your config file in the `settings` section, so it can be injected into the application when needed. Or let the package autodiscover settings, more on that later.

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
