<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\UserBankAccount;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserBankAccountPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:UserBankAccount');
    }

    public function view(AuthUser $authUser, UserBankAccount $userBankAccount): bool
    {
        return $authUser->can('View:UserBankAccount');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:UserBankAccount');
    }

    public function update(AuthUser $authUser, UserBankAccount $userBankAccount): bool
    {
        return $authUser->can('Update:UserBankAccount');
    }

    public function delete(AuthUser $authUser, UserBankAccount $userBankAccount): bool
    {
        return $authUser->can('Delete:UserBankAccount');
    }

    public function restore(AuthUser $authUser, UserBankAccount $userBankAccount): bool
    {
        return $authUser->can('Restore:UserBankAccount');
    }

    public function forceDelete(AuthUser $authUser, UserBankAccount $userBankAccount): bool
    {
        return $authUser->can('ForceDelete:UserBankAccount');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:UserBankAccount');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:UserBankAccount');
    }

    public function replicate(AuthUser $authUser, UserBankAccount $userBankAccount): bool
    {
        return $authUser->can('Replicate:UserBankAccount');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:UserBankAccount');
    }

}