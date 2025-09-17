<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureAbility;
use App\Http\Middleware\EnsureEmailVerifiedForRole;
use App\Http\Middleware\EnsureOrgScope;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'ability' => EnsureAbility::class,
            'abilities' => EnsureAbility::class,
            'verified.role' => EnsureEmailVerifiedForRole::class,
            'org.scope' => EnsureOrgScope::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();




use App\Models\Usuario;

$u = Usuario::where('email','davidclaudio5000@gmail.com')->first();  
$u->tokens()->delete(); 

$token = $u->createToken('postman', [
  'org.members.read',
  'org.members.create',
  'org.members.update',
  'org.members.delete'
])->plainTextToken;

$token;
