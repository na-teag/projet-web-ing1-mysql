<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ContactController;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {return view('application', [
	"page_to_load" => "home",
	"title" => "Accueil"
]);});

Route::get('/login', function () {return view('application', [
	"page_to_load" => "login",
	"title" => "Connexion"
]);});

Route::get('/contact', function () {return view('application', [
    "page_to_load" => "contact",
    "title" => "Nous contacter"
]);});

Route::get('/profile', function () {return view('application', [
	"page_to_load" => "profile",
	"title" => "Votre profil"
]);})->middleware("auth");

Route::get('/about', function () {return view('application', [
	"page_to_load" => "about",
	"title" => "À propos"
]);});

Route::get('/cart', function () {return view('application', [
    "page_to_load" => "cart",
    "title" => "Mon panier"
]);})->middleware("auth");

Route::get('/catalog', function () {
    return view('application', [
        "page_to_load" => "catalog",
        "title" => "Produits",
        "category_name" => Category::find(Request::get("category", 12))->name
]);});
Route::get('/registered', function () {
    return view('application', [
        "page_to_load" => "registered",
        "title" => "Confirmez votre email"
    ]);
})->name('verification.notice');

Route::get('/reset-sent', function () {
    return view('application', [
        "page_to_load" => "reset-sent",
        "title" => "Demande envoyée"
    ]);
});

Route::get('/forgot-password', function () {
    return view('application', [
        "page_to_load" => "password-forgot",
        "title" => "Réinitialiser son mot de passe"
    ]);
});

Route::get('/reset-password/{token}', function ($token) {
    return view('application', [
        "page_to_load" => "password-reset",
        "title" => "Réinitialiser son mot de passe",
        "token" => $token
    ]);
})->name('password.reset');

Route::fallback(function () {return view('application', ["page_to_load" => "error", "title" => "Erreur"]);});

// Admin-only
Route::get('/admin', function () {return view('application', [
	"page_to_load" => "admin",
	"title" => "Panel Admin"
]);})->middleware("admin");
Route::get('/admin/products', function () {return view('application', [
	"page_to_load" => "admin_products",
	"title" => "Gestion produits"
]);})->middleware("admin");
Route::get('/admin/users', function () {return view('application', [
	"page_to_load" => "admin_users",
	"title" => "Gestion utilisateurs"
]);})->middleware("admin");
Route::get('/admin/contacts', function () {return view('application', [
    "page_to_load" => "admin_contacts",
    "title" => "Gestion formulaires de contact"
]);})->middleware("admin");


Route::post("/auth/login", [AuthController::class, "login"]);
Route::get("/auth/logout", [AuthController::class, "logout"]);
Route::post("/auth/register", [AuthController::class, "register"]);
Route::post("/auth/password-request", [AuthController::class, "passwordRequest"]);
Route::post("/auth/update", [AuthController::class, "update"])->middleware("auth");
Route::get("/auth/verify/{id}/{hash}", [AuthController::class, "verifyEmail"])
    ->middleware(["auth", "signed"])
    ->name("verification.verify");

Route::post("/auth/reset-password", [AuthController::class, "resetPassword"]);

Route::post("/cart/add", [CartController::class, "add"]);
Route::post("/cart/remove", [CartController::class, "remove"]);
Route::post("/cart/delete", [CartController::class, "delete"])->middleware("auth");
Route::post("/cart/clear", [CartController::class, "clear"])->middleware("auth");
Route::post("/cart/buy", [CartController::class, "buy"])->middleware(["auth", "verified"]);
Route::get("/cart/get", [CartController::class, "getCart"])->middleware("auth");

Route::get("/catalog/products", [CatalogController::class, "getProducts"]);

Route::post("/contact/create", [ContactController::class, "create"]);
Route::post("/contact/delete", [ContactController::class, "delete"])->middleware(["admin", "verified"]);

Route::post("/admin/category/get", [AdminController::class, "getCategories"])->middleware(["admin", "verified"]);
Route::post("/admin/category/add", [AdminController::class, "addCategory"])->middleware(["admin", "verified"]);
Route::post("/admin/category/remove", [AdminController::class, "removeCategory"])->middleware(["admin", "verified"]);
Route::post("/admin/category/update", [AdminController::class, "updateCategory"])->middleware(["admin", "verified"]);
Route::post("/admin/product/add", [AdminController::class, "addProduct"])->middleware(["admin", "verified"]);
Route::post("/admin/product/remove", [AdminController::class, "removeProduct"])->middleware(["admin", "verified"]);
Route::post("/admin/product/update", [AdminController::class, "updateProduct"])->middleware(["admin", "verified"]);
Route::post("/admin/product/get", [AdminController::class, "getProducts"])->middleware(["admin", "verified"]);
Route::post("/admin/user/get", [AdminController::class, "getUsers"])->middleware(["admin", "verified"]);
Route::post("/admin/user/add", [AdminController::class, "addUser"])->middleware(["admin", "verified"]);
Route::post("/admin/user/remove", [AdminController::class, "removeUser"])->middleware(["admin", "verified"]);
Route::post("/admin/user/resetPassword", [AdminController::class, "resetPassword"])->middleware(["admin", "verified"]);
Route::post("/admin/contact/get", [AdminController::class, "getContacts"])->middleware(["admin", "verified"]);
Route::post("/admin/contact/reply", [AdminController::class, "replyContact"])->middleware(["admin", "verified"]);
Route::post("/admin/contact/remove", [AdminController::class, "removeContact"])->middleware(["admin", "verified"]);
