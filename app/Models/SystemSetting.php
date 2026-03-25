<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = ['workspace_id','key','value','type','group'];

    public static function get(string $key, mixed $default = null, ?int $workspaceId = null): mixed
    {
        $cacheKey = "setting_{$workspaceId}_{$key}";
        return Cache::remember($cacheKey, 3600, function () use ($key, $default, $workspaceId) {
            $setting = static::where('key', $key)
                             ->where('workspace_id', $workspaceId)
                             ->first();
            if (!$setting) return $default;
            return match($setting->type) {
                'boolean' => (bool) $setting->value,
                'integer' => (int)  $setting->value,
                'json'    => json_decode($setting->value, true),
                default   => $setting->value,
            };
        });
    }

    public static function set(string $key, mixed $value, string $type = 'string', ?int $workspaceId = null): void
    {
        static::updateOrCreate(
            ['key' => $key, 'workspace_id' => $workspaceId],
            ['value' => is_array($value) ? json_encode($value) : $value, 'type' => $type]
        );
        Cache::forget("setting_{$workspaceId}_{$key}");
    }
}
