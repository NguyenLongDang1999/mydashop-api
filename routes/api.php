<?php

use App\Http\Controllers\Admins\FlashSaleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ** Admins
use App\Http\Controllers\Admins\AuthController;
use App\Http\Controllers\Admins\CategoryController;
use App\Http\Controllers\Admins\BrandController;
use App\Http\Controllers\Admins\AttributeController;
use App\Http\Controllers\Admins\ProductController;
use App\Http\Controllers\Admins\SliderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// ** Admins
Route::prefix('admin')->group(function () {
        // ** Authentication
        Route::controller(AuthController::class)
            ->prefix('auth')
            ->group(function () {
                Route::post('sign-in', 'signIn');

                Route::middleware('auth:sanctum')->group(function () {
                    Route::get('sign-out', 'signOut');
                });
            });

        Route::middleware('auth:sanctum')->group(function () {
            // ** Category
            Route::controller(CategoryController::class)
                ->prefix('category')
                ->group(function () {
                    // ** Data List
                    Route::get('/', 'index');
                    Route::get('data-list', 'dataList');

                    // ** Store
                    Route::post('/', 'store');

                    // ** Update
                    Route::get('{id}', 'show');
                    Route::patch('{id}', 'update');

                    // ** Remove
                    Route::patch('remove/{id}', 'remove');
                });

            // ** Brand
            Route::controller(BrandController::class)
                ->prefix('brand')
                ->group(function () {
                    // ** Data List
                    Route::get('/', 'index');
                    Route::get('data-list', 'dataList');
                    Route::get('data-list-category/{id}', 'dataListCategory');

                    // ** Store
                    Route::post('/', 'store');

                    // ** Update
                    Route::get('{id}', 'show');
                    Route::patch('{id}', 'update');

                    // ** Remove
                    Route::patch('remove/{id}', 'remove');
                });

            // ** Attribute
            Route::controller(AttributeController::class)
                ->prefix('attribute')
                ->group(function () {
                    // ** Data List
                    Route::get('/', 'index');
                    Route::get('data-list', 'dataList');
                    Route::get('data-list-category/{id}', 'dataListCategory');
                    Route::get('attribute-value-data-list/{id}', 'dataListAttributeValues');

                    // ** Store
                    Route::post('/', 'store');

                    // ** Update
                    Route::get('{id}', 'show');
                    Route::patch('{id}', 'update');

                    // ** Remove
                    Route::patch('remove/{id}', 'remove');
                });

            // ** Flash Sale
            Route::controller(FlashSaleController::class)
                ->prefix('flash-sale')
                ->group(function () {
                    // ** Data List
                    Route::get('/', 'index');

                    // ** Store
                    Route::post('/', 'store');

                    // ** Update
                    Route::get('{id}', 'show');
                    Route::patch('{id}', 'update');

                    // ** Remove
                    Route::patch('remove/{id}', 'remove');
                });

            // ** Product
            Route::controller(ProductController::class)
                ->prefix('product')
                ->group(function () {
                    // ** Data List
                    Route::get('/', 'index');
                    Route::get('data-list', 'dataList');

                    // ** Store
                    Route::post('/', 'store');

                    // ** Update
                    Route::get('{id}', 'show');
                    Route::patch('{id}', 'update');

                    // ** Remove
                    Route::patch('remove/{id}', 'remove');
                });

            // ** Slider
            Route::controller(SliderController::class)
                ->prefix('slider')
                ->group(function () {
                    // ** Data List
                    Route::get('/', 'index');

                    // ** Store
                    Route::post('/', 'store');

                    // ** Update
                    Route::patch('{id}', 'update');

                    // ** Remove
                    Route::patch('remove/{id}', 'remove');
                });
        });
});
