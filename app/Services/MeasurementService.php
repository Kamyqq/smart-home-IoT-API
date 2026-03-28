<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\FireAlertEvent;
use App\Events\HighHumidityEvent;
use App\Models\Room;
use Illuminate\Support\Facades\Cache;

class MeasurementService
{
    public function storeMeasurement(Room $room, array $data): void
    {
        $sensors = $room->sensors->keyBy('type');

        if (isset($data['temperature']) && isset($sensors['temperature'])) {
            $sensors['temperature']->measurements()->create(['value' => $data['temperature']]);

            if ((float) $data['temperature'] > config('smarthome.thresholds.fire_temperature')) {
                $this->triggerFireAlarm($room, (float) $data['temperature']);
            }
        }

        if (isset($data['humidity']) && isset($sensors['humidity'])) {
            $sensors['humidity']->measurements()->create(['value' => $data['humidity']]);

            if ((float) $data['humidity'] > config('smarthome.thresholds.humidity')) {
                $this->triggerFloodAlarm($room, (float) $data['humidity']);
            }
        }

        if (isset($data['light']) && isset($sensors['light'])) {
            $sensors['light']->measurements()->create(['value' => $data['light']]);
        }
    }

    public function triggerFireAlarm(Room $room, float $temperature): void
    {
        $cacheKey = 'fire_alert_room_' . $room->id;

        if (!Cache::has($cacheKey)) {
            $owner = $room->house->user;

            FireAlertEvent::dispatch($room, $owner, $temperature);

            $cooldown = config('smarthome.settings.alert_cooldown_minutes');
            Cache::put($cacheKey, true, now()->addMinutes((int) $cooldown));
        }
    }

    public function triggerFloodAlarm(Room $room, float $humidity): void
    {
        $cacheKey = 'flood_alert_room_' . $room->id;

        if (!Cache::has($cacheKey)) {
            $owner = $room->house->user;

            HighHumidityEvent::dispatch($room, $owner, $humidity);

            $cooldown = config('smarthome.settings.alert_cooldown_minutes');
            Cache::put($cacheKey, true, now()->addMinutes((int) $cooldown));
        }
    }

    public function changeRoomStates(Room $room): void
    {
        $lightSensor = $room->sensors()->where('type', 'light')->first();

        if (!$lightSensor) return;

        $measurementsCount = (int) config('smarthome.settings.light_measurements_count');
        $recentData = $lightSensor->measurements()->latest()->take($measurementsCount)->get();

        if ($recentData->count() < config('smarthome.settings.light_measurements_count')) return;

        $avgLight = $recentData->avg('value');

        if ($avgLight < config('smarthome.thresholds.light_min')) {
            $turnOnLight = true;
        } elseif ($avgLight > config('smarthome.thresholds.light_max')) {
            $turnOnLight = false;
        } else {
            return;
        }

        $lastAction = $room->actions()->where('type', 'light')->latest()->first();

        if (!$lastAction || (bool)$lastAction->value !== $turnOnLight) {
            $room->actions()->create([
                'type' => 'light',
                'value' => $turnOnLight,
            ]);
        }
    }

    public function getRoomChartData(Room $room): array
    {
        $limit = (int) config('smarthome.settings.chart_data_limit');

        $room->load(['sensors.measurements' => function ($query) use ($limit) {
            $query->latest()->limit($limit);
        }]);

        $data = [];

        foreach ($room->sensors as $sensor) {
            $measurements = $sensor->measurements->reverse();

            $data[$sensor->type] = [
                'labels' => $measurements->pluck('created_at')
                    ->map(fn($d) => $d?->format('H:i:s'))
                    ->values(),
                'values' => $measurements->pluck('value')
                    ->values(),
            ];
        }

        return $data;
    }
}
