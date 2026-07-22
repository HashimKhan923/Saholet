<?php

namespace App\Providers;

use App\Models\Bid;
use App\Models\Booking;
use App\Models\CareerApplication;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Contract;
use App\Models\Dispute;
use App\Models\EmergencyRequest;
use App\Models\JobPost;
use App\Models\ProviderProfile;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\WithdrawalRequest;
use App\Observers\BidObserver;
use App\Observers\BookingObserver;
use App\Observers\CatalogObserver;
use App\Observers\JobPostObserver;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Observers\EmergencyRequestObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Category::observe(CatalogObserver::class);
        Service::observe(CatalogObserver::class);
        Booking::observe(BookingObserver::class);
        JobPost::observe(JobPostObserver::class);
        Bid::observe(BidObserver::class);
        EmergencyRequest::observe(EmergencyRequestObserver::class);

        // Use our Tailwind pagination partial.
        Paginator::defaultView('vendor.pagination.tailwind');

        // Centralizes the "must be an approved provider" gate that used to be
        // reimplemented per-controller (JobController, BidController,
        // EmergencyController, PortfolioController, ProviderServiceController).
        Gate::define('actAsApprovedProvider', fn (User $user) => ($user->providerProfile?->isApproved() ?? false)
            ? Response::allow()
            : Response::deny('You must be a verified provider.'));

        // Auth-surface rate limiting — keyed by email+IP so one account under attack
        // can't be used to lock out other users sharing the same IP (offices/NAT).
        RateLimiter::for('login', function (Request $request) {
            $key = mb_strtolower((string) $request->input('email')) . '|' . $request->ip();

            return Limit::perMinute(6)->by($key);
        });

        RateLimiter::for('register', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));

        RateLimiter::for('password-reset', function (Request $request) {
            $key = mb_strtolower((string) $request->input('email')) . '|' . $request->ip();

            return Limit::perMinute(5)->by($key);
        });

        // Sidebar badge counts for the admin & provider portal shells.
        View::composer('layouts.admin', function ($view) {
            $pendingProviders = ProviderProfile::where('status', ProviderProfile::STATUS_PENDING)->count();
            $openDisputes = Dispute::where('status', Dispute::STATUS_OPEN)->count();
            $pendingContracts = Contract::where('status', Contract::STATUS_SUBMITTED)->count();
            $newApplications = CareerApplication::where('status', CareerApplication::STATUS_SUBMITTED)->count();
            $pendingSubscriptions = Subscription::where('status', Subscription::STATUS_PENDING_ASSIGNMENT)->count();
            $pendingWithdrawals = WithdrawalRequest::where('status', WithdrawalRequest::STATUS_PENDING)->count();
            $unreadContactMessages = ContactMessage::whereNull('read_at')->count();

            $view->with('sidebarPendingProviders', $pendingProviders);
            $view->with('sidebarOpenDisputes', $openDisputes);
            $view->with('sidebarPendingContracts', $pendingContracts);
            $view->with('sidebarNewApplications', $newApplications);
            $view->with('sidebarPendingSubscriptions', $pendingSubscriptions);
            $view->with('sidebarPendingWithdrawals', $pendingWithdrawals);
            $view->with('sidebarUnreadContactMessages', $unreadContactMessages);
            $view->with('sidebarTotalRequests', $pendingProviders + $openDisputes + $pendingContracts + $newApplications + $pendingSubscriptions + $pendingWithdrawals);
        });

        View::composer('layouts.provider', function ($view) {
            $pendingBookings = 0;
            $openEmergencies = 0;
            $availableJobs = 0;
            $myServiceIds = collect();

            $profile = Auth::user()?->providerProfile;

            if ($profile && $profile->isApproved()) {
                $pendingBookings = Booking::where('provider_profile_id', $profile->id)
                    ->where('status', Booking::STATUS_PENDING)
                    ->count();

                $myServiceIds = $profile->providerServices()->where('is_active', true)->pluck('service_id')->values();

                $availableJobs = JobPost::where('status', JobPost::STATUS_OPEN)
                    ->whereIn('service_id', $myServiceIds)
                    ->count();

                $openEmergencies = EmergencyRequest::where('status', EmergencyRequest::STATUS_OPEN)
                    ->whereIn('service_id', $myServiceIds)
                    ->whereRaw('LOWER(city) = ?', [mb_strtolower(trim($profile->city ?? ''))])
                    ->count();
            }

            $view->with('sidebarPendingBookings', $pendingBookings);
            $view->with('sidebarOpenEmergencies', $openEmergencies);
            $view->with('sidebarAvailableJobs', $availableJobs);
            $view->with('sidebarMyServiceIds', $myServiceIds);
            $view->with('sidebarProviderProfileId', $profile?->id);
        });

        // Saved-address quick-pick, wherever <x-address-input> renders (booking/job/contract/emergency forms).
        View::composer('components.address-input', function ($view) {
            $user = Auth::user();

            $view->with('savedAddresses', $user && $user->isConsumer() ? $user->addresses : collect());
        });
    }
}