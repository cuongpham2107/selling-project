<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\BalanceTransaction;
use Illuminate\Auth\Access\HandlesAuthorization;

class BalanceTransactionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BalanceTransaction');
    }

    public function view(AuthUser $authUser, BalanceTransaction $balanceTransaction): bool
    {
        return $authUser->can('View:BalanceTransaction');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BalanceTransaction');
    }

    public function update(AuthUser $authUser, BalanceTransaction $balanceTransaction): bool
    {
        return $authUser->can('Update:BalanceTransaction');
    }

    public function delete(AuthUser $authUser, BalanceTransaction $balanceTransaction): bool
    {
        return $authUser->can('Delete:BalanceTransaction');
    }

    public function restore(AuthUser $authUser, BalanceTransaction $balanceTransaction): bool
    {
        return $authUser->can('Restore:BalanceTransaction');
    }

    public function forceDelete(AuthUser $authUser, BalanceTransaction $balanceTransaction): bool
    {
        return $authUser->can('ForceDelete:BalanceTransaction');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BalanceTransaction');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BalanceTransaction');
    }

    public function replicate(AuthUser $authUser, BalanceTransaction $balanceTransaction): bool
    {
        return $authUser->can('Replicate:BalanceTransaction');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BalanceTransaction');
    }

}