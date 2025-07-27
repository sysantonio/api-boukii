<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\AppBaseController;

class SystemValidationController extends AppBaseController
{
    public function validateSystem()
    {
        return $this->sendResponse(['system' => 'ok'], 'System validation successful');
    }
}
