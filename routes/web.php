<?php

use App\Http\Controllers\Admin\AboutContentController;
use App\Http\Controllers\Admin\AboutHistoryController;
use App\Http\Controllers\Admin\AboutSectionController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AuthController;
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
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\NewsController as PublicNewsController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\ProjectController as PublicProjectController;
use App\Http\Controllers\Public\ServiceController as PublicServiceController;
use App\Http\Controllers\Public\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('about', [PageController::class, 'about'])->name('about');
Route::get('faq', [PageController::class, 'faq'])->name('faq');

Route::get('services', [PublicServiceController::class, 'index'])->name('services.index');
Route::get('services/{slug}', [PublicServiceController::class, 'show'])->name('services.show');

Route::get('projects', [PublicProjectController::class, 'index'])->name('projects.index');
Route::get('projects/{slug}', [PublicProjectController::class, 'show'])->name('projects.show');

Route::get('news', [PublicNewsController::class, 'index'])->name('news.index');
Route::get('news/{slug}', [PublicNewsController::class, 'show'])->name('news.show');

/*
|--------------------------------------------------------------------------
| SEO — sitemap & robots (dynamic so URLs are domain-aware)
|--------------------------------------------------------------------------
*/

Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('robots.txt', function () {
    $body = implode("\n", [
        'User-agent: *',
        'Disallow: /admin',
        '',
        'Sitemap: '.route('sitemap'),
    ])."\n";

    return response($body, 200)->header('Content-Type', 'text/plain');
})->name('robots');

/*
|--------------------------------------------------------------------------
| Public Contact Form
|--------------------------------------------------------------------------
*/

Route::get('contact', [ContactController::class, 'create'])->name('contact');
Route::post('contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

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

            Route::resource(
                'company-documents',
                CompanyDocumentController::class
            );

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
