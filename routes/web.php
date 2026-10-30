<?php

use App\Http\Controllers\Web\Admin\ActivityLogController;
use App\Http\Controllers\Web\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Web\Admin\ClientController as AdminClientController;
use App\Http\Controllers\Web\Admin\CompanyProfileController as AdminCompanyProfileController;
use App\Http\Controllers\Web\Admin\DashboardController;
use App\Http\Controllers\Web\Admin\PortfolioController as AdminPortfolioController;
use App\Http\Controllers\Web\Admin\ServiceRequestController as AdminServiceRequestController;
use App\Http\Controllers\Web\Admin\TeamPortfolioController as AdminTeamPortfolioController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\PortfolioController;
use App\Http\Controllers\Web\PwaController;
use App\Http\Controllers\Web\ServiceRequestController;
use App\Http\Controllers\Web\TeamPortfolioController;
use App\Models\Portfolio;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PUBLIK — server-rendered, SEO-friendly (URL nyata per halaman)
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/portfolio', [PortfolioController::class, 'index'])->name('portfolio.index');
Route::get('/portfolio/{slug}', [PortfolioController::class, 'show'])->name('portfolio.show');
Route::get('/tim/{slug}', [TeamPortfolioController::class, 'show'])->where('slug', '[a-z0-9\-]+')->name('team.portfolio');
Route::get('/pesan-jasa', [ServiceRequestController::class, 'create'])->name('requests.create');
Route::post('/pesan-jasa', [ServiceRequestController::class, 'store'])->middleware('throttle:public-form')->name('requests.store');
Route::get('/pesan-jasa/sukses', [ServiceRequestController::class, 'success'])->name('requests.success');

// PWA: manifest (tanpa ekstensi supaya selalu lewat Laravel) + halaman offline.
Route::get('/app-manifest', [PwaController::class, 'manifest'])->name('pwa.manifest');
Route::get('/admin/app-manifest', [PwaController::class, 'adminManifest'])->name('pwa.manifest.admin');
Route::get('/offline', [PwaController::class, 'offline'])->name('pwa.offline');

// SEO — sitemap dinamis dari portfolio yang publish + halaman statis.
Route::get('/media/{path}', [App\Http\Controllers\Web\MediaController::class, 'show'])
    ->where('path', '.*')
    ->name('media');

Route::get('/sitemap.xml', function () {
    $urls = collect([
        ['loc' => route('home'), 'priority' => '1.0'],
        ['loc' => route('portfolio.index'), 'priority' => '0.8'],
        ['loc' => route('requests.create'), 'priority' => '0.8'],
    ])->concat(
        Portfolio::published()->get()->map(fn ($p) => [
            'loc' => route('portfolio.show', $p->slug),
            'priority' => '0.6',
            'lastmod' => $p->updated_at->toAtomString(),
        ])
    );

    return response()
        ->view('sitemap', ['urls' => $urls])
        ->header('Content-Type', 'text/xml');
})->name('sitemap');

/*
|--------------------------------------------------------------------------
| ADMIN — session auth, satu sistem yang sama dengan publik (bukan app terpisah)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login/password', [AdminAuthController::class, 'verifyPassword'])->middleware('throttle:admin-login')->name('login.password');
    Route::post('/login/email', [AdminAuthController::class, 'sendOtp'])->middleware('throttle:admin-login')->name('login.email');
    Route::post('/login/otp', [AdminAuthController::class, 'verifyOtp'])->middleware('throttle:admin-login')->name('login.otp');
    Route::post('/login/resend', [AdminAuthController::class, 'resendOtp'])->middleware('throttle:admin-login')->name('login.resend');
    Route::post('/login/reset', [AdminAuthController::class, 'resetLogin'])->name('login.reset');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware(['auth', 'admin.security'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/requests', [AdminServiceRequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/{request}', [AdminServiceRequestController::class, 'show'])->name('requests.show');
        Route::patch('/requests/{request}/status', [AdminServiceRequestController::class, 'updateStatus'])->name('requests.status');
        Route::post('/requests/{request}/notes', [AdminServiceRequestController::class, 'storeNote'])->name('requests.notes.store');
        Route::post('/requests/{request}/convert-to-client', [AdminServiceRequestController::class, 'convertToClient'])->name('requests.convert');
        Route::post('/requests/{request}/assign-team', [AdminServiceRequestController::class, 'assignTeam'])->name('requests.assign-team');
        Route::get('/requests/{request}/assignment-message/{member}', [AdminServiceRequestController::class, 'assignmentMessage'])->name('requests.assignment-message');
        Route::post('/requests/{request}/assignment-email/{member}', [AdminServiceRequestController::class, 'sendAssignmentEmail'])->name('requests.assignment-email');
        Route::post('/requests/{request}/assignment-whatsapp/{member}', [AdminServiceRequestController::class, 'assignmentWhatsapp'])->name('requests.assignment-whatsapp');

        // Anggota agency + keahlian.
        Route::get('/team', [App\Http\Controllers\Web\Admin\TeamMemberController::class, 'index'])->name('team.index');
        Route::get('/team/create', [App\Http\Controllers\Web\Admin\TeamMemberController::class, 'create'])->name('team.create');
        Route::post('/team', [App\Http\Controllers\Web\Admin\TeamMemberController::class, 'store'])->name('team.store');
        Route::get('/team/{member}/edit', [App\Http\Controllers\Web\Admin\TeamMemberController::class, 'edit'])->name('team.edit');
        Route::patch('/team/{member}', [App\Http\Controllers\Web\Admin\TeamMemberController::class, 'update'])->name('team.update');
        Route::delete('/team/{member}', [App\Http\Controllers\Web\Admin\TeamMemberController::class, 'destroy'])->name('team.destroy');

        // Portofolio pribadi anggota tim (halaman publik /tim/{slug}).
        Route::get('/team/{member}/portfolio', [AdminTeamPortfolioController::class, 'edit'])->name('team.portfolio.edit');
        Route::put('/team/{member}/portfolio', [AdminTeamPortfolioController::class, 'update'])->name('team.portfolio.update');
        Route::post('/team/{member}/portfolio/generate', [AdminTeamPortfolioController::class, 'generate'])->name('team.portfolio.generate');
        Route::post('/team/{member}/portfolio/publish', [AdminTeamPortfolioController::class, 'togglePublish'])->name('team.portfolio.publish');
        Route::get('/team/{member}/portfolio/preview', [AdminTeamPortfolioController::class, 'preview'])->name('team.portfolio.preview');

        Route::get('/clients', [AdminClientController::class, 'index'])->name('clients.index');
        Route::patch('/clients/{client}', [AdminClientController::class, 'update'])->name('clients.update');
        Route::delete('/clients/{client}', [AdminClientController::class, 'destroy'])->name('clients.destroy');

        // Portfolio CRUD — sekaligus jadi CMS untuk microsite publik /portfolio/{slug}.
        Route::get('/portfolio', [AdminPortfolioController::class, 'index'])->name('portfolio.index');
        Route::get('/portfolio/create', [AdminPortfolioController::class, 'create'])->name('portfolio.create');
        Route::post('/portfolio', [AdminPortfolioController::class, 'store'])->name('portfolio.store');
        Route::get('/portfolio/{portfolio}/edit', [AdminPortfolioController::class, 'edit'])->name('portfolio.edit');
        Route::get('/portfolio/{portfolio}/pdf', [AdminPortfolioController::class, 'pdf'])->name('portfolio.pdf');
        Route::get('/portfolio/{portfolio}/powerpoint', [AdminPortfolioController::class, 'powerpoint'])->name('portfolio.powerpoint');
        Route::post('/portfolio/export/pdf', [AdminPortfolioController::class, 'exportSelectedPdf'])->name('portfolio.export.pdf');
        Route::post('/portfolio/export/powerpoint', [AdminPortfolioController::class, 'exportSelectedPowerPoint'])->name('portfolio.export.powerpoint');
        Route::delete('/portfolio/{portfolio}/image', [AdminPortfolioController::class, 'deleteImage'])->name('portfolio.image.delete');
        Route::patch('/portfolio/{portfolio}', [AdminPortfolioController::class, 'update'])->name('portfolio.update');
        Route::patch('/portfolio/{portfolio}/publish', [AdminPortfolioController::class, 'togglePublish'])->name('portfolio.publish');
        Route::delete('/portfolio/{portfolio}', [AdminPortfolioController::class, 'destroy'])->name('portfolio.destroy');

        Route::get('/account', [App\Http\Controllers\Web\Admin\AccountController::class, 'edit'])->name('account.edit');
        Route::patch('/account', [App\Http\Controllers\Web\Admin\AccountController::class, 'update'])->name('account.update');

        Route::get('/company-profile', [AdminCompanyProfileController::class, 'edit'])->name('company-profile.edit');
        Route::patch('/company-profile', [AdminCompanyProfileController::class, 'update'])->name('company-profile.update');
        Route::get('/footer', [AdminCompanyProfileController::class, 'footerEdit'])->name('footer.edit');
        Route::patch('/footer', [AdminCompanyProfileController::class, 'footerUpdate'])->name('footer.update');

        Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
    });
});
