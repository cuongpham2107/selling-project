<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\FeeTier;
use Illuminate\Auth\Access\HandlesAuthorization;

class FeeTierPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:FeeTier');
    }

    public function view(AuthUser $authUser, FeeTier $feeTier): bool
    {
        return $authUser->can('View:FeeTier');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:FeeTier');
    }

    public function update(AuthUser $authUser, FeeTier $feeTier): bool
    {
        return $authUser->can('Update:FeeTier');
    }

    public function delete(AuthUser $authUser, FeeTier $feeTier): bool
    {
        return $authUser->can('Delete:FeeTier');
    }

    public function restore(AuthUser $authUser, FeeTier $feeTier): bool
    {
        return $authUser->can('Restore:FeeTier');
    }

    public function forceDelete(AuthUser $authUser, FeeTier $feeTier): bool
    {
        return $authUser->can('ForceDelete:FeeTier');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:FeeTier');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:FeeTier');
    }

    public function replicate(AuthUser $authUser, FeeTier $feeTier): bool
    {
        return $authUser->can('Replicate:FeeTier');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:FeeTier');
    }

}