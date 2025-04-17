<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Api\Controllers\ProductController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::get('/', function() {
    return response()->json("Catalogo Coimaf");
});

Route::get('/products', [ProductController::class, 'index']);