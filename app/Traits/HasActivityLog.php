<?php
namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

trait HasActivityLog
{
    public static function bootHasActivityLog(): void
    {
        static::updated(function (Model $model) {
            if (!auth()->check()) return;
            $changes = $model->getChanges();
            unset($changes['updated_at']);
            if (empty($changes)) return;

            ActivityLog::create([
                'user_id'       => auth()->id(),
                'subject_type'  => get_class($model),
                'subject_id'    => $model->id,
                'action'        => 'updated',
                'properties'    => json_encode([
                    'old' => array_intersect_key($model->getOriginal(), $changes),
                    'new' => $changes,
                ]),
            ]);
        });
    }

    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }
}
