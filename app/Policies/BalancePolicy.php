<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Balance;
use Illuminate\Auth\Access\HandlesAuthorization;

class BalancePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Balance');
    }

    public function view(AuthUser $authUser, Balance $balance): bool
    {
        return $authUser->can('View:Balance');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Balance');
    }

    public function update(AuthUser $authUser, Balance $balance): bool
    {
        return $authUser->can('Update:Balance');
    }

    public function delete(AuthUser $authUser, Balance $balance): bool
    {
        return $authUser->can('Delete:Balance');
    }

    public function restore(AuthUser $authUser, Balance $balance): bool
    {
        return $authUser->can('Restore:Balance');
    }

    public function forceDelete(AuthUser $authUser, Balance $balance): bool
    {
        return $authUser->can('ForceDelete:Balance');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Balance');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Balance');
    }

    public function replicate(AuthUser $authUser, Balance $balance): bool
    {
        return $authUser->can('Replicate:Balance');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Balance');
    }

}