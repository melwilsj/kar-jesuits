protected $middlewareAliases = [
    // ... other middleware aliases
    'role' => \App\Http\Middleware\CheckRole::class,
];