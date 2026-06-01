<?php

namespace App\Filters;

use App\Services\SystemSubscriptionService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class SubscriptionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): ?ResponseInterface
    {
        $service = new SystemSubscriptionService();
        if (!$service->isEnabled()) {
            return null;
        }

        $path = trim($request->getUri()->getPath(), '/');
        $basePath = trim((string)(parse_url((string)config('App')->baseURL, PHP_URL_PATH) ?? ''), '/');
        if ($basePath !== '' && ($path === $basePath || str_starts_with($path, $basePath . '/'))) {
            $path = trim(substr($path, strlen($basePath)), '/');
        }
        if ($this->isAllowedPath($path)) {
            return null;
        }

        $state = $service->subscriptionState();
        if (!empty($state['blocked'])) {
            return redirect()->route('subscription.index')->with('error', (string)$state['message']);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
        //
    }

    private function isAllowedPath(string $path): bool
    {
        $allowed = [
            'assinatura',
            'assinatura/sincronizar',
            'assinatura/gerar-pix',
            'assinatura/liberar-manual',
            'assinatura/configuracoes',
            'logout',
            'avatar',
            'password/force',
        ];

        foreach ($allowed as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return true;
            }
        }

        return false;
    }
}
