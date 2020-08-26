<?php

namespace Spatie\LaravelSettings\Tests\TestClasses;

class FakeAction
{
    private DummySimpleSettings $settings;

    public function __construct(DummySimpleSettings $settings)
    {
        $this->settings = $settings;
    }

    public function getSettings(): DummySimpleSettings
    {
        return $this->settings;
    }

    public function updateSettings(): self
    {
        $this->settings->name = 'updated';
        $this->settings->save();

        return $this;
    }
}
