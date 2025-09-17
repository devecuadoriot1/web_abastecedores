<?php

use Illuminate\Support\ServiceProvider;
use App\Models\Usuario;
use App\Policies\UsuarioPolicy;


class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Usuario::class => UsuarioPolicy::class,
    ];

}
