<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): ?ResponseInterface
    {
        $isJson = (method_exists($request, 'isAJAX') && $request->isAJAX())
            || str_contains((string)$request->getHeaderLine('Accept'), 'application/json');

        if (!session()->has('auth')) {
            if ($isJson) {
                return service('response')->setStatusCode(401)->setJSON(['error' => 'Sessão expirada ou não autenticada.']);
            }
            return redirect()->route('login')->with('error', 'Sessão expirada ou não autenticada.');
        }

        $user = session()->get('user');
        if (!$user) {
            if ($isJson) {
                return service('response')->setStatusCode(401)->setJSON(['error' => 'Sessão expirada ou não autenticada.']);
            }
            return redirect()->route('login')->with('error', 'Sessão expirada ou não autenticada.');
        }

        $required = $arguments[0] ?? 'user';
        $role = $this->resolveRole($user);

        if (!$this->isAllowed($role, $required)) {
            if ($isJson) {
                return service('response')->setStatusCode(403)->setJSON(['error' => 'Acesso negado.']);
            }
            return redirect()->route('home')->with('error', 'Acesso negado.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
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
        $requiredLevel = $order[$required] ?? PHP_INT_MAX;

        return $roleLevel >= $requiredLevel;
    }
}
