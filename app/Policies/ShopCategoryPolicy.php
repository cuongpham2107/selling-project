<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ShopCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShopCategoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ShopCategory');
    }

    public function view(AuthUser $authUser, ShopCategory $shopCategory): bool
    {
        return $authUser->can('View:ShopCategory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ShopCategory');
    }

    public function update(AuthUser $authUser, ShopCategory $shopCategory): bool
    {
        return $authUser->can('Update:ShopCategory');
    }

    public function delete(AuthUser $authUser, ShopCategory $shopCategory): bool
    {
        return $authUser->can('Delete:ShopCategory');
    }

    public function restore(AuthUser $authUser, ShopCategory $shopCategory): bool
    {
        return $authUser->can('Restore:ShopCategory');
    }

    public function forceDelete(AuthUser $authUser, ShopCategory $shopCategory): bool
    {
        return $authUser->can('ForceDelete:ShopCategory');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ShopCategory');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ShopCategory');
    }

    public function replicate(AuthUser $authUser, ShopCategory $shopCategory): bool
    {
        return $authUser->can('Replicate:ShopCategory');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ShopCategory');
    }

}