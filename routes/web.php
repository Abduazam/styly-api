<?php

use App\Http\Controllers\AiGenerateController;
use App\Http\Controllers\ClothesController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MeController;
use App\Http\Controllers\OutfitController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => 'OK');

Route::post('api/login', LoginController::class)->name('login');

\Illuminate\Support\Facades\Auth::loginUsingId(1);
Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('me', MeController::class)->name('me');

    Route::prefix('collections')->group(function () {
        Route::get('/', CollectionController::class)->name('home');

        Route::prefix('wardrobe')->controller(ClothesController::class)->group(function () {
            Route::get('/', 'index')->name('wardrobe.index');
            Route::post('/', 'store')->name('wardrobe.store');
            Route::get('{id}', 'show')->name('wardrobe.show');
            Route::delete('{id}', 'destroy')->name('wardrobe.destroy');
        });

        Route::prefix('outfits')->group(function () {
            Route::get('/', [OutfitController::class, 'index'])->name('outfits');
            Route::post('store', [OutfitController::class, 'store'])->name('outfits.store');
            Route::get('{id}', [OutfitController::class, 'show'])->name('outfits.show');
            Route::delete('{outfit}', [OutfitController::class, 'destroy'])->name('outfits.destroy');
        });
    });

    Route::prefix('ai')->group(function () {
        Route::get('get-clothes', fn () => 'My Clothes in Wardrobe')->name('get-clothes');
        Route::get('get-occasion', fn () => 'Occasions')->name('get-occasion');

        Route::post('generate', AiGenerateController::class)->name('generate');
    });
});

Route::post('test', [ClothesController::class, 'store'])->name('test');
