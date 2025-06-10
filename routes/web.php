<?php

use App\Http\Controllers\PedidoController;
Route::redirect('/', '/embalagem');
Route::get('/embalagem', [PedidoController::class, 'index'])->name('embalagem.index');
Route::post('/embalagem/start/{id}', [PedidoController::class, 'start'])->name('embalagem.start');
Route::post('/embalagem/stop/{id}', [PedidoController::class, 'stop'])->name('embalagem.stop');
Route::post('/embalagem/reiniciar/{id}', [PedidoController::class, 'reiniciar'])->name('embalagem.reiniciar');
