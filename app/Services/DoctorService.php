<?php

namespace App\Services;

class DoctorService
{
    /**
     * Returns doctor data. Currently mock/static.
     * Later: replace the body with an HTTP call to the real API,
     * keep the return shape identical so nothing downstream changes.
     */
    public function getAll(): array
    {
        return [
            ['id' => 1, 'name' => null, 'specialty' => null, 'photo_url' => null, 'available' => null],
            ['id' => 2, 'name' => null, 'specialty' => null, 'photo_url' => null, 'available' => null],
            ['id' => 3, 'name' => null, 'specialty' => null, 'photo_url' => null, 'available' => null],
        ];
    }
}
