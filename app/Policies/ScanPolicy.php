<?php

namespace App\Policies;

use App\Models\Scan;
use App\Models\User;

class ScanPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view scans');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Scan $scan): bool
    {
        // Users can view their own scans, or if they have view scans permission
        return $user->can('view scans') || $scan->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create scans');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Scan $scan): bool
    {
        // Users can edit their own scans if not submitted, or if they have edit permission
        if ($user->can('edit scans')) {
            return true;
        }

        return $scan->user_id === $user->id && ! $scan->submitted;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Scan $scan): bool
    {
        // Users can delete their own unsubmitted scans, or if they have delete permission
        if ($user->can('delete scans')) {
            return true;
        }

        return $scan->user_id === $user->id && ! $scan->submitted;
    }

    /**
     * Determine whether the user can sync scans.
     */
    public function sync(User $user): bool
    {
        return $user->can('sync scans');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Scan $scan): bool
    {
        return $user->can('delete scans');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Scan $scan): bool
    {
        return $user->can('delete scans');
    }
}
