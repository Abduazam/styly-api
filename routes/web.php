<?php

use App\Http\Controllers\AI\AiGenerateController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\MeController;
use App\Http\Controllers\Collection\ClothesController;
use App\Http\Controllers\Collection\OutfitController;
use App\Http\Controllers\Collection\WearController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Market\MarketController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => 'OK');

Route::post('api/login', LoginController::class)->name('login');

\Illuminate\Support\Facades\Auth::loginUsingId(1);
Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('me', MeController::class)->name('me');

    Route::prefix('dashboard')->group(function () {
        Route::get('/', DashboardController::class)->name('home');
    });

    Route::prefix('wardrobe')->controller(ClothesController::class)->group(function () {
        Route::get('/', 'index')->name('wardrobe.index');
        Route::post('/', 'store')->name('wardrobe.store');
        Route::get('{id}', 'show')->name('wardrobe.show');
        Route::delete('{id}', 'destroy')->name('wardrobe.destroy');
    });

    Route::prefix('outfits')->controller(OutfitController::class)->group(function () {
        Route::get('/', 'index')->name('outfits');
        Route::post('store', 'store')->name('outfits.store');
        Route::post('{id}', 'show')->name('outfits.show');
        Route::delete('delete', 'destroy')->name('outfits.delete');
    });

    Route::prefix('wears')->controller(WearController::class)->group(function () {
        Route::get('/', 'index')->name('wears');
        Route::post('store', 'store')->name('wears.store');
        Route::post('{id}', 'show')->name('wears.show');
        Route::delete('delete', 'destroy')->name('wears.delete');
    });

    Route::prefix('ai')->group(function () {
        Route::post('generate', AiGenerateController::class)->name('generate');
        Route::post('wear-me', fn () => 'Wear Me')->name('generate');
    });

    Route::prefix('market')->group(function () {
        Route::get('/', MarketController::class)->name('market');
    });
});

Route::post('test', [ClothesController::class, 'store'])->name('test');
