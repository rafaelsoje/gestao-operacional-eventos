<?php

namespace App\Validation\FormRequest;

use CodeIgniter\Validation\Validation;

final class VisitaFormRequest
{
    public static function rules(bool $isUpdate = false): array
    {
        $rules = [
            'data_visita' => 'required|valid_date[Y-m-d]',
            'tipo_visita' => 'required|min_length[2]|max_length[120]',
            'responsavel_exc' => 'required|min_length[2]|max_length[120]',
            'cliente' => 'required|min_length[2]|max_length[190]',
            'evento' => 'required|min_length[2]|max_length[190]',
            'data_evento' => 'required|valid_date[Y-m-d]',
            'horario_inicio' => 'required|regex_match[/^\d{2}:\d{2}$/]',
            'horario_termino' => 'required|regex_match[/^\d{2}:\d{2}$/]',
            'publico_estimado' => 'required|is_natural',
            'objetivo_visita' => 'required|min_length[3]',
        ];

        if ($isUpdate) {
            $rules['id'] = 'required|is_natural_no_zero';
        }

        return $rules;
    }

    public static function messages(): array
    {
        return [
            'data_visita' => [
                'required' => 'Informe a data da visita.',
                'valid_date' => 'Data da visita inválida. Use o formato AAAA-MM-DD.',
            ],
            'tipo_visita' => ['required' => 'Informe o tipo de visita.'],
            'responsavel_exc' => ['required' => 'Informe o responsável (EXC).'],
            'cliente' => ['required' => 'Informe o cliente.'],
            'evento' => ['required' => 'Informe o evento.'],
            'data_evento' => [
                'required' => 'Informe a data do evento.',
                'valid_date' => 'Data do evento inválida. Use o formato AAAA-MM-DD.',
            ],
            'horario_inicio' => [
                'required' => 'Informe o horário de início.',
                'regex_match' => 'Horário de início inválido. Use HH:MM.',
            ],
            'horario_termino' => [
                'required' => 'Informe o horário de término.',
                'regex_match' => 'Horário de término inválido. Use HH:MM.',
            ],
            'publico_estimado' => [
                'required' => 'Informe o público estimado.',
                'is_natural' => 'Público estimado deve ser um número inteiro.',
            ],
            'objetivo_visita' => ['required' => 'Informe o objetivo da visita.'],
            'id' => [
                'required' => 'Identificador da visita é obrigatório para atualização.',
                'is_natural_no_zero' => 'Identificador da visita inválido.',
            ],
        ];
    }

    public static function firstError(?Validation $validator): string
    {
        if ($validator === null) {
            return 'Dados inválidos para salvar visita.';
        }

        $errors = $validator->getErrors();
        if (empty($errors)) {
            return 'Dados inválidos para salvar visita.';
        }

        return (string) array_shift($errors);
    }
}
