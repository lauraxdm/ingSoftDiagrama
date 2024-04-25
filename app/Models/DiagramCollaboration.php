<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiagramCollaboration extends Model
{
    use HasFactory;

    protected $table = 'diagram_collaborations';
    protected $fillable = ['user_id', 'diagram_id','edit'];
}
