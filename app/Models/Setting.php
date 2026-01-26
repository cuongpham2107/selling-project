<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'type', 'description'];

    public static function getValue(string $key, $default = null)
    {
        $setting = self::query()->where('key', '=', $key)->first();

        if (! $setting) {
            return $default;
        }

        return match ($setting->type) {
            'int', 'integer' => (int) $setting->value,
            'float', 'double' => (float) $setting->value,
            'bool', 'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    public static function setValue(string $key, $value, ?string $type = null, ?string $description = null)
    {
        $type = $type ?? (is_array($value) ? 'json' : (is_bool($value) ? 'bool' : (is_numeric($value) ? (is_float($value) ? 'float' : 'int') : 'string')));

        $encodedValue = is_array($value) ? json_encode($value) : (string) $value;

        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $encodedValue,
                'type' => $type,
                'description' => $description,
            ]
        );
    }
}
