<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = ["house_id", "name"];

    public function sensors()
    {
        return $this->hasMany(Sensor::class);
    }

    public function actions()
    {
        return $this->hasMany(Action::class);
    }

    public function house()
    {
        return $this->belongsTo(House::class);
    }
}
