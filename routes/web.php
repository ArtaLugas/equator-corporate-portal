<?php

use App\Http\Controllers\Admin\AboutContentController;
use App\Http\Controllers\Admin\AboutHistoryController;
use App\Http\Controllers\Admin\AboutSectionController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\PasswordResetController;
use App\Http\Controllers\Admin\TwoFactorChallengeController;
use App\Http\Controllers\Admin\TwoFactorController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CompanyCredentialController;
use App\Http\Controllers\Admin\CompanyDocumentController;
use App\Http\Controllers\Admin\CoreValueController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\HeroBannerController;
use App\Http\Controllers\Admin\KeyMetricController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\NewsCategoryController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OfficeLocationController;
use App\Http\Controllers\Admin\PartnerController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SocialLinkController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Admin\TranslationProgressController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\LegalController;
use App\Http\Controllers\Public\NewsController as PublicNewsController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\ProjectController as PublicProjectController;
use App\Http\Controllers\Public\ServiceController as PublicServiceController;
use App\Http\Controllers\Public\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public (localized)
|--------------------------------------------------------------------------
|
| URL strategy: the default locale (en) is served unprefixed (/services) and
| is the SEO canonical; other locales are URL-prefixed (/id/services).
|
| Symfony drops the optionality of a leading {locale?} when a static segment
| follows it (so "{locale?}/about" compiles as required), which means a single
| optional-prefix group cannot match BOTH /about and /id/about. We therefore
| register the same routes twice from one closure:
|
|   (1) PLAIN, unprefixed, UNNAMED → matches default-locale URLs (/about) only.
|   (2) LOCALIZED, {locale?}, NAMED → matches prefixed URLs (/id/about) AND owns
|       the route names, so route() stays locale-aware via URL::defaults (set
|       per-request in SetLocale). An explicit default prefix (/en/...) is 301'd
|       to the canonical URL by SetLocale.
|
| Only the localized group is named — registering both with the same names would
| break `route:cache` (Symfony requires unique route names). Matching is by URI,
| so the unnamed plain group still resolves /about, /services, etc.
| No existing route() call needs to change.
|
*/

$publicRoutes = function (bool $named) {
    $name = fn ($route, $routeName) => $named ? $route->name($routeName) : $route;

    $name(Route::get('/', [HomeController::class, 'index']), 'home');

    $name(Route::get('about', [PageController::class, 'about']), 'about');
    $name(Route::get('faq', [PageController::class, 'faq']), 'faq');

    $name(Route::get('privacy', [LegalController::class, 'privacy']), 'privacy');
    $name(Route::get('cookies', [LegalController::class, 'cookies']), 'cookies');

    $name(Route::get('services', [PublicServiceController::class, 'index']), 'services.index');
    $name(Route::get('services/{slug}', [PublicServiceController::class, 'show']), 'services.show');

    $name(Route::get('projects', [PublicProjectController::class, 'index']), 'projects.index');
    $name(Route::get('projects/{slug}', [PublicProjectController::class, 'show']), 'projects.show');

    $name(Route::get('news', [PublicNewsController::class, 'index']), 'news.index');
    $name(Route::get('news/{slug}', [PublicNewsController::class, 'show']), 'news.show');

    $name(Route::get('contact', [ContactController::class, 'create']), 'contact');
    $name(
        Route::post('contact', [ContactController::class, 'store'])->middleware('throttle:5,1'),
        'contact.store'
    );
};

// (1) Default locale — unprefixed, canonical. UNNAMED (matching only).
Route::middleware('setlocale')->group(fn () => $publicRoutes(false));

// (2) Localized — prefixed for non-default locales. NAMED (owns route names).
Route::prefix('{locale?}')
    ->where(['locale' => implode('|', array_keys(config('locales.supported', [])))])
    ->middleware('setlocale')
    ->group(fn () => $publicRoutes(true));

/*
|--------------------------------------------------------------------------
| SEO — sitemap & robots (dynamic so URLs are domain-aware; not localized)
|--------------------------------------------------------------------------
*/

Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Controller (not a closure) so the route set stays cacheable via route:cache.
Route::get('robots.txt', [SitemapController::class, 'robots'])->name('robots');

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        */

        Route::get('login', [AuthController::class, 'login'])
            ->name('login');

        // Coarse per-IP outer bound. Fine-grained brute-force protection
        // (per email+IP, with lockout) lives in AuthController::authenticate.
        Route::post('login', [AuthController::class, 'authenticate'])
            ->middleware('throttle:10,1')
            ->name('authenticate');

        /*
        |--------------------------------------------------------------------------
        | Password Reset (self-service, guest) — throttled to curb abuse.
        |--------------------------------------------------------------------------
        */

        Route::get('password/forgot', [PasswordResetController::class, 'showForgotForm'])
            ->name('password.request');
        Route::post('password/forgot', [PasswordResetController::class, 'sendResetLink'])
            ->middleware('throttle:5,1')
            ->name('password.email');
        Route::get('password/reset/{token}', [PasswordResetController::class, 'showResetForm'])
            ->name('password.reset');
        Route::post('password/reset', [PasswordResetController::class, 'resetPassword'])
            ->middleware('throttle:5,1')
            ->name('password.update');

        /*
        |--------------------------------------------------------------------------
        | Two-Factor login challenge (second step; gated by a pending session).
        |--------------------------------------------------------------------------
        */

        Route::get('login/two-factor', [TwoFactorChallengeController::class, 'create'])
            ->name('two-factor.login');
        Route::post('login/two-factor', [TwoFactorChallengeController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('two-factor.login.store');

        /*
        |--------------------------------------------------------------------------
        | Protected Routes
        |--------------------------------------------------------------------------
        */

        Route::middleware('admin.auth')->group(function () {

            /*
            |--------------------------------------------------------------------------
            | Dashboard
            |--------------------------------------------------------------------------
            */

            Route::get('dashboard', [DashboardController::class, 'index'])
                ->name('dashboard');

            Route::get('dashboard/report', [DashboardController::class, 'report'])
                ->name('dashboard.report');

            Route::get('dashboard/export', [DashboardController::class, 'export'])
                ->name('dashboard.export');

            /*
            |--------------------------------------------------------------------------
            | Translation Progress (i18n monitoring)
            |--------------------------------------------------------------------------
            */

            Route::get('translations', [TranslationProgressController::class, 'index'])
                ->name('translations.index');

            /*
            |--------------------------------------------------------------------------
            | Logout
            |--------------------------------------------------------------------------
            */

            Route::post('logout', [AuthController::class, 'logout'])
                ->name('logout');

            /*
            |--------------------------------------------------------------------------
            | Account Settings (self-service)
            |--------------------------------------------------------------------------
            */

            /*
            |--------------------------------------------------------------------------
            | Notifications
            |--------------------------------------------------------------------------
            */

            Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
            Route::post('notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
            Route::delete('notifications/clear', [NotificationController::class, 'clear'])->name('notifications.clear');
            Route::get('notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');

            Route::get('account', [AccountController::class, 'edit'])->name('account.edit');
            Route::put('account/profile', [AccountController::class, 'updateProfile'])->name('account.profile.update');
            Route::put('account/password', [AccountController::class, 'updatePassword'])->name('account.password.update');

            // Two-factor enrollment (opt-in, from the Account page).
            Route::post('account/2fa/enable', [TwoFactorController::class, 'enable'])->name('account.2fa.enable');
            Route::post('account/2fa/confirm', [TwoFactorController::class, 'confirm'])->name('account.2fa.confirm');
            Route::post('account/2fa/recovery-codes', [TwoFactorController::class, 'recoveryCodes'])->name('account.2fa.recovery');
            Route::delete('account/2fa', [TwoFactorController::class, 'disable'])->name('account.2fa.disable');

            /*
            |--------------------------------------------------------------------------
            | Service Categories
            |--------------------------------------------------------------------------
            */

            Route::get(
                'service-categories/trash',
                [ServiceCategoryController::class, 'trash']
            )->name('service-categories.trash');

            Route::patch(
                'service-categories/{id}/restore',
                [ServiceCategoryController::class, 'restore']
            )->name('service-categories.restore');

            Route::delete(
                'service-categories/{id}/force-delete',
                [ServiceCategoryController::class, 'forceDelete']
            )->name('service-categories.force-delete');

            Route::post('service-categories/bulk-destroy', [ServiceCategoryController::class, 'bulkDestroy'])->name('service-categories.bulk-destroy');

            Route::resource(
                'service-categories',
                ServiceCategoryController::class
            );

            /*
            |--------------------------------------------------------------------------
            | Services
            |--------------------------------------------------------------------------
            */

            Route::get('services/trash', [ServiceController::class, 'trash'])->name('services.trash');

            Route::patch('services/{id}/restore', [ServiceController::class, 'restore'])->name('services.restore');

            Route::delete('services/{id}/force-delete', [ServiceController::class, 'forceDelete'])->name('services.force-delete');

            Route::post('services/bulk-destroy', [ServiceController::class, 'bulkDestroy'])->name('services.bulk-destroy');

            Route::resource(
                'services',
                ServiceController::class
            );

            /*
            |--------------------------------------------------------------------------
            | Hero Banner
            |--------------------------------------------------------------------------
            */

            Route::resource('hero-banners', HeroBannerController::class);

            /*
            |--------------------------------------------------------------------------
            | About Section
            |--------------------------------------------------------------------------
            */
            Route::resource('about-sections', AboutSectionController::class);
            Route::resource('about-contents', AboutContentController::class);
            Route::resource('about-histories', AboutHistoryController::class);

            /*
            |--------------------------------------------------------------------------
            | Core Value
            |--------------------------------------------------------------------------
            */

            Route::resource('core-values', CoreValueController::class);

            /*
            |--------------------------------------------------------------------------
            | Company Documents
            |--------------------------------------------------------------------------
            */

            Route::get(
                'company-documents/trash',
                [CompanyDocumentController::class, 'trash']
            )->name('company-documents.trash');

            Route::patch(
                'company-documents/{id}/restore',
                [CompanyDocumentController::class, 'restore']
            )->name('company-documents.restore');

            Route::delete(
                'company-documents/{id}/force-delete',
                [CompanyDocumentController::class, 'forceDelete']
            )->name('company-documents.force-delete');

            Route::post('company-documents/bulk-destroy', [CompanyDocumentController::class, 'bulkDestroy'])->name('company-documents.bulk-destroy');

            Route::resource(
                'company-documents',
                CompanyDocumentController::class
            );

            /*
            |--------------------------------------------------------------------------
            | Company Credentials
            |--------------------------------------------------------------------------
            */

            Route::get('company-credentials/trash', [CompanyCredentialController::class, 'trash'])->name('company-credentials.trash');
            Route::patch('company-credentials/{id}/restore', [CompanyCredentialController::class, 'restore'])->name('company-credentials.restore');
            Route::delete('company-credentials/{id}/force-delete', [CompanyCredentialController::class, 'forceDelete'])->name('company-credentials.force-delete');
            Route::post('company-credentials/bulk-destroy', [CompanyCredentialController::class, 'bulkDestroy'])->name('company-credentials.bulk-destroy');

            Route::resource('company-credentials', CompanyCredentialController::class);

            /*
            |--------------------------------------------------------------------------
            | Teams
            |--------------------------------------------------------------------------
            */

            Route::get(
                'teams/trash',
                [TeamController::class, 'trash']
            )->name('teams.trash');

            Route::patch(
                'teams/{id}/restore',
                [TeamController::class, 'restore']
            )->name('teams.restore');

            Route::delete(
                'teams/{id}/force-delete',
                [TeamController::class, 'forceDelete']
            )->name('teams.force-delete');

            Route::post('teams/bulk-destroy', [TeamController::class, 'bulkDestroy'])->name('teams.bulk-destroy');

            Route::resource(
                'teams',
                TeamController::class
            );

            /*
            |--------------------------------------------------------------------------
            | Partners
            |--------------------------------------------------------------------------
            */

            Route::get(
                'partners/trash',
                [PartnerController::class, 'trash']
            )->name('partners.trash');

            Route::patch(
                'partners/{id}/restore',
                [PartnerController::class, 'restore']
            )->name('partners.restore');

            Route::delete(
                'partners/{id}/force-delete',
                [PartnerController::class, 'forceDelete']
            )->name('partners.force-delete');

            Route::post('partners/bulk-destroy', [PartnerController::class, 'bulkDestroy'])->name('partners.bulk-destroy');

            Route::resource(
                'partners',
                PartnerController::class
            );

            /*
            |--------------------------------------------------------------------------
            | Projects
            |--------------------------------------------------------------------------
            */

            Route::get(
                'projects/trash',
                [ProjectController::class, 'trash']
            )->name('projects.trash');

            Route::patch(
                'projects/{id}/restore',
                [ProjectController::class, 'restore']
            )->name('projects.restore');

            Route::delete(
                'projects/{id}/force-delete',
                [ProjectController::class, 'forceDelete']
            )->name('projects.force-delete');

            Route::post('projects/bulk-destroy', [ProjectController::class, 'bulkDestroy'])->name('projects.bulk-destroy');

            Route::resource(
                'projects',
                ProjectController::class
            );

            /*
            |--------------------------------------------------------------------------
            | News Categories
            |--------------------------------------------------------------------------
            */

            Route::resource('news-categories', NewsCategoryController::class)
                ->except(['show']);

            /*
            |--------------------------------------------------------------------------
            | News
            |--------------------------------------------------------------------------
            */

            Route::get(
                'news/trash',
                [NewsController::class, 'trash']
            )->name('news.trash');

            Route::patch(
                'news/{id}/restore',
                [NewsController::class, 'restore']
            )->name('news.restore');

            Route::delete(
                'news/{id}/force-delete',
                [NewsController::class, 'forceDelete']
            )->name('news.force-delete');

            Route::post('news/bulk-destroy', [NewsController::class, 'bulkDestroy'])->name('news.bulk-destroy');

            Route::resource(
                'news',
                NewsController::class
            );

            /*
            |--------------------------------------------------------------------------
            | Messages (Communication)
            |--------------------------------------------------------------------------
            */

            Route::get('messages/trash', [MessageController::class, 'trash'])->name('messages.trash');
            Route::patch('messages/{id}/restore', [MessageController::class, 'restore'])->name('messages.restore');
            Route::delete('messages/{id}/force-delete', [MessageController::class, 'forceDelete'])->name('messages.force-delete');
            Route::post('messages/bulk-destroy', [MessageController::class, 'bulkDestroy'])->name('messages.bulk-destroy');

            // Lead analytics (mini-CRM) — before the {message} wildcard.
            Route::get('messages/analytics', [MessageController::class, 'analytics'])->name('messages.analytics');

            Route::get('messages', [MessageController::class, 'index'])->name('messages.index');
            Route::get('messages/{message}', [MessageController::class, 'show'])->name('messages.show');
            Route::post('messages/{message}/reply', [MessageController::class, 'reply'])->name('messages.reply');
            Route::patch('messages/{message}/archive', [MessageController::class, 'archive'])->name('messages.archive');
            Route::patch('messages/{message}/unarchive', [MessageController::class, 'unarchive'])->name('messages.unarchive');
            Route::patch('messages/{message}/spam', [MessageController::class, 'markSpam'])->name('messages.spam');
            Route::patch('messages/{message}/unread', [MessageController::class, 'markUnread'])->name('messages.unread');
            Route::delete('messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');

            /*
            |--------------------------------------------------------------------------
            | Settings — Email (Brevo SMTP)
            |--------------------------------------------------------------------------
            */

            Route::get('settings/general', [SettingController::class, 'general'])->name('settings.general');
            Route::put('settings/general', [SettingController::class, 'updateGeneral'])->name('settings.general.update');

            Route::get('settings/email', [SettingController::class, 'email'])->name('settings.email');
            Route::put('settings/email', [SettingController::class, 'updateEmail'])->name('settings.email.update');

            /*
            |--------------------------------------------------------------------------
            | Social Links
            |--------------------------------------------------------------------------
            */

            Route::resource('social-links', SocialLinkController::class)->except(['show']);

            Route::resource('office-locations', OfficeLocationController::class)->except(['show']);

            /*
            |--------------------------------------------------------------------------
            | FAQ
            |--------------------------------------------------------------------------
            */

            Route::resource('faqs', FaqController::class)->except(['show']);

            /*
            |--------------------------------------------------------------------------
            | Key Metrics (homepage stats)
            |--------------------------------------------------------------------------
            */

            Route::resource('key-metrics', KeyMetricController::class)->except(['show']);

            /*
            |--------------------------------------------------------------------------
            | Activity Log (Audit) — Super Admin only
            |--------------------------------------------------------------------------
            */

            Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

            /*
            |--------------------------------------------------------------------------
            | Admin / User Management (Super Admin only via policy)
            |--------------------------------------------------------------------------
            */

            Route::resource('admins', AdminController::class)->except(['show']);
        });
    });
