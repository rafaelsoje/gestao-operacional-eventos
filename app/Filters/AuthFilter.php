<?php

namespace App\Filters;

use App\Models\Users;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null): ?ResponseInterface
    {
        if (!session()->get('auth')) {
            return redirect()->route('login')->with('error', 'Sessão expirada ou não autenticada.');
        }

        $user = session()->get('user');
        $current = !empty($user->id) ? (new Users())->find($user->id) : null;
        $fingerprint = session()->get('password_fingerprint');
        // Sessions created before this check must authenticate again.
        if (!$current || !is_string($fingerprint) || empty($current->password)
            || !hash_equals(hash('sha256', $current->password), $fingerprint)) {
            session()->remove(['auth', 'user', 'admin', 'password_fingerprint']);
            session()->destroy();
            return redirect()->route('login')->with('error', 'Sessão expirada ou não autenticada.');
        }

        $superEmail = env('SUPERUSER_EMAIL', env('TELEGRAM_ADMIN_EMAIL', ''));
        $current->role = $superEmail !== '' && $current->email === $superEmail
            ? 'superuser'
            : (($current->role ?? '') ?: ((int)($current->admin ?? 0) === 1 ? 'admin' : 'user'));
        unset($current->password);
        session()->set('user', $current);
        if (in_array($current->role, ['admin', 'superuser'], true)) {
            session()->set('admin', true);
        } else {
            session()->remove('admin');
        }

        $path = trim($request->getUri()->getPath(), '/');
        $basePath = trim((string)parse_url(config('App')->baseURL, PHP_URL_PATH), '/');
        if ($basePath !== '' && str_starts_with($path, $basePath . '/')) {
            $path = substr($path, strlen($basePath) + 1);
        }
        if (!empty($current->must_change_password) && $path !== 'password/force') {
            return redirect()->route('password.force')->with('warning', 'Defina uma nova senha para continuar.');
        }

        return null;
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
        //
    }
}
