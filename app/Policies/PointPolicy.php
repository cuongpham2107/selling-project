<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Point;
use Illuminate\Auth\Access\HandlesAuthorization;

class PointPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Point');
    }

    public function view(AuthUser $authUser, Point $point): bool
    {
        return $authUser->can('View:Point');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Point');
    }

    public function update(AuthUser $authUser, Point $point): bool
    {
        return $authUser->can('Update:Point');
    }

    public function delete(AuthUser $authUser, Point $point): bool
    {
        return $authUser->can('Delete:Point');
    }

    public function restore(AuthUser $authUser, Point $point): bool
    {
        return $authUser->can('Restore:Point');
    }

    public function forceDelete(AuthUser $authUser, Point $point): bool
    {
        return $authUser->can('ForceDelete:Point');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Point');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Point');
    }

    public function replicate(AuthUser $authUser, Point $point): bool
    {
        return $authUser->can('Replicate:Point');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Point');
    }

}