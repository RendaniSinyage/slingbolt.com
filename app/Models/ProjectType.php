<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectType extends Model
{
    // Define all available project types
    const STANDARD = 'standard';
    const ROADMAP = 'roadmap';
   // const DEVELOPMENT = 'development';
   // const MARKETING = 'marketing';
   // const RESEARCH = 'research';

    /**
     * Get all available project types
     */
    public static function getTypes()
    {
        return [
            self::STANDARD => 'Standard Project',
            self::ROADMAP => 'Product Roadmap',
         //   self::DEVELOPMENT => 'Software Development', 
         //   self::MARKETING => 'Marketing Campaign',
         //   self::RESEARCH => 'Research Project',
        ];
    }

    /**
     * Get type label
     */
    public static function getTypeLabel($type)
    {
        $types = self::getTypes();
        return $types[$type] ?? 'Unknown Type';
    }

    /**
     * Get type options for dropdowns
     */
    public static function getTypeOptions()
    {
        return collect(self::getTypes())->map(function($label, $value) {
            return [
                'value' => $value,
                'label' => $label
            ];
        })->values();
    }

    /**
     * Validate if type exists
     */
    public static function isValidType($type)
    {
        return array_key_exists($type, self::getTypes());
    }

    /**
     * Get default type
     */
    public static function getDefaultType()
    {
        return self::STANDARD;
    }
}