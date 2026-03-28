<?php

namespace App\Policies;

use App\Models\House;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class HousePolicy
{
    public function view(User $user, House $house): bool
    {
        return $user->id === $house->user_id;
    }
}
