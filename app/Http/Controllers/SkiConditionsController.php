<?php

namespace App\Http\Controllers;

class SkiConditionsController extends AppBaseController
{
    public function current()
    {
        $data = [
            'condition' => 'powder',
            'snow_depth_cm' => 120,
            'last_updated' => now()->toIso8601String(),
        ];

        return $this->sendResponse($data, 'Ski conditions retrieved');
    }
}
