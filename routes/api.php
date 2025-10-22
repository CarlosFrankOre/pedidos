<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\PedidoController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('usuarios')->group(function () {
    Route::post('store', [UsuarioController::class, 'store']);
    // Ruta para obtener pedidos de un usuario (ej: GET /api/usuarios/2/pedidos)
    Route::get('{id}/pedidos', [UsuarioController::class, 'pedidos']);
    // Buscar usuarios por inicial:
    // - GET /api/usuarios/inicial/R
    Route::get('inicial/{letra}', [UsuarioController::class, 'buscarPorInicial']);
    // Conteo de pedidos (GET /api/usuarios/{id}/pedidos/count)
    Route::get('/pedidoscount', [UsuarioController::class, 'pedidosCount']);
    
});

Route::prefix('pedidos')->group(function () {
    Route::post('store', [PedidoController::class, 'store']);
    // Lista todos los pedidos con datos del usuario (GET /api/pedidos)
    Route::get('/', [PedidoController::class, 'index']);
    // Pedidos por rango de total (GET /api/pedidos/rango?min=100&max=250)
    Route::get('rango', [PedidoController::class, 'rango']);
    // Pedidos Ordenados (GET /api/pedidos/pedidosOrdenados)
    Route::get('pedidosOrdenados', [PedidoController::class, 'pedidosOrdenados']);
    // Suma total de todos los pedidos
    Route::get('total', [PedidoController::class, 'totalSuma']);
    // Pedido más económico
    Route::get('masbarato', [PedidoController::class, 'pedidoMasEconomico']);
    // Pedidos agrupados por usuario (producto, cantidad, total)
    Route::get('agrupadoUsuario', [PedidoController::class, 'agrupadoPorUsuario']);
});