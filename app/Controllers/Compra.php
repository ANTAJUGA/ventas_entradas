<?php

namespace App\Controllers;

use App\Services\CompraService;
use DomainException;
use Throwable;

class Compra extends BaseController
{
    public function seleccionar(int $id): mixed
    {
        try {
            $o = (new CompraService())->opcion($id);
        } catch (DomainException $e) {
            return redirect()->to(base_url('/'))->with('error', $e->getMessage());
        }
        return view('public/compra/seleccionar', ['titulo' => 'Seleccionar entradas', 'opcion' => $o]);
    }
    public function revisar(int $id): mixed
    {
        try {
            $r = (new CompraService())->preview($id, (int)$this->request->getPost('cantidad'), trim((string)$this->request->getPost('codigo_descuento')) ?: null);
        } catch (DomainException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
        return view('public/compra/resumen', ['titulo' => 'Revisar compra', 'resumen' => $r]);
    }
    public function confirmar(): mixed
    {
        try {
            $r = (new CompraService())->comprar((int)$this->request->getPost('tipo_entrada_id'), (int)$this->request->getPost('cantidad'), trim((string)$this->request->getPost('codigo_descuento')) ?: null, session('usuario'));
        } catch (DomainException $e) {
            return redirect()->to(base_url('/'))->with('error', $e->getMessage());
        } catch (Throwable $e) {
            log_message('error', 'Error de compra: {message}', ['message' => $e->getMessage()]);
            return redirect()->to(base_url('/'))->with('error', 'No se pudo completar la compra.');
        }
        return redirect()->to(base_url('mi-cuenta/entradas'))->with('success', "Compra {$r['codigo']} confirmada. Se generaron {$r['entradas']} entrada(s).");
    }

    public function reservar(): mixed
    {
        try {
            $r = (new CompraService())->reservar((int) $this->request->getPost('tipo_entrada_id'), (int) $this->request->getPost('cantidad'), trim((string) $this->request->getPost('codigo_descuento')) ?: null, session('usuario'));
        } catch (DomainException $e) {
            return redirect()->to(base_url('/'))->with('error', $e->getMessage());
        } catch (Throwable $e) {
            log_message('error', 'Error de reserva: {message}', ['message' => $e->getMessage()]);
            return redirect()->to(base_url('/'))->with('error', 'No se pudo registrar la reserva.');
        }
        return redirect()->to(base_url('mi-cuenta/entradas'))->with('success', "Reserva {$r['codigo']} creada. Presenta ese código y paga en ventanilla para recibir tus entradas.");
    }
}
