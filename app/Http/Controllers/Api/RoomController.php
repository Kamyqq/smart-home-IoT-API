<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Action;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function decisions(Request $request)
    {
        $device = $request->attributes->get('device');
        $room = $device->room;

        $action = $room->actions()
            ->where('type', 'light')
            ->latest()
            ->first();

        return response()->json([
            'turn_on_light' => $action ? (int)$action->value : 0
        ]);
    }
}
