<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
    use HasFactory;

    protected $fillable = ["room_id", "type"];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function measurements()
    {
        return $this->hasMany(Measurement::class);
    }
}
