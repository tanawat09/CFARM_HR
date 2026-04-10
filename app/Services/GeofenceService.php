<?php

namespace App\Services;

class GeofenceService
{
    /**
     * Calculate distance between two coordinates in meters using Haversine formula
     *
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float Distance in meters
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Check if a coordinate is within a radius of another coordinate
     *
     * @param float $checkLat
     * @param float $checkLon
     * @param float $centerLat
     * @param float $centerLon
     * @param int $radiusInMeters
     * @return bool
     */
    public function isWithinRadius(float $checkLat, float $checkLon, float $centerLat, float $centerLon, int $radiusInMeters): bool
    {
        $distance = $this->calculateDistance($checkLat, $checkLon, $centerLat, $centerLon);
        return $distance <= $radiusInMeters;
    }
}
