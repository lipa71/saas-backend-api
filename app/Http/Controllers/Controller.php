<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    // Re-enabling the standard authorization capabilities for clean DRY architecture inside controllers
    use AuthorizesRequests;
}
