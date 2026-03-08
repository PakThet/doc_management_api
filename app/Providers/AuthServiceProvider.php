<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Document;
use App\Policies\DocumentPolicy;
class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Document::class => DocumentPolicy::class,
        \App\Models\Employee::class => \App\Policies\EmployeePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}