<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait LogsActivity
{
    /**
     * Atomically record a critical employee action inside the isolated tenant activity log ledger.
     *
     * @param string $action The code name of the audited operation (e.g., 'invoice.created')
     * @param Model|null $model Optional Eloquent model instance bound to the operation context
     * @param string|null $description Custom contextual metadata description of the action
     * @return void
     */
    protected function logAction(string $action, ?Model $model = null, ?string $description = null): void
    {
        // Capture connection fingerprints safely from the active HTTP request context
        $ipAddress = Request::ip();
        $userAgent = Request::header('User-Agent');

        // Execute the atomic persistence layer directly inside the active tenant schema
        ActivityLog::create([
            'user_id' => Auth::id(), // Automatically matches the authenticated employee or null for anonymous
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->getKey() : null,
            'description' => $description,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }
}
