<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->has('auth')) {
            return redirect()->route('login')->with('error', 'Sessão expirada ou não autenticada.');
        }

        $user = session()->get('user');
        if (!$user) {
            return redirect()->route('login')->with('error', 'Sessão expirada ou não autenticada.');
        }

        $required = $arguments[0] ?? 'user';
        $role = $this->resolveRole($user);

        if (!$this->isAllowed($role, $required)) {
            return redirect()->route('home')->with('error', 'Acesso negado.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }

    private function resolveRole($user): string
    {
        $superEmail = env('SUPERUSER_EMAIL', env('TELEGRAM_ADMIN_EMAIL', ''));
        if (!empty($superEmail) && isset($user->email) && $user->email === $superEmail) {
            return 'superuser';
        }
        if (!empty($user->role)) {
            return (string) $user->role;
        }
        if (isset($user->admin) && (int) $user->admin === 1) {
            return 'admin';
        }
        return 'user';
    }

    private function isAllowed(string $role, string $required): bool
    {
        $order = [
            'user' => 1,
            'admin' => 2,
            'superuser' => 3,
        ];

        $roleLevel = $order[$role] ?? 0;
        $requiredLevel = $order[$required] ?? 0;

        return $roleLevel >= $requiredLevel;
    }
}
