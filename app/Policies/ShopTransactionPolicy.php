<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ShopTransaction;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShopTransactionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ShopTransaction');
    }

    public function view(AuthUser $authUser, ShopTransaction $shopTransaction): bool
    {
        return $authUser->can('View:ShopTransaction');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ShopTransaction');
    }

    public function update(AuthUser $authUser, ShopTransaction $shopTransaction): bool
    {
        return $authUser->can('Update:ShopTransaction');
    }

    public function delete(AuthUser $authUser, ShopTransaction $shopTransaction): bool
    {
        return $authUser->can('Delete:ShopTransaction');
    }

    public function restore(AuthUser $authUser, ShopTransaction $shopTransaction): bool
    {
        return $authUser->can('Restore:ShopTransaction');
    }

    public function forceDelete(AuthUser $authUser, ShopTransaction $shopTransaction): bool
    {
        return $authUser->can('ForceDelete:ShopTransaction');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ShopTransaction');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ShopTransaction');
    }

    public function replicate(AuthUser $authUser, ShopTransaction $shopTransaction): bool
    {
        return $authUser->can('Replicate:ShopTransaction');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ShopTransaction');
    }

}