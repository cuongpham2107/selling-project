<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Dispute;
use Illuminate\Auth\Access\HandlesAuthorization;

class DisputePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Dispute');
    }

    public function view(AuthUser $authUser, Dispute $dispute): bool
    {
        return $authUser->can('View:Dispute');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Dispute');
    }

    public function update(AuthUser $authUser, Dispute $dispute): bool
    {
        return $authUser->can('Update:Dispute');
    }

    public function delete(AuthUser $authUser, Dispute $dispute): bool
    {
        return $authUser->can('Delete:Dispute');
    }

    public function restore(AuthUser $authUser, Dispute $dispute): bool
    {
        return $authUser->can('Restore:Dispute');
    }

    public function forceDelete(AuthUser $authUser, Dispute $dispute): bool
    {
        return $authUser->can('ForceDelete:Dispute');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Dispute');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Dispute');
    }

    public function replicate(AuthUser $authUser, Dispute $dispute): bool
    {
        return $authUser->can('Replicate:Dispute');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Dispute');
    }

}