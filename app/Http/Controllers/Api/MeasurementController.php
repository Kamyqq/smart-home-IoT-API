<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMeasurementsRequest;
use App\Models\Measurement;
use App\Models\Room;
use App\Services\MeasurementService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class MeasurementController extends Controller
{
    use AuthorizesRequests;

    public function __construct(public MeasurementService $measurementService) {}

    public function store(StoreMeasurementsRequest $request): JsonResponse
    {
        $device = $request->attributes->get('device');
        $room = $device->room;

        $this->measurementService->storeMeasurement($room, $request->validated());
        $this->measurementService->changeRoomStates($room);

        return response()->json(['message' => 'Measurements saved'], 200);
    }

    public function index(Room $room): JsonResponse
    {
        $this->authorize('view', $room->house);
        $measurements = $this->measurementService->getRoomChartData($room);

        return response()->json($measurements);
    }
}
