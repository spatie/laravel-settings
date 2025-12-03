<?php

namespace Spatie\LaravelSettings;

use Exception;
use Illuminate\Container\Container;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;
use ReflectionProperty;
use Spatie\LaravelSettings\Events\SavingSettings;
use Spatie\LaravelSettings\Events\SettingsLoaded;
use Spatie\LaravelSettings\Events\SettingsSaved;
use Spatie\LaravelSettings\Exceptions\MissingSettings;
use Spatie\LaravelSettings\SettingsRepositories\SettingsRepository;
use Spatie\LaravelSettings\Support\Crypto;

abstract class Settings implements Arrayable, Jsonable, Responsable
{
    private SettingsMapper $mapper;

    private SettingsConfig $config;

    private bool $loaded = false;

    private bool $configInitialized = false;

    protected ?Collection $originalValues = null;

    abstract public static function group(): string;

    public static function repository(): ?string
    {
        return null;
    }

    public static function casts(): array
    {
        return [];
    }

    public static function encrypted(): array
    {
        return [];
    }

    public static function cacheKey(): string
    {
        return static::class;
    }

    /**
     * @param array $values
     *
     * @return static
     */
    public static function fake(array $values, bool $loadMissingValues = true): self
    {
        $settingsMapper = app(SettingsMapper::class);

        $propertiesToLoad = $settingsMapper->initialize(static::class)
            ->getReflectedProperties()
            ->keys()
            ->reject(fn (string $name) => array_key_exists($name, $values));

        if ($propertiesToLoad->isEmpty()) {
            return app(Container::class)->instance(static::class, new static(
                $values
            ));
        }

        if ($propertiesToLoad->isNotEmpty() && $loadMissingValues === false) {
            throw MissingSettings::create(static::class, $propertiesToLoad->toArray(), 'loading fake');
        }

        $mergedValues = $settingsMapper
            ->fetchProperties(static::class, $propertiesToLoad)
            ->merge($values)
            ->all();

        return app(Container::class)->instance(static::class, new static(
            $mergedValues
        ));
    }

    final public function __construct(array $values = [])
    {
        foreach ($this->settingsConfig()->getReflectedProperties() as $name => $property) {
            if (method_exists($property, 'isReadOnly') && $property->isReadOnly()) {
                continue;
            }

            unset($this->{$name});
        }

        if (! empty($values)) {
            $this->loadValues($values);
        }
    }

    public function __get($name)
    {
        $this->loadValues();

        return $this->{$name};
    }

    public function __set($name, $value)
    {
        $this->loadValues();

        $this->{$name} = $value;
    }

    public function __debugInfo(): array
    {
        try {
            $this->loadValues();

            return $this->toArray();
        } catch (Exception $exception) {
            return [
                'Could not load values',
            ];
        }
    }

    public function __isset($name)
    {
        $this->loadValues();

        return isset($this->{$name});
    }

    public function __serialize(): array
    {
        /** @var Collection $encrypted */
        /** @var Collection $nonEncrypted */
        [$encrypted, $nonEncrypted] = $this->toCollection()->partition(
            fn ($value, string $name) => $this->settingsConfig()->isEncrypted($name)
        );

        return array_merge(
            $encrypted->map(fn ($value) => Crypto::encrypt($value))->all(),
            $nonEncrypted->all(),
            ['_settingsLoadedFromCache' => $this->settingsConfig()->isLoadedFromCache()]
        );
    }

    public function __unserialize(array $data): void
    {
        $this->loaded = false;

        $settingsLoadedFromCache = $data['_settingsLoadedFromCache'] ?? null;
        unset($data['_settingsLoadedFromCache']);
        $this->settingsConfig()->markLoadedFromCache($settingsLoadedFromCache ?? false);

        /** @var Collection $encrypted */
        /** @var Collection $nonEncrypted */
        [$encrypted, $nonEncrypted] = collect($data)->partition(
            fn ($value, string $name) => $this->settingsConfig()->isEncrypted($name)
        );

        $data = array_merge(
            $encrypted->map(fn ($value) => Crypto::decrypt($value))->all(),
            $nonEncrypted->all()
        );

        $this->loadValues($data);
    }

    /**
     * @param \Illuminate\Support\Collection|array $properties
     *
     * @return $this
     */
    public function fill($properties): self
    {
        foreach ($properties as $name => $payload) {
            $this->{$name} = $payload;
        }

        return $this;
    }

    public function save(): self
    {
        $properties = $this->toCollection();

        event(new SavingSettings($properties, $this->originalValues, $this));

        $values = $this->mapper->save(static::class, $properties);

        $this->fill($values);
        $this->originalValues = $values;

        event(new SettingsSaved($this));

        return $this;
    }

    public function lock(string ...$properties)
    {
        $this->settingsConfig()->lock(...$properties);
    }

    public function unlock(string ...$properties)
    {
        $this->settingsConfig()->unlock(...$properties);
    }

    public function isLocked(string $property): bool
    {
        return in_array($property, $this->getLockedProperties());
    }

    public function isUnlocked(string $property): bool
    {
        return ! $this->isLocked($property);
    }

    public function getLockedProperties(): array
    {
        return $this->settingsConfig()->getLocked()->toArray();
    }

    public function toCollection(): Collection
    {
        return $this->settingsConfig()
            ->getReflectedProperties()
            ->mapWithKeys(fn (ReflectionProperty $property) => [
                $property->getName() => $this->{$property->getName()},
            ]);
    }

    public function toArray(): array
    {
        return $this->toCollection()->toArray();
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    public function toResponse($request)
    {
        return response()->json($this->toJson());
    }

    public function getRepository(): SettingsRepository
    {
        return $this->settingsConfig()->getRepository();
    }

    public function refresh(): self
    {
        $this->settingsConfig()->clearCachedLockedProperties();

        $this->loaded = false;
        $this->loadValues();

        return $this;
    }

    public function settingsConfig(): SettingsConfig
    {
        $this->ensureConfigIsLoaded();

        return $this->config;
    }

    private function loadValues(?array $values = null): self
    {
        if ($this->loaded) {
            return $this;
        }

        $values ??= $this->mapper->load(static::class);

        $this->loaded = true;

        $this->fill($values);
        $this->originalValues = collect($values);

        event(new SettingsLoaded($this, $this->settingsConfig()->isLoadedFromCache()));

        return $this;
    }

    private function ensureConfigIsLoaded(): self
    {
        if ($this->configInitialized) {
            return $this;
        }

        $this->mapper = app(SettingsMapper::class);
        $this->config = $this->mapper->initialize(static::class);
        $this->configInitialized = true;

        return $this;
    }
}
