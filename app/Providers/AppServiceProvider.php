<?php

namespace App\Providers;

use Booking\Application\Commands\CancelBooking\CancelBookingCommand;
use Booking\Application\Commands\CancelBooking\CancelBookingHandler;
use Booking\Application\Commands\ConfirmBooking\ConfirmBookingCommand;
use Booking\Application\Commands\ConfirmBooking\ConfirmBookingHandler;
use Booking\Application\Commands\CreateBooking\CreateBookingCommand;
use Booking\Application\Commands\CreateBooking\CreateBookingHandler;
use Booking\Application\Commands\RescheduleBooking\RescheduleBookingCommand;
use Booking\Application\Commands\RescheduleBooking\RescheduleBookingHandler;
use Booking\Application\Queries\GetAvailableSlots\GetAvailableSlotsHandler;
use Booking\Application\Queries\GetAvailableSlots\GetAvailableSlotsQuery;
use Booking\Application\Queries\GetBookingDetails\GetBookingDetailsHandler;
use Booking\Application\Queries\GetBookingDetails\GetBookingDetailsQuery;
use Booking\Application\Queries\GetResourceSchedule\GetResourceScheduleHandler;
use Booking\Application\Queries\GetResourceSchedule\GetResourceScheduleQuery;
use Booking\Application\Queries\ListBookings\ListBookingsHandler;
use Booking\Application\Queries\ListBookings\ListBookingsQuery;
use Booking\Domain\Repositories\BookingRepositoryInterface;
use Booking\Domain\Repositories\ClientRepositoryInterface;
use Booking\Domain\Repositories\ResourceRepositoryInterface;
use Booking\Domain\Services\AvailabilityService;
use Booking\Domain\Services\ConflictDetectionService;
use Booking\Infrastructure\Messaging\CommandBus;
use Booking\Infrastructure\Messaging\QueryBus;
use Booking\Infrastructure\Persistence\Eloquent\Repositories\EloquentBookingRepository;
use Booking\Infrastructure\Persistence\Eloquent\Repositories\EloquentClientRepository;
use Booking\Infrastructure\Persistence\Eloquent\Repositories\EloquentResourceRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Domain Services
        $this->app->singleton(ConflictDetectionService::class);
        $this->app->singleton(AvailabilityService::class);

        // Repository Bindings (DIP: depend on abstractions)
        $this->app->bind(BookingRepositoryInterface::class, EloquentBookingRepository::class);
        $this->app->bind(ResourceRepositoryInterface::class, EloquentResourceRepository::class);
        $this->app->bind(ClientRepositoryInterface::class, EloquentClientRepository::class);

        // Command Bus (singleton — handlers are resolved per dispatch)
        $this->app->singleton(CommandBus::class, function ($app) {
            $bus = new CommandBus($app);
            $bus->register(CreateBookingCommand::class, CreateBookingHandler::class);
            $bus->register(CancelBookingCommand::class, CancelBookingHandler::class);
            $bus->register(RescheduleBookingCommand::class, RescheduleBookingHandler::class);
            $bus->register(ConfirmBookingCommand::class, ConfirmBookingHandler::class);

            return $bus;
        });

        // Query Bus (singleton — handlers are resolved per dispatch)
        $this->app->singleton(QueryBus::class, function ($app) {
            $bus = new QueryBus($app);
            $bus->register(GetAvailableSlotsQuery::class, GetAvailableSlotsHandler::class);
            $bus->register(GetBookingDetailsQuery::class, GetBookingDetailsHandler::class);
            $bus->register(ListBookingsQuery::class, ListBookingsHandler::class);
            $bus->register(GetResourceScheduleQuery::class, GetResourceScheduleHandler::class);

            return $bus;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
