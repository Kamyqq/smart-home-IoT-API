<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    protected $fillable = ["room_id", "type", "value"];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
