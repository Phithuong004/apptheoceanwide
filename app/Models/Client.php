<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ClientContact;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'workspace_id','name','company','email','phone','website',
        'avatar','address','country','currency','status','notes','social_links',
    ];

    protected $casts = ['social_links' => 'array'];

    public function workspace()  { return $this->belongsTo(Workspace::class); }
    public function contacts()   { return $this->hasMany(ClientContact::class); }
    public function projects()   { return $this->hasMany(Project::class); }
    public function invoices()   { return $this->hasMany(Invoice::class); }

    public function primaryContact()
    {
        return $this->hasOne(ClientContact::class)->where('is_primary', true);
    }

    public function getTotalBilledAttribute(): float
    {
        return $this->invoices()->where('status','paid')->sum('total');
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('storage/'.$this->avatar)
            : 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=0ea5e9&color=fff';
    }
}
