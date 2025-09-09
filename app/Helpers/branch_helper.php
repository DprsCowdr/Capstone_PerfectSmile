<?php

if (! function_exists('normalizeOperatingHours')) {
    function normalizeOperatingHours(array $branch = []): array
    {
        $defaults = [
            'monday'    => ['enabled' => true, 'open' => '09:00', 'close' => '17:00'],
            'tuesday'   => ['enabled' => true, 'open' => '09:00', 'close' => '17:00'],
            'wednesday' => ['enabled' => true, 'open' => '09:00', 'close' => '17:00'],
            'thursday'  => ['enabled' => true, 'open' => '09:00', 'close' => '17:00'],
            'friday'    => ['enabled' => true, 'open' => '09:00', 'close' => '17:00'],
            'saturday'  => ['enabled' => false, 'open' => '09:00', 'close' => '13:00'],
            'sunday'    => ['enabled' => false, 'open' => '00:00', 'close' => '00:00'],
        ];

        $raw = $branch['operating_hours'] ?? [];
        // If stored as JSON string, decode it
        if (is_string($raw) && strlen(trim($raw)) > 0) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) $raw = $decoded;
        }

        $normalized = [];
        foreach ($defaults as $day => $def) {
            $dayData = $raw[$day] ?? $raw[ucfirst($day)] ?? [];
            $normalized[$day] = [
                'enabled' => isset($dayData['enabled']) ? (bool)$dayData['enabled'] : $def['enabled'],
                'open'    => $dayData['open'] ?? $def['open'],
                'close'   => $dayData['close'] ?? $def['close'],
            ];
        }

        return $normalized;
    }
}

if (! function_exists('getNotifications')) {
    function getNotifications(array $branch = [], $notifications = null): array
    {
        if (is_array($notifications) && ! empty($notifications)) {
            return $notifications;
        }

        if (! empty($branch['recent_activity']) && is_array($branch['recent_activity'])) {
            return $branch['recent_activity'];
        }

        // Fallback sample notifications
        return [
            ['type' => 'appointment', 'message' => 'New appointment scheduled for tomorrow', 'time' => '2 hours ago'],
            ['type' => 'staff', 'message' => 'Dr. Smith added to branch staff', 'time' => '1 day ago'],
            ['type' => 'update', 'message' => 'Operating hours updated', 'time' => '3 days ago'],
        ];
    }
}
