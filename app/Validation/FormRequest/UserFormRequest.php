<?php

namespace App\Validation\FormRequest;

use CodeIgniter\Validation\Validation;

final class UserFormRequest
{
    public static function storeRules(): array
    {
        return [
            'name' => 'required|min_length[3]|max_length[120]',
            'email' => 'required|valid_email|max_length[190]',
            'role' => 'required|in_list[user,admin]',
        ];
    }

    public static function updateRules(): array
    {
        return [
            'id' => 'required|is_natural_no_zero',
            'name' => 'required|min_length[3]|max_length[120]',
            'role' => 'required|in_list[user,admin]',
        ];
    }

    public static function messages(): array
    {
        return [
            'name' => ['required' => 'Informe o nome do usuário.'],
            'email' => [
                'required' => 'Informe o e-mail do usuário.',
                'valid_email' => 'Informe um e-mail válido.',
            ],
            'role' => [
                'required' => 'Informe o perfil do usuário.',
                'in_list' => 'Perfil inválido para o usuário.',
            ],
            'id' => [
                'required' => 'Identificador do usuário é obrigatório.',
                'is_natural_no_zero' => 'Identificador do usuário inválido.',
            ],
        ];
    }

    public static function firstError(?Validation $validator): string
    {
        if ($validator === null) {
            return 'Preencha o formulário corretamente.';
        }

        $errors = $validator->getErrors();
        if (empty($errors)) {
            return 'Preencha o formulário corretamente.';
        }

        return (string) array_shift($errors);
    }
}
