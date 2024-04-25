<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    protected $table = 'projects';

    protected $fillable = ['url', 'name', 'favorite', 'description', 'finished', 'date_end', 'user_id'];

    public function diagrams()
    {
        return $this->hasMany(Diagram::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'project_collaborations');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function timeLeft()
    {
        return Carbon::parse($this->date_end)->longAbsoluteDiffForHumans();
    }

    public function percentageFinished()
    {
        if (count($this->diagrams)) {
            return (int) (count($this->diagrams()->where('finished', 1)->get()) * 100 / count($this->diagrams));
        }
        return 0;
    }
}
