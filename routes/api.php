<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\BookingRoomController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DisputeController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProviderDirectoryController;
use App\Http\Controllers\Api\ServiceAreaController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\SubscriptionPlanController;
use App\Http\Controllers\Api\Consumer\AddressController as ConsumerAddressController;
use App\Http\Controllers\Api\Consumer\BookingController as ConsumerBookingController;
use App\Http\Controllers\Api\Consumer\ContractController as ConsumerContractController;
use App\Http\Controllers\Api\Consumer\DashboardController as ConsumerDashboardController;
use App\Http\Controllers\Api\Consumer\EmergencyController as ConsumerEmergencyController;
use App\Http\Controllers\Api\Consumer\JobController as ConsumerJobController;
use App\Http\Controllers\Api\Consumer\PaymentController as ConsumerPaymentController;
use App\Http\Controllers\Api\Consumer\ReviewController as ConsumerReviewController;
use App\Http\Controllers\Api\Consumer\SubscriptionController as ConsumerSubscriptionController;
use App\Http\Controllers\Api\Provider\BidController as ProviderBidController;
use App\Http\Controllers\Api\Provider\BookingController as ProviderBookingController;
use App\Http\Controllers\Api\Provider\DashboardController as ProviderDashboardController;
use App\Http\Controllers\Api\Provider\EmergencyController as ProviderEmergencyController;
use App\Http\Controllers\Api\Provider\JobController as ProviderJobController;
use App\Http\Controllers\Api\Provider\OnboardingController as ProviderOnboardingController;
use App\Http\Controllers\Api\Provider\PayoutMethodController as ProviderPayoutMethodController;
use App\Http\Controllers\Api\Provider\PortfolioController as ProviderPortfolioController;
use App\Http\Controllers\Api\Provider\ProviderServiceController;
use App\Http\Controllers\Api\Provider\WalletController as ProviderWalletController;
use App\Http\Controllers\Api\Provider\WithdrawalController as ProviderWithdrawalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Sahoulat mobile app (customers + professionals)
|--------------------------------------------------------------------------
| All routes are prefixed with /api. Authenticated routes use Sanctum bearer
| tokens (Authorization: Bearer <token>). Route names are prefixed "api."
| to avoid clashing with the web app's route names of the same shape.
*/

// ─── Public (no auth) ────────────────────────────────────────────────
Route::post('register', [AuthController::class, 'register'])->name('api.register');
Route::post('login', [AuthController::class, 'login'])->name('api.login');

Route::get('categories', [CategoryController::class, 'index'])->name('api.categories.index');
Route::get('services', [ServiceController::class, 'index'])->name('api.services.index');
Route::get('services/{service:slug}', [ServiceController::class, 'show'])->name('api.services.show');

Route::get('providers', [ProviderDirectoryController::class, 'index'])->name('api.providers.index');
Route::get('providers/{provider}', [ProviderDirectoryController::class, 'show'])->name('api.providers.show');
Route::get('providers/{provider}/services/{service}/availability', [AvailabilityController::class, 'show'])->name('api.providers.availability');

Route::get('subscription-plans', [SubscriptionPlanController::class, 'index'])->name('api.subscription-plans.index');
Route::get('cities', [ServiceAreaController::class, 'index'])->name('api.cities.index');

// ─── Authenticated (all roles) ───────────────────────────────────────
Route::middleware(['auth:sanctum', 'api.not.suspended'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::post('logout-all', [AuthController::class, 'logoutAll'])->name('api.logout-all');
    Route::get('me', [AuthController::class, 'me'])->name('api.me');

    Route::put('profile', [ProfileController::class, 'update'])->name('api.profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('api.profile.password');

    Route::get('notifications', [NotificationController::class, 'index'])->name('api.notifications.index');
    Route::post('notifications/read-all', [NotificationController::class, 'readAll'])->name('api.notifications.read-all');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'read'])->name('api.notifications.read');

    Route::get('bookings/{booking}/room', [BookingRoomController::class, 'show'])->name('api.bookings.room');
    Route::post('bookings/{booking}/messages', [BookingRoomController::class, 'sendMessage'])->name('api.bookings.messages.store');
    Route::post('bookings/{booking}/tracking', [BookingRoomController::class, 'shareLocation'])->name('api.bookings.tracking.store');

    Route::post('bookings/{booking}/dispute', [DisputeController::class, 'store'])->name('api.bookings.dispute.store');
    Route::get('disputes/{dispute}', [DisputeController::class, 'show'])->name('api.disputes.show');

    // ─── Consumer ────────────────────────────────────────────────
    Route::middleware('api.role:consumer')->prefix('consumer')->name('api.consumer.')->group(function () {
        Route::get('dashboard', [ConsumerDashboardController::class, 'index'])->name('dashboard');

        Route::get('addresses', [ConsumerAddressController::class, 'index'])->name('addresses.index');
        Route::post('addresses', [ConsumerAddressController::class, 'store'])->name('addresses.store');
        Route::put('addresses/{address}', [ConsumerAddressController::class, 'update'])->name('addresses.update');
        Route::delete('addresses/{address}', [ConsumerAddressController::class, 'destroy'])->name('addresses.destroy');

        Route::get('bookings', [ConsumerBookingController::class, 'index'])->name('bookings.index');
        Route::post('bookings', [ConsumerBookingController::class, 'store'])->name('bookings.store');
        Route::get('bookings/{booking}', [ConsumerBookingController::class, 'show'])->name('bookings.show');
        Route::post('bookings/{booking}/cancel', [ConsumerBookingController::class, 'cancel'])->name('bookings.cancel');

        Route::get('bookings/{booking}/payment-options', [ConsumerPaymentController::class, 'options'])->name('bookings.payment-options');
        Route::post('bookings/{booking}/pay', [ConsumerPaymentController::class, 'store'])->name('bookings.pay');
        Route::post('bookings/{booking}/release', [ConsumerPaymentController::class, 'release'])->name('bookings.release');

        Route::post('bookings/{booking}/review', [ConsumerReviewController::class, 'store'])->name('bookings.review');

        Route::get('jobs', [ConsumerJobController::class, 'index'])->name('jobs.index');
        Route::post('jobs', [ConsumerJobController::class, 'store'])->name('jobs.store');
        Route::get('jobs/{jobPost}', [ConsumerJobController::class, 'show'])->name('jobs.show');
        Route::post('jobs/{jobPost}/cancel', [ConsumerJobController::class, 'cancel'])->name('jobs.cancel');
        Route::post('jobs/{jobPost}/bids/{bid}/accept', [ConsumerJobController::class, 'acceptBid'])->name('jobs.bids.accept');

        Route::get('contracts', [ConsumerContractController::class, 'index'])->name('contracts.index');
        Route::post('contracts', [ConsumerContractController::class, 'store'])->name('contracts.store');
        Route::get('contracts/{contract}', [ConsumerContractController::class, 'show'])->name('contracts.show');
        Route::post('contracts/{contract}/accept', [ConsumerContractController::class, 'accept'])->name('contracts.accept');
        Route::post('contracts/{contract}/reject', [ConsumerContractController::class, 'reject'])->name('contracts.reject');
        Route::post('contracts/{contract}/cancel', [ConsumerContractController::class, 'cancel'])->name('contracts.cancel');
        Route::get('contracts/{contract}/milestones/{milestone}/payment-options', [ConsumerContractController::class, 'milestoneOptions'])->name('contracts.milestones.payment-options');
        Route::post('contracts/{contract}/milestones/{milestone}/pay', [ConsumerContractController::class, 'payMilestone'])->name('contracts.milestones.pay');

        Route::get('emergencies', [ConsumerEmergencyController::class, 'index'])->name('emergencies.index');
        Route::post('emergencies', [ConsumerEmergencyController::class, 'store'])->name('emergencies.store');
        Route::get('emergencies/{emergencyRequest}', [ConsumerEmergencyController::class, 'show'])->name('emergencies.show');
        Route::post('emergencies/{emergencyRequest}/cancel', [ConsumerEmergencyController::class, 'cancel'])->name('emergencies.cancel');

        Route::get('subscriptions', [ConsumerSubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::post('subscription-plans/{plan:slug}/subscribe', [ConsumerSubscriptionController::class, 'store'])->name('subscriptions.store');
        Route::get('subscriptions/{subscription}', [ConsumerSubscriptionController::class, 'show'])->name('subscriptions.show');
        Route::post('subscriptions/{subscription}/cancel', [ConsumerSubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
    });

    // ─── Provider ────────────────────────────────────────────────
    Route::middleware('api.role:provider')->prefix('provider')->name('api.provider.')->group(function () {
        Route::get('dashboard', [ProviderDashboardController::class, 'index'])->name('dashboard');

        Route::get('onboarding', [ProviderOnboardingController::class, 'show'])->name('onboarding.show');
        Route::put('onboarding', [ProviderOnboardingController::class, 'update'])->name('onboarding.update');
        Route::post('onboarding/submit', [ProviderOnboardingController::class, 'submit'])->name('onboarding.submit');
        Route::post('onboarding/documents', [ProviderOnboardingController::class, 'storeDocument'])->name('onboarding.documents.store');
        Route::get('onboarding/documents/{document}', [ProviderOnboardingController::class, 'showDocument'])->name('onboarding.documents.show');
        Route::delete('onboarding/documents/{document}', [ProviderOnboardingController::class, 'destroyDocument'])->name('onboarding.documents.destroy');

        Route::get('services', [ProviderServiceController::class, 'index'])->name('services.index');
        Route::post('services', [ProviderServiceController::class, 'store'])->name('services.store');
        Route::put('services/{providerService}', [ProviderServiceController::class, 'update'])->name('services.update');
        Route::delete('services/{providerService}', [ProviderServiceController::class, 'destroy'])->name('services.destroy');

        Route::get('bookings', [ProviderBookingController::class, 'index'])->name('bookings.index');
        Route::get('bookings/{booking}', [ProviderBookingController::class, 'show'])->name('bookings.show');
        Route::post('bookings/{booking}/status', [ProviderBookingController::class, 'updateStatus'])->name('bookings.status');

        Route::get('jobs', [ProviderJobController::class, 'index'])->name('jobs.index');
        Route::get('jobs/{jobPost}', [ProviderJobController::class, 'show'])->name('jobs.show');
        Route::post('jobs/{jobPost}/bids', [ProviderBidController::class, 'store'])->name('jobs.bids.store');

        Route::get('bids', [ProviderBidController::class, 'index'])->name('bids.index');
        Route::put('bids/{bid}', [ProviderBidController::class, 'update'])->name('bids.update');
        Route::delete('bids/{bid}', [ProviderBidController::class, 'destroy'])->name('bids.destroy');

        Route::get('emergencies', [ProviderEmergencyController::class, 'index'])->name('emergencies.index');
        Route::post('emergencies/{emergencyRequest}/accept', [ProviderEmergencyController::class, 'accept'])->name('emergencies.accept');

        Route::get('wallet', [ProviderWalletController::class, 'index'])->name('wallet.index');
        Route::post('payout-method', [ProviderPayoutMethodController::class, 'update'])->name('payout-method.update');
        Route::post('withdrawals', [ProviderWithdrawalController::class, 'store'])->name('withdrawals.store');

        Route::get('portfolio', [ProviderPortfolioController::class, 'index'])->name('portfolio.index');
        Route::post('portfolio', [ProviderPortfolioController::class, 'store'])->name('portfolio.store');
        Route::delete('portfolio/{photo}', [ProviderPortfolioController::class, 'destroy'])->name('portfolio.destroy');
    });
});
