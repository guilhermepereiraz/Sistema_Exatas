<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\PepsController;

/*
|--------------------------------------------------------------------------
| Rotas de API para PEPS
|--------------------------------------------------------------------------
|
| Adicione estas rotas no arquivo routes/api.php dentro do grupo v1
|
*/

// Rotas de PEPS
Route::middleware('auth:api')->group(function () {
    // Upload de arquivo PEPS
    Route::post('/peps/upload', [PepsController::class, 'upload']);
    
    // Listar PEPS com paginação
    Route::get('/peps', [PepsController::class, 'index']);
});

