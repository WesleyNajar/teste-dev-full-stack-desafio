<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\UsuarioProdutoController;
use App\Http\Controllers\RelatorioController;

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

// Rota de teste
Route::get('/test', function () {
    return response()->json([
        'message' => 'API funcionando!',
        'timestamp' => now()
    ]);
});

// Rotas de usuários
Route::prefix('usuarios')->group(function () {
    Route::get('/', [UsuarioController::class, 'index']);
    Route::get('/{id}', [UsuarioController::class, 'show']);
    Route::post('/', [UsuarioController::class, 'store']);
    Route::put('/{id}', [UsuarioController::class, 'update']);
    Route::delete('/{id}', [UsuarioController::class, 'destroy']);
});

// Rotas de produtos
Route::prefix('produtos')->group(function () {
    Route::get('/', [ProdutoController::class, 'index']);
    Route::get('/{id}', [ProdutoController::class, 'show']);
    Route::post('/', [ProdutoController::class, 'store']);
    Route::put('/{id}', [ProdutoController::class, 'update']);
    Route::delete('/{id}', [ProdutoController::class, 'destroy']);
});

// Rotas de relacionamentos
Route::prefix('relacionamentos')->group(function () {
    Route::get('/', [UsuarioProdutoController::class, 'index']);
    Route::post('/', [UsuarioProdutoController::class, 'store']);
    Route::delete('/{id}', [UsuarioProdutoController::class, 'destroy']);
    
    Route::get('/usuario/{usuarioId}/produtos', [UsuarioProdutoController::class, 'produtosPorUsuario']);
    Route::get('/produto/{produtoId}/usuarios', [UsuarioProdutoController::class, 'usuariosPorProduto']);
});

// Rotas de relatórios
Route::prefix('relatorios')->group(function () {
    Route::get('/usuarios-mais-produtos', [RelatorioController::class, 'usuariosComMaisProdutos']);
    Route::get('/produto-mais-caro-por-usuario', [RelatorioController::class, 'produtoMaisCaroPorUsuario']);
    Route::get('/produtos-por-faixa-preco', [RelatorioController::class, 'produtosPorFaixaPreco']);
});
