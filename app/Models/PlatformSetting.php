<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'updated_by',
    ];

    /**
     * Get a setting value by key with optional default fallback.
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        if (!$setting) {
            return $default;
        }

        return match ($setting->type) {
            'integer' => (int) $setting->value,
            'decimal', 'float' => (float) $setting->value,
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    /**
     * Set/update a setting value by key.
     */
    public static function setValue(string $key, $value, ?string $type = null, ?string $description = null, ?int $updatedBy = null)
    {
        $type = $type ?? (is_bool($value) ? 'boolean' : (is_numeric($value) ? (is_float($value) ? 'decimal' : 'integer') : 'string'));

        if (is_array($value) || is_object($value)) {
            $type = 'json';
            $value = json_encode($value);
        } elseif (is_bool($value)) {
            $value = $value ? '1' : '0';
        }

        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => (string) $value,
                'type' => $type,
                'description' => $description,
                'updated_by' => $updatedBy,
            ]
        );
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
