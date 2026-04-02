<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Ticket;
use App\Policies\OrderPolicy;
use App\Policies\TicketPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Order::class => OrderPolicy::class,
        Ticket::class => TicketPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
