<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IvrSetting extends Model
{
    protected $fillable = ['key', 'value', 'description', 'created_by'];
}