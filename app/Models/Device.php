<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = ["name", "room_id", "api_key", "is_active", "last_seen_at"];

    public function casts(): array
    {
        return [
            "last_seen_at" => "datetime",
            "is_active" => "boolean",
        ];
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
