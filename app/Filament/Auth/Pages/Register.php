<?php

namespace App\Filament\Auth\Pages;

use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Auth\Events\Registered as FilamentRegistered;
use Spatie\Permission\Models\Role;

/**
 * Extend Filament's Register page to hook into the registration lifecycle.
 *
 * Note: the vendor page calls `$this->callHook('beforeRegister')` before
 * the user model is created. To assign roles using Spatie\Permission we need
 * the user model instance, so we implement `afterRegister()` which receives
 * the created model context from the page (available as `$this->form->getModel()`
 * after saveRelationships). If you absolutely need to mutate data before
 * creation, use `beforeRegister()` to modify `$this->data`.
 */
class Register extends BaseRegister
{
    /**
     * Called by the parent page after the user has been created and relationships saved.
     */
    protected function afterRegister(): void
    {
        // Prefer assigning roles using an event listener for Filament\Auth\Events\Registered.
        // See App\Listeners\AssignDefaultRole for an example implementation.
    }

    /**
     * Optional: if you really want to modify the raw form data before creation,
     * implement beforeRegister and change `$this->data` or `$this->form` state.
     */
    /*
    protected function beforeRegister(): void
    {
        // Example: add a default value into the input data
        $data = $this->form->getState();
        $data['some_field'] = 'default';
        $this->form->fill($data);
    }
    */
}
