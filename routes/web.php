<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Seller\DashboardController as SellerDashboard;
use App\Http\Controllers\Seller\ProductController as SellerProductController;
use App\Http\Controllers\Seller\OrderController as SellerOrderController;
use App\Http\Controllers\Seller\ProfileController as SellerProfileController;
use App\Http\Controllers\Affiliate\DashboardController as AffiliateDashboard;
use App\Http\Controllers\Affiliate\CommissionController;
use App\Http\Controllers\Affiliate\PayoutController;
use App\Http\Controllers\Affiliate\ProfileController as AffiliateProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\SellerController as AdminSellerController;
use App\Http\Controllers\Admin\AffiliateController as AdminAffiliateController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CommissionController as AdminCommissionController;
use App\Http\Controllers\Admin\DestinationController as AdminDestinationController;
use App\Http\Controllers\Admin\TourismCategoryController;
use App\Http\Controllers\Admin\PayoutController as AdminPayoutController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\SettingController;

// ─── Language Switcher ────────────────────────────────────────────────────────
Route::post('/language/switch', [LanguageController::class, 'switchLanguage'])->name('language.switch');

// ─── Home ─────────────────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/newsletter', [HomeController::class, 'subscribe'])
    ->middleware('throttle:10,1')
    ->name('newsletter.subscribe');

// ─── Static / Marketing pages ─────────────────────────────────────────────────
Route::get('/a-propos', [PageController::class, 'about'])->name('pages.about');
Route::get('/devenir-vendeur', [PageController::class, 'becomeSeller'])->name('pages.become-seller');
Route::get('/devenir-affilie', [PageController::class, 'becomeAffiliate'])->name('pages.become-affiliate');
Route::get('/contact', [PageController::class, 'contact'])->name('pages.contact');
Route::post('/contact', [PageController::class, 'sendContact'])
    ->middleware('throttle:10,1')
    ->name('pages.contact.send');

// ─── Authentication ───────────────────────────────────────────────────────────
Auth::routes(['verify' => false]);

// ─── Shop / Products ──────────────────────────────────────────────────────────
Route::prefix('boutique')->name('products.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/recherche', [ProductController::class, 'search'])->name('search');
    Route::get('/{product:slug}', [ProductController::class, 'show'])->name('show');
});

// ─── Cart ─────────────────────────────────────────────────────────────────────
Route::prefix('panier')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/ajouter/{product:slug}', [CartController::class, 'add'])->name('add');
    Route::patch('/modifier/{cartItem}', [CartController::class, 'update'])->name('update');
    Route::delete('/supprimer/{cartItem}', [CartController::class, 'remove'])->name('remove');
    Route::delete('/vider', [CartController::class, 'clear'])->name('clear');
    Route::get('/count', [CartController::class, 'getCartCount'])->name('count');
});

// ─── Checkout ─────────────────────────────────────────────────────────────────
Route::prefix('commande')->name('checkout.')->middleware('auth')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/passer', [CheckoutController::class, 'store'])->name('store');
    Route::get('/confirmation/{order}', [CheckoutController::class, 'confirmation'])->name('confirmation');
});

// ─── Orders ───────────────────────────────────────────────────────────────────
Route::prefix('mes-commandes')->name('orders.')->middleware('auth')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('index');
    Route::get('/{order}', [OrderController::class, 'show'])->name('show');
    Route::post('/{order}/annuler', [OrderController::class, 'cancel'])->name('cancel');
});

// ─── Reviews ──────────────────────────────────────────────────────────────────
Route::post('/avis/{product:slug}', [ReviewController::class, 'store'])->name('reviews.store')->middleware('auth');

// ─── Tourism ──────────────────────────────────────────────────────────────────
Route::prefix('tourisme')->name('tourism.')->group(function () {
    Route::get('/', [DestinationController::class, 'index'])->name('index');
    Route::get('/carte', [DestinationController::class, 'map'])->name('map');
    Route::get('/{destination:slug}', [DestinationController::class, 'show'])->name('show');
});

// ─── Profile ──────────────────────────────────────────────────────────────────
Route::prefix('profil')->name('profile.')->middleware('auth')->group(function () {
    Route::get('/', [ProfileController::class, 'edit'])->name('edit');
    Route::put('/', [ProfileController::class, 'update'])->name('update');
    Route::post('/devenir-vendeur', [ProfileController::class, 'applyAsSeller'])->name('apply-seller');
    Route::post('/devenir-affilie', [ProfileController::class, 'applyAsAffiliate'])->name('apply-affiliate');
});

// ─── Seller Dashboard ─────────────────────────────────────────────────────────
Route::prefix('vendeur')->name('seller.')->middleware(['auth', 'role:seller'])->group(function () {
    Route::get('/', [SellerDashboard::class, 'index'])->name('dashboard');
    Route::get('/boutique', [SellerProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/boutique', [SellerProfileController::class, 'update'])->name('profile.update');

    Route::get('/produits', [SellerProductController::class, 'index'])->name('products.index');
    Route::get('/produits/ajouter', [SellerProductController::class, 'create'])->name('products.create');
    Route::post('/produits', [SellerProductController::class, 'store'])->name('products.store');
    Route::get('/produits/{product:slug}/modifier', [SellerProductController::class, 'edit'])->name('products.edit');
    Route::put('/produits/{product:slug}', [SellerProductController::class, 'update'])->name('products.update');
    Route::delete('/produits/{product:slug}', [SellerProductController::class, 'destroy'])->name('products.destroy');
    Route::delete('/produits/{product:slug}/image/{image}', [SellerProductController::class, 'destroyImage'])->name('products.destroy-image');

    Route::get('/commandes', [SellerOrderController::class, 'index'])->name('orders.index');
    Route::get('/commandes/{sellerOrder}', [SellerOrderController::class, 'show'])->name('orders.show');
    Route::patch('/commandes/{sellerOrder}/statut', [SellerOrderController::class, 'updateStatus'])->name('orders.update-status');
});

// ─── Affiliate Dashboard ──────────────────────────────────────────────────────
Route::prefix('affilie')->name('affiliate.')->middleware(['auth', 'role:affiliate'])->group(function () {
    Route::get('/', [AffiliateDashboard::class, 'index'])->name('dashboard');
    Route::get('/liens', [AffiliateDashboard::class, 'links'])->name('links');
    Route::get('/statistiques', [AffiliateDashboard::class, 'statistics'])->name('statistics');
    Route::get('/commissions', [CommissionController::class, 'index'])->name('commissions.index');
    Route::get('/profil', [AffiliateProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [AffiliateProfileController::class, 'update'])->name('profile.update');
    Route::get('/paiements', [PayoutController::class, 'index'])->name('payouts.index');
    Route::post('/paiements/demander', [PayoutController::class, 'request'])->name('payouts.request');
});

// ─── Admin Dashboard ──────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [AdminDashboard::class, 'index'])->name('dashboard');

    // Users
    Route::patch('utilisateurs/{user}/statut', [AdminUserController::class, 'updateStatus'])->name('users.status');
    Route::patch('utilisateurs/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.role');
    Route::resource('utilisateurs', AdminUserController::class)->names('users');

    // Sellers
    Route::get('vendeurs', [AdminSellerController::class, 'index'])->name('sellers.index');
    Route::get('vendeurs/candidatures', [AdminSellerController::class, 'applications'])->name('sellers.applications');
    Route::get('vendeurs/{sellerProfile}', [AdminSellerController::class, 'show'])->name('sellers.show');
    Route::patch('vendeurs/{sellerProfile}/approuver', [AdminSellerController::class, 'approve'])->name('sellers.approve');
    Route::patch('vendeurs/{sellerProfile}/rejeter', [AdminSellerController::class, 'reject'])->name('sellers.reject');
    Route::patch('vendeurs/{sellerProfile}/suspendre', [AdminSellerController::class, 'suspend'])->name('sellers.suspend');

    // Affiliates
    Route::get('affilies', [AdminAffiliateController::class, 'index'])->name('affiliates.index');
    Route::get('affilies/candidatures', [AdminAffiliateController::class, 'applications'])->name('affiliates.applications');
    Route::get('affilies/{affiliateProfile}', [AdminAffiliateController::class, 'show'])->name('affiliates.show');
    Route::patch('affilies/{affiliateProfile}/approuver', [AdminAffiliateController::class, 'approve'])->name('affiliates.approve');
    Route::patch('affilies/{affiliateProfile}/rejeter', [AdminAffiliateController::class, 'reject'])->name('affiliates.reject');
    Route::patch('affilies/{affiliateProfile}/suspendre', [AdminAffiliateController::class, 'suspend'])->name('affiliates.suspend');

    // Products
    Route::patch('produits/{product}/approuver', [AdminProductController::class, 'approve'])->name('products.approve');
    Route::patch('produits/{product}/rejeter', [AdminProductController::class, 'reject'])->name('products.reject');
    Route::patch('produits/{product}/vedette', [AdminProductController::class, 'toggleFeatured'])->name('products.featured');
    Route::resource('produits', AdminProductController::class)->names('products');

    // Categories
    Route::resource('categories', AdminCategoryController::class)->names('categories');

    // Orders
    Route::get('commandes', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('commandes/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('commandes/{order}/statut', [AdminOrderController::class, 'updateStatus'])->name('orders.status');

    // Commissions
    Route::get('commissions', [AdminCommissionController::class, 'index'])->name('commissions.index');
    Route::patch('commissions/{commission}/approuver', [AdminCommissionController::class, 'approve'])->name('commissions.approve');
    Route::patch('commissions/{commission}/annuler', [AdminCommissionController::class, 'cancel'])->name('commissions.cancel');

    // Payouts
    Route::get('paiements', [AdminPayoutController::class, 'index'])->name('payouts.index');
    Route::patch('paiements/{payoutRequest}/approuver', [AdminPayoutController::class, 'approve'])->name('payouts.approve');
    Route::patch('paiements/{payoutRequest}/payer', [AdminPayoutController::class, 'markPaid'])->name('payouts.paid');
    Route::patch('paiements/{payoutRequest}/rejeter', [AdminPayoutController::class, 'reject'])->name('payouts.reject');

    // Tourism
    Route::resource('destinations', AdminDestinationController::class)->names('destinations');
    Route::resource('categories-tourisme', TourismCategoryController::class)->names('tourism-categories');

    // Reviews
    Route::get('avis', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::patch('avis/{review}/approuver', [AdminReviewController::class, 'approve'])->name('reviews.approve');
    Route::delete('avis/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

    // Settings
    Route::get('parametres', [SettingController::class, 'index'])->name('settings.index');
    Route::put('parametres', [SettingController::class, 'update'])->name('settings.update');
});
