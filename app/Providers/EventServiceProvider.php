<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Observers\RequestJobObserver;

use App\Models\RequestJob;





class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],



    ];





    protected $observers = [

        RequestJob::class => [RequestJobObserver::class],
    ];



        public function boot()
{
    RequestJob::observe(RequestJobObserver::class);
}
    

    public function shouldDiscoverEvents()
    {
        return false;
    }
}




