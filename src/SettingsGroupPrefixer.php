<?php

namespace Spatie\LaravelSettings;

class SettingsGroupPrefixer
{
    private string $prefix = '';

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @param  string  $prefix
     */
    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }
}
