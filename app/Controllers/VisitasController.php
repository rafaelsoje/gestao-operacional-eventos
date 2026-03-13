<?php

namespace App\Controllers;

use App\Models\Logs;
use App\Models\Visita;
use App\Validation\FormRequest\VisitaFormRequest;
use CodeIgniter\HTTP\ResponseInterface;

class VisitasController extends Home
{
    /**
     * Exibe o formulário e a lista das 100 visitas mais recentes.
     */
    public function visitas(): string
    {
        return view('visitas', [
            'visitasPage' => true,
            'data' => $this->loadRecentVisitas(),
        ]);
    }

    /**
     * Carrega uma visita para edição no formulário principal.
     */
    public function updateVisita($id): string|ResponseInterface
    {
        if (!$this->isAdminUser()) {
            return redirect()->route('visitas')->with('error', 'Apenas administradores podem editar visitas.');
        }

        $id = (int)$id;
        if ($id <= 0) {
            return redirect()->route('visitas')->with('error', 'Parametro invalido para edicao da visita.');
        }

        $model = new Visita();
        $item = $model->find($id);
        if (!$item) {
            return redirect()->route('visitas')->with('error', 'Visita nao encontrada para edicao.');
        }

        return view('visitas', [
            'visitasPage' => true,
            'item' => $item,
            'data' => $this->loadRecentVisitas(),
        ]);
    }

    /**
     * Cria ou atualiza uma visita com validação centralizada.
     */
    public function visitasStore(): ResponseInterface
    {
        $post = $this->request->getPost();
        $id = (int)($post['id'] ?? 0);

        if ($id > 0 && !$this->isAdminUser()) {
            return redirect()->route('visitas')->with('error', 'Apenas administradores podem atualizar visitas.');
        }

        $payloadInput = [
            'id' => $id,
            'data_visita' => trim((string)($post['data_visita'] ?? '')),
            'tipo_visita' => trim((string)($post['tipo_visita'] ?? '')),
            'responsavel_exc' => trim((string)($post['responsavel_exc'] ?? '')),
            'cliente' => trim((string)($post['cliente'] ?? '')),
            'evento' => trim((string)($post['evento'] ?? '')),
            'data_evento' => trim((string)($post['data_evento'] ?? '')),
            'horario_inicio' => trim((string)($post['horario_inicio'] ?? '')),
            'horario_termino' => trim((string)($post['horario_termino'] ?? '')),
            'publico_estimado' => trim((string)($post['publico_estimado'] ?? '')),
            'objetivo_visita' => trim((string)($post['objetivo_visita'] ?? '')),
        ];

        $rules = VisitaFormRequest::rules($id > 0);
        if (!$this->validateData($payloadInput, $rules, VisitaFormRequest::messages())) {
            return redirect()->back()->withInput()->with('error', VisitaFormRequest::firstError($this->validator));
        }

        $publicoEstimado = (int)$payloadInput['publico_estimado'];

        $model = new Visita();
        $payload = [
            'data_visita' => $payloadInput['data_visita'],
            'tipo_visita' => $payloadInput['tipo_visita'],
            'responsavel_exc' => $payloadInput['responsavel_exc'],
            'cliente' => $payloadInput['cliente'],
            'evento' => $payloadInput['evento'],
            'data_evento' => $payloadInput['data_evento'],
            'horario_inicio' => $payloadInput['horario_inicio'] . ':00',
            'horario_termino' => $payloadInput['horario_termino'] . ':00',
            'publico_estimado' => $publicoEstimado,
            'objetivo_visita' => $payloadInput['objetivo_visita'],
        ];

        $saved = false;
        if ($id > 0) {
            $existing = $model->find($id);
            if (!$existing) {
                return redirect()->route('visitas')->with('error', 'Visita nao encontrada para atualizacao.');
            }
            $saved = $model->update($id, $payload);
        } else {
            $saved = $model->insert($payload);
        }

        if (!$saved) {
            return redirect()->back()->withInput()->with('error', 'Nao foi possivel salvar a visita.');
        }

        $user = session()->get('user');
        if ($id > 0) {
            (new Logs())->insert([
                'title' => 'ATUALIZACAO VISITA',
                'description' => ($user->name ?? 'Usuario') . " atualizou visita #{$id}: {$payloadInput['evento']} ({$payloadInput['cliente']}) em " . date('Y-m-d H:i:s'),
            ]);
            return redirect()->route('visitas')->with('success', 'Visita atualizada com sucesso!');
        }

        (new Logs())->insert([
            'title' => 'CADASTRO VISITA',
            'description' => ($user->name ?? 'Usuario') . " cadastrou visita: {$payloadInput['evento']} ({$payloadInput['cliente']}) em " . date('Y-m-d H:i:s'),
        ]);

        return redirect()->route('visitas')->with('success', 'Visita cadastrada com sucesso!');
    }

    /**
     * Remove uma visita existente e registra auditoria em logs.
     */
    public function deleteVisita(): ResponseInterface
    {
        if (!$this->isAdminUser()) {
            return redirect()->route('visitas')->with('error', 'Apenas administradores podem excluir visitas.');
        }

        $id = (int)($this->request->getPost('id') ?? 0);
        if ($id <= 0) {
            return redirect()->route('visitas')->with('error', 'Registro invalido para exclusao.');
        }

        $model = new Visita();
        $item = $model->find($id);
        if (!$item) {
            return redirect()->route('visitas')->with('error', 'Visita nao encontrada para exclusao.');
        }

        if (!$model->delete($id)) {
            return redirect()->route('visitas')->with('error', 'Nao foi possivel excluir a visita.');
        }

        $user = session()->get('user');
        (new Logs())->insert([
            'title' => 'EXCLUSAO VISITA',
            'description' => ($user->name ?? 'Usuario') . " excluiu visita #{$id}: " . ($item->evento ?? 'Sem evento') . '.',
        ]);

        return redirect()->route('visitas')->with('success', 'Visita excluida com sucesso.');
    }

    /**
     * Retorna a listagem limitada para manter a tabela responsiva.
     */
    protected function loadRecentVisitas(): array
    {
        return (new Visita())
            ->orderBy('data_visita', 'desc')
            ->orderBy('id', 'desc')
            ->limit(100)
            ->findAll();
    }
}
