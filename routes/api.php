<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('books', BookController::class);
Route::apiResource('categories', CategoryController::class);

Route::get('categories/{id}/books', [CategoryController::class, 'listBooksByCategory']);
Route::get('search', [BookController::class, 'searchBooks']);

Route::get('/teste', function () {
    return 'teste';
});