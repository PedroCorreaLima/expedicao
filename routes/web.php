<?php

use App\Http\Controllers\PedidoController;
Route::redirect('/', '/embalagem');
Route::get('/embalagem', [PedidoController::class, 'index'])->name('embalagem.index');
Route::post('/embalagem/start/{id}', [PedidoController::class, 'start'])->name('embalagem.start');
Route::post('/embalagem/stop/{id}', [PedidoController::class, 'stop'])->name('embalagem.stop');
Route::post('/embalagem/reiniciar/{id}', [PedidoController::class, 'reiniciar'])->name('embalagem.reiniciar');
Route::post('/pedidos/atualizar', [PedidoController::class, 'atualizarPedidos'])->name('pedidos.atualizar');
Route::get('/embalados', [PedidoController::class, 'embalados'])->name('embalagem.embalados');
Route::post('/embalagem/{pedido}/valor', [PedidoController::class, 'atualizarValor'])->name('embalagem.valor');