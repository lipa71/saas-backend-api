<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    /**
     * Determine whether the user can view any models (List view).
     */
    public function viewAny(User $user): bool
    {
        // All authenticated corporate roles are allowed to browse the invoice registry
        return $user->hasRole(['admin', 'manager', 'accountant', 'viewer']);
    }

    /**
     * Determine whether the user can view the model details.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        return $user->hasRole(['admin', 'manager', 'accountant', 'viewer']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Viewers are strictly blocked from creating new corporate records
        return $user->hasRole(['admin', 'manager', 'accountant']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        return $user->hasRole(['admin', 'manager', 'accountant']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        // Only high-level management and administrators hold destructive authority
        return $user->hasRole(['admin', 'manager']);
    }
}
