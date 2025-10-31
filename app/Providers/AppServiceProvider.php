<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use App\Services\DueDateReminderService;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Builder::macro('search', function ($field, $string) {
            return $string ? $this->where($field, 'like', '%'.$string.'%') : $this;
        });

        // Check due date reminders on EVERY request (page refresh/reload)
        Event::listen('Illuminate\Foundation\Http\Events\RequestHandled', function ($event) {
            // Only run for authenticated users
            if (auth()->check()) {
                $reminderService = app(DueDateReminderService::class);
                
                // Run on every request without rate limiting
                $sentCount = $reminderService->checkAllReminders();
                
                // Optional: Log for debugging (remove in production)
                if (app()->environment('local') && $sentCount > 0) {
                    \Log::info("Sent {$sentCount} due date reminders on request");
                }
            }
        });

    }
}