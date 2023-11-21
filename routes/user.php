<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\User\SliderController;
use App\Http\Controllers\User\CategoryController;
use App\Http\Controllers\User\ProductController;

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

Route::prefix('user')->group(function () {
        // ** Slider
        Route::controller(SliderController::class)
            ->prefix('slider')
            ->group(function () {
                // ** Data List
                Route::get('data-list', 'dataList');
            });

        // ** Category
        Route::controller(CategoryController::class)
            ->prefix('category')
            ->group(function () {
                // ** Data List
                Route::get('data-list', 'dataList');
                Route::get('data-list-nested', 'dataListNested');

                // ** Details
                Route::get('{slug}', 'dataDetail');
            });

        // ** Product
        Route::controller(ProductController::class)
            ->prefix('product')
            ->group(function () {
                // ** Details
                Route::get('{slug}', 'dataDetail');
            });
});
