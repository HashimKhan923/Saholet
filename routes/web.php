<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\CareerApplicationController as AdminCareerApplicationController;
use App\Http\Controllers\Admin\CareerCategoryController as AdminCareerCategoryController;
use App\Http\Controllers\Admin\CareerListingController as AdminCareerListingController;
use App\Http\Controllers\Admin\CorporateAccountController as AdminCorporateAccountController;
use App\Http\Controllers\Admin\PaymentVerificationController as AdminPaymentVerificationController;
use App\Http\Controllers\Admin\ProviderSettlementController as AdminProviderSettlementController;
use App\Http\Controllers\Admin\RequestsInboxController as AdminRequestsInboxController;
use App\Http\Controllers\Admin\WithdrawalController as AdminWithdrawalController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\SubscriptionPlanController as AdminSubscriptionPlanController;
use App\Http\Controllers\Admin\TalentSearchController as AdminTalentSearchController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\EmergencyController as AdminEmergencyController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Admin\ContractController as AdminContractController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DisputeController as AdminDisputeController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\FraudController;
use App\Http\Controllers\Admin\ProviderController as AdminProviderController;
use App\Http\Controllers\Admin\ServiceAreaController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BookingRoomController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\CategoryController as PublicCategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CareerResumeController;
use App\Http\Controllers\Consumer\AddressController as ConsumerAddressController;
use App\Http\Controllers\Consumer\BookingController as ConsumerBookingController;
use App\Http\Controllers\Consumer\ContractController as ConsumerContractController;
use App\Http\Controllers\Consumer\CorporateAccountController as ConsumerCorporateAccountController;
use App\Http\Controllers\Consumer\ReferralController as ConsumerReferralController;
use App\Http\Controllers\Consumer\DashboardController as ConsumerDashboardController;
use App\Http\Controllers\Consumer\EmergencyController as ConsumerEmergencyController;
use App\Http\Controllers\Consumer\JobController as ConsumerJobController;
use App\Http\Controllers\Consumer\CompletionPaymentController as ConsumerCompletionPaymentController;
use App\Http\Controllers\Consumer\PaymentController as ConsumerPaymentController;
use App\Http\Controllers\Consumer\ReviewController as ConsumerReviewController;
use App\Http\Controllers\Consumer\SubscriptionController as ConsumerSubscriptionController;
use App\Http\Controllers\DisputeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\JobSeeker\CareerApplicationController as JobSeekerCareerApplicationController;
use App\Http\Controllers\JobSeeker\DashboardController as JobSeekerDashboardController;
use App\Http\Controllers\JobSeeker\ProfileController as JobSeekerProfileController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentReturnController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\Provider\BidController as ProviderBidController;
use App\Http\Controllers\Provider\BookingController as ProviderBookingController;
use App\Http\Controllers\Provider\DashboardController as ProviderDashboardController;
use App\Http\Controllers\Provider\JobController as ProviderJobController;
use App\Http\Controllers\Provider\OnboardingController;
use App\Http\Controllers\Provider\PortfolioController as ProviderPortfolioController;
use App\Http\Controllers\Provider\ProviderServiceController;
use App\Http\Controllers\Provider\PayoutMethodController as ProviderPayoutMethodController;
use App\Http\Controllers\Provider\WalletController as ProviderWalletController;
use App\Http\Controllers\Provider\SettlementController as ProviderSettlementController;
use App\Http\Controllers\Provider\WithdrawalController as ProviderWithdrawalController;
use App\Http\Controllers\ProviderDirectoryController;
use App\Http\Controllers\ProviderDocumentController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\PwaController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SubscriptionPlanController;
use Illuminate\Support\Facades\Route;

// ─── Public ──────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
// Sandbox homepage — edit resources/views/landing-dev.blade.php freely without touching the live "/".
Route::get('/dev', [HomeController::class, 'devIndex'])->name('dev.home');

Route::get('locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');
Route::get('manifest.webmanifest', [PwaController::class, 'manifest'])->name('pwa.manifest');
Route::get('robots.txt', [PublicController::class, 'robots'])->name('robots');
Route::get('sitemap.xml', [PublicController::class, 'sitemap'])->name('sitemap');

// Public service catalog
Route::get('services', [ServiceController::class, 'index'])->name('services.index');
Route::get('services/{service:slug}', [ServiceController::class, 'show'])->name('services.show');
Route::get('categories/{category:slug}', [PublicCategoryController::class, 'show'])->name('categories.show');

// Public provider directory (Step 5)
Route::get('providers', [ProviderDirectoryController::class, 'index'])->name('providers.index');
Route::get('providers/{provider}', [ProviderDirectoryController::class, 'show'])->name('providers.show');

// Public careers (recruitment) board
Route::get('careers', [CareerController::class, 'index'])->name('careers.index');
Route::get('careers/{listing:slug}', [CareerController::class, 'show'])->name('careers.show');

// Public maintenance/AMC subscription plans
Route::get('plans', [SubscriptionPlanController::class, 'index'])->name('subscription-plans.index');

// Legal pages
Route::view('privacy-policy', 'legal.privacy')->name('legal.privacy');
Route::view('terms-and-conditions', 'legal.terms')->name('legal.terms');

// Contact us
Route::get('contact', [ContactController::class, 'create'])->name('contact');
Route::post('contact', [ContactController::class, 'store'])->name('contact.store')->middleware('throttle:6,1');

// Redirect-gateway return/webhook callback (JazzCash, EasyPaisa) — off-site,
// signature-verified inside the controller, not session/CSRF authenticated.
Route::match(['get', 'post'], 'payments/{gateway}/return', [PaymentReturnController::class, 'handle'])->name('payments.return');

// ─── Guest (auth) ────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store'])->middleware('throttle:register');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');

    // Password reset (Step 6)
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email')->middleware('throttle:password-reset');
    Route::get('reset-password', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store')->middleware('throttle:password-reset');
});

// ─── Authenticated (all roles) ──────────────────────────────────────
Route::middleware(['auth', 'not.suspended'])->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Profile & account settings (Step 6)
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Notifications (all roles)
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');

    // Web Push subscriptions
    Route::post('push-subscriptions', [PushSubscriptionController::class, 'store'])->name('push-subscriptions.store');
    Route::delete('push-subscriptions', [PushSubscriptionController::class, 'destroy'])->name('push-subscriptions.destroy');

    // Private KYC document streaming
    Route::get('provider-documents/{document}', [ProviderDocumentController::class, 'show'])
        ->name('provider-documents.show');

    // Private resume streaming
    Route::get('resumes/profile/{jobSeekerProfile}', [CareerResumeController::class, 'showProfile'])
        ->name('job-seeker.resume.show');
    Route::get('resumes/application/{careerApplication}', [CareerResumeController::class, 'showApplication'])
        ->name('career-applications.resume.show');

    // Booking room — chat + tracking
    Route::get('bookings/{booking}/room', [BookingRoomController::class, 'show'])->name('bookings.room');
    Route::post('bookings/{booking}/messages', [BookingRoomController::class, 'sendMessage'])->name('bookings.messages.store');
    Route::post('bookings/{booking}/tracking', [BookingRoomController::class, 'shareLocation'])->name('bookings.tracking.store');

    // Disputes
    Route::get('bookings/{booking}/dispute', [DisputeController::class, 'create'])->name('bookings.dispute.create');
    Route::post('bookings/{booking}/dispute', [DisputeController::class, 'store'])->name('bookings.dispute.store');
    Route::get('disputes/{dispute}', [DisputeController::class, 'show'])->name('disputes.show');

    // Invoice / receipt PDFs
    Route::get('bookings/{booking}/receipt', [InvoiceController::class, 'bookingReceipt'])->name('bookings.receipt');
    Route::get('contracts/{contract}/invoice', [InvoiceController::class, 'contractReceipt'])->name('contracts.invoice');

    // ─── Consumer ────────────────────────────────────────────────
    Route::middleware('role:consumer')->group(function () {
        Route::get('dashboard', [ConsumerDashboardController::class, 'index'])->name('consumer.dashboard');

        // Saved addresses
        Route::get('addresses', [ConsumerAddressController::class, 'index'])->name('consumer.addresses.index');
        Route::post('addresses', [ConsumerAddressController::class, 'store'])->name('consumer.addresses.store');
        Route::put('addresses/{address}', [ConsumerAddressController::class, 'update'])->name('consumer.addresses.update');
        Route::delete('addresses/{address}', [ConsumerAddressController::class, 'destroy'])->name('consumer.addresses.destroy');

        // Referral program
        Route::get('referrals', [ConsumerReferralController::class, 'index'])->name('consumer.referrals.index');

        // Subscription / AMC plans
        Route::get('subscriptions', [ConsumerSubscriptionController::class, 'index'])->name('consumer.subscriptions.index');
        Route::get('subscriptions/{plan:slug}/subscribe', [ConsumerSubscriptionController::class, 'create'])->name('consumer.subscriptions.create');
        Route::post('subscriptions/{plan:slug}/subscribe', [ConsumerSubscriptionController::class, 'store'])->name('consumer.subscriptions.store');
        Route::get('subscriptions/{subscription}', [ConsumerSubscriptionController::class, 'show'])->name('consumer.subscriptions.show');
        Route::post('subscriptions/{subscription}/cancel', [ConsumerSubscriptionController::class, 'cancel'])->name('consumer.subscriptions.cancel');

        // Corporate / B2B accounts
        Route::get('company', [ConsumerCorporateAccountController::class, 'create'])->name('consumer.corporate.create');
        Route::post('company', [ConsumerCorporateAccountController::class, 'store'])->name('consumer.corporate.store');
        Route::get('company/dashboard', [ConsumerCorporateAccountController::class, 'show'])->name('consumer.corporate.show');
        Route::post('company/members', [ConsumerCorporateAccountController::class, 'inviteMember'])->name('consumer.corporate.members.invite');
        Route::delete('company/members/{member}', [ConsumerCorporateAccountController::class, 'removeMember'])->name('consumer.corporate.members.remove');

        // Consumer bookings (Flow A — Direct)
        Route::get('bookings', [ConsumerBookingController::class, 'index'])->name('consumer.bookings.index');
        Route::get('bookings/create/{provider}/{service:slug}', [ConsumerBookingController::class, 'create'])->name('consumer.bookings.create');
        Route::post('bookings/create/{provider}/{service:slug}', [ConsumerBookingController::class, 'store'])->name('consumer.bookings.store');
        Route::get('bookings/{booking}', [ConsumerBookingController::class, 'show'])->name('consumer.bookings.show');
        Route::post('bookings/{booking}/cancel', [ConsumerBookingController::class, 'cancel'])->name('consumer.bookings.cancel');

        // Consumer payments (escrow)
        Route::get('bookings/{booking}/pay', [ConsumerPaymentController::class, 'create'])->name('consumer.payments.create');
        Route::post('bookings/{booking}/pay', [ConsumerPaymentController::class, 'store'])->name('consumer.payments.store');
        Route::post('bookings/{booking}/release', [ConsumerPaymentController::class, 'release'])->name('consumer.payments.release');

        // Cash / bank-transfer settlement, shown only once the job is complete.
        Route::get('bookings/{booking}/pay-complete', [ConsumerCompletionPaymentController::class, 'create'])->name('consumer.payments.complete.create');
        Route::post('bookings/{booking}/pay-complete', [ConsumerCompletionPaymentController::class, 'store'])->name('consumer.payments.complete.store');

        // Consumer reviews
        Route::get('bookings/{booking}/review', [ConsumerReviewController::class, 'create'])->name('consumer.reviews.create');
        Route::post('bookings/{booking}/review', [ConsumerReviewController::class, 'store'])->name('consumer.reviews.store');

        // Consumer jobs (Flow B — Post & Bid)
        Route::get('jobs', [ConsumerJobController::class, 'index'])->name('consumer.jobs.index');
        Route::get('jobs/create', [ConsumerJobController::class, 'create'])->name('consumer.jobs.create');
        Route::post('jobs', [ConsumerJobController::class, 'store'])->name('consumer.jobs.store');
        Route::get('jobs/{jobPost}', [ConsumerJobController::class, 'show'])->name('consumer.jobs.show');
        Route::post('jobs/{jobPost}/cancel', [ConsumerJobController::class, 'cancel'])->name('consumer.jobs.cancel');
        Route::post('jobs/{jobPost}/bids/{bid}/accept', [ConsumerJobController::class, 'acceptBid'])->name('consumer.jobs.bids.accept');

        // Consumer contracts (multi-service projects)
        Route::get('contracts', [ConsumerContractController::class, 'index'])->name('consumer.contracts.index');
        Route::get('contracts/create', [ConsumerContractController::class, 'create'])->name('consumer.contracts.create');
        Route::post('contracts', [ConsumerContractController::class, 'store'])->name('consumer.contracts.store');
        Route::get('contracts/{contract}', [ConsumerContractController::class, 'show'])->name('consumer.contracts.show');
        Route::post('contracts/{contract}/accept', [ConsumerContractController::class, 'accept'])->name('consumer.contracts.accept');
        Route::post('contracts/{contract}/reject', [ConsumerContractController::class, 'reject'])->name('consumer.contracts.reject');
        Route::post('contracts/{contract}/cancel', [ConsumerContractController::class, 'cancel'])->name('consumer.contracts.cancel');
        Route::get('contracts/{contract}/milestones/{milestone}/pay', [ConsumerContractController::class, 'payMilestone'])->name('consumer.contracts.milestones.pay');
        Route::post('contracts/{contract}/milestones/{milestone}/pay', [ConsumerContractController::class, 'storeMilestonePayment'])->name('consumer.contracts.milestones.pay.store');

        // Consumer emergencies (Flow C)
        Route::get('emergencies', [ConsumerEmergencyController::class, 'index'])->name('consumer.emergencies.index');
        Route::get('emergencies/create', [ConsumerEmergencyController::class, 'create'])->name('consumer.emergencies.create');
        Route::post('emergencies', [ConsumerEmergencyController::class, 'store'])->name('consumer.emergencies.store');
        Route::get('emergencies/{emergencyRequest}', [ConsumerEmergencyController::class, 'show'])->name('consumer.emergencies.show');
        Route::post('emergencies/{emergencyRequest}/cancel', [ConsumerEmergencyController::class, 'cancel'])->name('consumer.emergencies.cancel');
        Route::post('emergencies/{emergencyRequest}/accept-quote', [ConsumerEmergencyController::class, 'acceptQuote'])->name('consumer.emergencies.accept-quote');
        Route::post('emergencies/{emergencyRequest}/decline-quote', [ConsumerEmergencyController::class, 'declineQuote'])->name('consumer.emergencies.decline-quote');
    });

    // ─── Provider ────────────────────────────────────────────────
    Route::middleware('role:provider')->prefix('provider')->name('provider.')->group(function () {
        Route::get('dashboard', [ProviderDashboardController::class, 'index'])->name('dashboard');

        Route::get('onboarding', [OnboardingController::class, 'show'])->name('onboarding');
        Route::put('onboarding', [OnboardingController::class, 'update'])->name('onboarding.update');
        Route::post('onboarding/submit', [OnboardingController::class, 'submit'])->name('onboarding.submit');
        Route::post('onboarding/documents', [OnboardingController::class, 'storeDocument'])->name('onboarding.documents.store');
        Route::delete('onboarding/documents/{document}', [OnboardingController::class, 'destroyDocument'])->name('onboarding.documents.destroy');

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

        Route::get('wallet', [ProviderWalletController::class, 'index'])->name('wallet.index');
        Route::post('payout-method', [ProviderPayoutMethodController::class, 'update'])->name('payout-method.update');
        Route::post('withdrawals', [ProviderWithdrawalController::class, 'store'])->name('withdrawals.store');
        Route::post('withdrawals/{withdrawal}/confirm-receipt', [ProviderWithdrawalController::class, 'confirmReceipt'])->name('withdrawals.confirm-receipt');

        Route::get('settlements', [ProviderSettlementController::class, 'index'])->name('settlements.index');
        Route::post('settlements', [ProviderSettlementController::class, 'store'])->name('settlements.store');

        Route::get('portfolio', [ProviderPortfolioController::class, 'index'])->name('portfolio.index');
        Route::post('portfolio', [ProviderPortfolioController::class, 'store'])->name('portfolio.store');
        Route::delete('portfolio/{photo}', [ProviderPortfolioController::class, 'destroy'])->name('portfolio.destroy');
    });

    // ─── Job seeker ──────────────────────────────────────────────
    Route::middleware('role:job_seeker')->prefix('job-seeker')->name('job-seeker.')->group(function () {
        Route::get('dashboard', [JobSeekerDashboardController::class, 'index'])->name('dashboard');

        Route::get('profile', [JobSeekerProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [JobSeekerProfileController::class, 'update'])->name('profile.update');
        Route::post('profile/resume', [JobSeekerProfileController::class, 'storeResume'])->name('profile.resume.store');
        Route::delete('profile/resume', [JobSeekerProfileController::class, 'destroyResume'])->name('profile.resume.destroy');

        Route::post('careers/{listing}/apply', [JobSeekerCareerApplicationController::class, 'store'])->name('careers.apply');

        Route::get('applications', [JobSeekerCareerApplicationController::class, 'index'])->name('applications.index');
        Route::get('applications/{application}', [JobSeekerCareerApplicationController::class, 'show'])->name('applications.show');
        Route::post('applications/{application}/withdraw', [JobSeekerCareerApplicationController::class, 'withdraw'])->name('applications.withdraw');
    });

    // ─── Admin ───────────────────────────────────────────────────
    Route::middleware('role:admin,staff')->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::middleware('permission:requests')->group(function () {
            Route::get('requests', [AdminRequestsInboxController::class, 'index'])->name('requests.index');
        });

        Route::middleware('permission:categories')->group(function () {
            Route::resource('categories', CategoryController::class)->except(['show']);
        });
        Route::middleware('permission:services')->group(function () {
            Route::resource('services', AdminServiceController::class)->except(['show']);
        });
        Route::middleware('permission:faqs')->group(function () {
            Route::resource('faqs', AdminFaqController::class)->except(['show']);
        });

        Route::middleware('permission:careers')->group(function () {
            Route::resource('career-categories', AdminCareerCategoryController::class)->except(['show']);
            Route::resource('careers', AdminCareerListingController::class)->except(['show']);
            Route::get('careers/{career}/applications', [AdminCareerApplicationController::class, 'index'])->name('careers.applications.index');
            Route::get('careers/{career}/applications/{application}', [AdminCareerApplicationController::class, 'show'])->name('careers.applications.show');
            Route::post('careers/{career}/applications/{application}/status', [AdminCareerApplicationController::class, 'updateStatus'])->name('careers.applications.status');
        });

        Route::middleware('permission:talent')->group(function () {
            Route::get('talent', [AdminTalentSearchController::class, 'index'])->name('talent.index');
        });

        // Subscription / AMC plans
        Route::middleware('permission:subscriptions')->group(function () {
            Route::resource('subscription-plans', AdminSubscriptionPlanController::class)->except(['show']);
            Route::get('subscriptions', [AdminSubscriptionController::class, 'index'])->name('subscriptions.index');
            Route::get('subscriptions/{subscription}', [AdminSubscriptionController::class, 'show'])->name('subscriptions.show');
            Route::post('subscriptions/{subscription}/assign', [AdminSubscriptionController::class, 'assignProvider'])->name('subscriptions.assign');
        });

        // Corporate / B2B accounts (read-only)
        Route::middleware('permission:corporate-accounts')->group(function () {
            Route::get('corporate-accounts', [AdminCorporateAccountController::class, 'index'])->name('corporate-accounts.index');
            Route::get('corporate-accounts/{corporateAccount}', [AdminCorporateAccountController::class, 'show'])->name('corporate-accounts.show');
        });

        // Withdrawals — money-related, admin-only, never delegable to staff.
        Route::middleware('role:admin')->group(function () {
            Route::get('withdrawals', [AdminWithdrawalController::class, 'index'])->name('withdrawals.index');
            Route::get('withdrawals/{withdrawal}', [AdminWithdrawalController::class, 'show'])->name('withdrawals.show');
            Route::post('withdrawals/{withdrawal}/paid-cash', [AdminWithdrawalController::class, 'markPaidCash'])->name('withdrawals.paid-cash');
            Route::post('withdrawals/{withdrawal}/paid-bank-transfer', [AdminWithdrawalController::class, 'markPaidBankTransfer'])->name('withdrawals.paid-bank-transfer');
            Route::post('withdrawals/{withdrawal}/reject', [AdminWithdrawalController::class, 'reject'])->name('withdrawals.reject');

            Route::get('payments', [AdminPaymentVerificationController::class, 'index'])->name('payments.index');
            Route::get('payments/{payment}', [AdminPaymentVerificationController::class, 'show'])->name('payments.show');
            Route::post('payments/{payment}/verify', [AdminPaymentVerificationController::class, 'verify'])->name('payments.verify');
            Route::post('payments/{payment}/reject', [AdminPaymentVerificationController::class, 'reject'])->name('payments.reject');

            Route::get('settlements', [AdminProviderSettlementController::class, 'index'])->name('settlements.index');
            Route::get('settlements/{settlement}', [AdminProviderSettlementController::class, 'show'])->name('settlements.show');
            Route::post('settlements/{settlement}/confirm', [AdminProviderSettlementController::class, 'confirm'])->name('settlements.confirm');
            Route::post('settlements/{settlement}/reject', [AdminProviderSettlementController::class, 'reject'])->name('settlements.reject');
        });

        Route::middleware('permission:bookings')->group(function () {
            Route::get('bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
            Route::get('bookings/create', [AdminBookingController::class, 'create'])->name('bookings.create');
            Route::post('bookings', [AdminBookingController::class, 'store'])->name('bookings.store');
            Route::get('bookings/{booking}', [AdminBookingController::class, 'show'])->name('bookings.show');
        });

        Route::middleware('permission:emergencies')->group(function () {
            Route::get('emergencies', [AdminEmergencyController::class, 'index'])->name('emergencies.index');
            Route::get('emergencies/{emergencyRequest}', [AdminEmergencyController::class, 'show'])->name('emergencies.show');
            Route::post('emergencies/{emergencyRequest}/quote', [AdminEmergencyController::class, 'quote'])->name('emergencies.quote');
            Route::post('emergencies/{emergencyRequest}/assign', [AdminEmergencyController::class, 'assign'])->name('emergencies.assign');
        });

        Route::middleware('permission:invoices')->group(function () {
            Route::get('invoices', [AdminInvoiceController::class, 'index'])->name('invoices.index');
            Route::get('invoices/create', [AdminInvoiceController::class, 'create'])->name('invoices.create');
            Route::post('invoices', [AdminInvoiceController::class, 'store'])->name('invoices.store');
            Route::get('invoices/{invoice}', [AdminInvoiceController::class, 'show'])->name('invoices.show');
            Route::get('invoices/{invoice}/edit', [AdminInvoiceController::class, 'edit'])->name('invoices.edit');
            Route::put('invoices/{invoice}', [AdminInvoiceController::class, 'update'])->name('invoices.update');
            Route::delete('invoices/{invoice}', [AdminInvoiceController::class, 'destroy'])->name('invoices.destroy');
            Route::get('invoices/{invoice}/download', [AdminInvoiceController::class, 'download'])->name('invoices.download');
        });

        Route::middleware('permission:contracts')->group(function () {
            Route::get('contracts', [AdminContractController::class, 'index'])->name('contracts.index');
            Route::get('contracts/{contract}', [AdminContractController::class, 'show'])->name('contracts.show');
            Route::post('contracts/{contract}/quote', [AdminContractController::class, 'quote'])->name('contracts.quote');
            Route::post('contracts/{contract}/items/{item}/assign', [AdminContractController::class, 'assignProvider'])->name('contracts.items.assign');
            Route::post('contracts/{contract}/milestones/{milestone}/release', [AdminContractController::class, 'releaseMilestone'])->name('contracts.milestones.release');
        });

        Route::middleware('permission:providers')->group(function () {
            Route::get('providers', [AdminProviderController::class, 'index'])->name('providers.index');
            Route::get('providers/{provider}', [AdminProviderController::class, 'show'])->name('providers.show');
            Route::post('providers/{provider}/approve', [AdminProviderController::class, 'approve'])->name('providers.approve');
            Route::post('providers/{provider}/reject', [AdminProviderController::class, 'reject'])->name('providers.reject');
            Route::post('providers/{provider}/suspend', [AdminProviderController::class, 'suspend'])->name('providers.suspend');
            Route::post('providers/{provider}/resume', [AdminProviderController::class, 'resume'])->name('providers.resume');
        });

        Route::middleware('permission:disputes')->group(function () {
            Route::get('disputes', [AdminDisputeController::class, 'index'])->name('disputes.index');
            Route::get('disputes/{dispute}', [AdminDisputeController::class, 'show'])->name('disputes.show');
            Route::post('disputes/{dispute}/resolve', [AdminDisputeController::class, 'resolve'])->name('disputes.resolve');
        });

        // Analytics — money-related, admin-only, never delegable to staff.
        Route::middleware('role:admin')->group(function () {
            Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
        });

        Route::middleware('permission:settings')->group(function () {
            Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
            Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        });

        Route::middleware('permission:service-areas')->group(function () {
            Route::get('service-areas', [ServiceAreaController::class, 'index'])->name('service-areas.index');
            Route::post('service-areas', [ServiceAreaController::class, 'store'])->name('service-areas.store');
            Route::put('service-areas/{serviceArea}', [ServiceAreaController::class, 'update'])->name('service-areas.update');
            Route::delete('service-areas/{serviceArea}', [ServiceAreaController::class, 'destroy'])->name('service-areas.destroy');
        });

        Route::middleware('permission:fraud')->group(function () {
            Route::get('fraud', [FraudController::class, 'index'])->name('fraud.index');
        });

        // Users — account-level, admin-only, never delegable to staff.
        Route::middleware('role:admin')->group(function () {
            Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
            Route::post('users/{user}/suspend', [AdminUserController::class, 'suspend'])->name('users.suspend');
            Route::post('users/{user}/unsuspend', [AdminUserController::class, 'unsuspend'])->name('users.unsuspend');
        });

        // Staff accounts — admin-only, never delegable to a staff account itself.
        Route::middleware('role:admin')->group(function () {
            Route::resource('staff', AdminStaffController::class)->except(['show']);
            Route::post('staff/{staff}/suspend', [AdminStaffController::class, 'suspend'])->name('staff.suspend');
            Route::post('staff/{staff}/unsuspend', [AdminStaffController::class, 'unsuspend'])->name('staff.unsuspend');
        });
    });
});