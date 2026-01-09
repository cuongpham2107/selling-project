<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PointTier;
use Illuminate\Auth\Access\HandlesAuthorization;

class PointTierPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PointTier');
    }

    public function view(AuthUser $authUser, PointTier $pointTier): bool
    {
        return $authUser->can('View:PointTier');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PointTier');
    }

    public function update(AuthUser $authUser, PointTier $pointTier): bool
    {
        return $authUser->can('Update:PointTier');
    }

    public function delete(AuthUser $authUser, PointTier $pointTier): bool
    {
        return $authUser->can('Delete:PointTier');
    }

    public function restore(AuthUser $authUser, PointTier $pointTier): bool
    {
        return $authUser->can('Restore:PointTier');
    }

    public function forceDelete(AuthUser $authUser, PointTier $pointTier): bool
    {
        return $authUser->can('ForceDelete:PointTier');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PointTier');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PointTier');
    }

    public function replicate(AuthUser $authUser, PointTier $pointTier): bool
    {
        return $authUser->can('Replicate:PointTier');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PointTier');
    }

}