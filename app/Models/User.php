<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function myProjects()
    {
        return $this->hasMany(Project::class)->orderBy('id', 'asc');
    }

/*     public function projectCollaborations()
    {
        return $this->hasMany(ProjectCollaboration::class)->orderBy('id', 'desc');
    } */

    public function projectCollaborations()
    {
        return $this->belongsToMany(Project::class, 'project_collaborations')->orderBy('id', 'desc');
    }

    public function myDiagrams()
    {
        return $this->hasMany(Diagram::class)->orderBy('id', 'desc');
    }

    public function diagramCollaborations()
    {
        return $this->hasMany(diagramCollaboration::class)->orderBy('id', 'desc');
    }

    public function diagrams()
    {
        return $this->belongsToMany(Diagram::class, 'diagram_collaborations')->orderBy('id', 'desc');
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    public function invitacion($project_id)
    {
        $invitations = $this->invitations()->where('project_id', $project_id)->get();
        $id = 0;
        foreach ($invitations as $noti) {
            $id = $noti->id;
        }
        return $id;
    }
}
