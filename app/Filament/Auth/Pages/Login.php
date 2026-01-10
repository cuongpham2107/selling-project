<?php
namespace App\Filament\Auth\Pages;

use Filament\Auth\Pages\Login as BaseAuth;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseAuth
{
    protected static string $layout = 'filament.components.layout.simple';
    
}