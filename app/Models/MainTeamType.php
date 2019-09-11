<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MainTeamType extends Model
{
    protected $table = "main_team_type";
    protected $fillable = ['team_type_name','team_type_status'];
}
