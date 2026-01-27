<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ShopProduct;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShopProductPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ShopProduct');
    }

    public function view(AuthUser $authUser, ShopProduct $shopProduct): bool
    {
        return $authUser->can('View:ShopProduct');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ShopProduct');
    }

    public function update(AuthUser $authUser, ShopProduct $shopProduct): bool
    {
        return $authUser->can('Update:ShopProduct');
    }

    public function delete(AuthUser $authUser, ShopProduct $shopProduct): bool
    {
        return $authUser->can('Delete:ShopProduct');
    }

    public function restore(AuthUser $authUser, ShopProduct $shopProduct): bool
    {
        return $authUser->can('Restore:ShopProduct');
    }

    public function forceDelete(AuthUser $authUser, ShopProduct $shopProduct): bool
    {
        return $authUser->can('ForceDelete:ShopProduct');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ShopProduct');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ShopProduct');
    }

    public function replicate(AuthUser $authUser, ShopProduct $shopProduct): bool
    {
        return $authUser->can('Replicate:ShopProduct');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ShopProduct');
    }

}