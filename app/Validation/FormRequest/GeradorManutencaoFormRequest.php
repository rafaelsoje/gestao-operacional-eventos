<?php

namespace App\Validation\FormRequest;

use CodeIgniter\Validation\Validation;

final class GeradorManutencaoFormRequest
{
    public static function storeRules(): array
    {
        return [
            'gerador' => 'required|in_list[G1,G2]',
            'data_manutencao' => 'required|valid_date[Y-m-d]',
            'observacao' => 'permit_empty|max_length[500]',
        ];
    }

    public static function updateRules(): array
    {
        return [
            'id' => 'required|is_natural_no_zero',
            'gerador' => 'required|in_list[G1,G2]',
            'data_manutencao' => 'required|valid_date[Y-m-d]',
            'observacao' => 'permit_empty|max_length[500]',
        ];
    }

    public static function messages(): array
    {
        return [
            'id' => [
                'required' => 'Registro de manutenção inválido para atualização.',
                'is_natural_no_zero' => 'Registro de manutenção inválido para atualização.',
            ],
            'gerador' => [
                'required' => 'Informe o gerador.',
                'in_list' => 'Gerador inválido para manutenção.',
            ],
            'data_manutencao' => [
                'required' => 'Informe a data da manutenção.',
                'valid_date' => 'Data de manutenção inválida. Use AAAA-MM-DD.',
            ],
        ];
    }

    public static function firstError(?Validation $validator): string
    {
        if ($validator === null) {
            return 'Dados inválidos para manutenção do gerador.';
        }

        $errors = $validator->getErrors();
        if (empty($errors)) {
            return 'Dados inválidos para manutenção do gerador.';
        }

        return (string) array_shift($errors);
    }
}
