<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $table = 'site_settings';

    protected $fillable = ['key', 'value', 'type'];

    /**
     * 获取配置值（带缓存）
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("site_setting:{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            if (!$setting) {
                return $default;
            }
            return match ($setting->type) {
                'boolean' => in_array($setting->value, ['true', '1', 'on'], true),
                'json'   => json_decode($setting->value, true) ?? $default,
                default  => $setting->value,
            };
        });
    }

    /**
     * 设置配置值（清除缓存）
     */
    public static function set(string $key, mixed $value, string $type = 'string'): void
    {
        $stored = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value;

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $stored, 'type' => $type]
        );

        Cache::forget("site_setting:{$key}");
    }
}
