<?php

namespace Spatie\LaravelSettings;

use Spatie\LaravelSettings\Settings;
use ReflectionClass;
use ReflectionProperty;

class SettingsInspector
{
    public static function getSettingsTypes(array|Settings $settingsClasses): array
    {
        $settingsCasts = [];

        if ( $settingsClasses instanceof Settings ) {
            $reflection = new ReflectionClass($settingsClasses);
            $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
            
            foreach ($properties as $property) {
                $type = $property->getType();
                $typeName = $type ? $type->getName() : 'mixed';

                $settingsCasts[$property->getName()] = [
                    'type' => $typeName,
                    'nullable' => $type ? $type->allowsNull() : false,
                ];
            }

        } elseif ( is_array($settingsClasses) ) {
            foreach ($settingsClasses as $group => $settingsClass) {
                $reflection = new ReflectionClass($settingsClass);
                $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
    
                foreach ($properties as $property) {
                    $type = $property->getType();
                    $typeName = $type ? $type->getName() : 'mixed';
    
                    $settingsCasts[$group][$property->getName()] = [
                        'type' => $typeName,
                        'nullable' => $type ? $type->allowsNull() : false,
                    ];
                }
            }
        }

        return $settingsCasts;
    }
}
