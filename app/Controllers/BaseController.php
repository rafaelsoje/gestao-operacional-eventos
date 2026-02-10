<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = [];

    protected int $onlineWindowSeconds = 300;

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = service('session');
        $this->touchOnlinePresence();
    }

    protected function touchOnlinePresence(): void
    {
        if (!session()->get('auth')) {
            return;
        }

        $user = session()->get('user');
        if (!$user || empty($user->id)) {
            return;
        }

        $snapshot = $this->readOnlinePresence();
        $now = time();

        $snapshot[(string) $user->id] = [
            'id'        => (int) $user->id,
            'name'      => (string) ($user->name ?? ''),
            'email'     => (string) ($user->email ?? ''),
            'last_seen' => $now,
        ];

        $this->writeOnlinePresence($snapshot, $now);
    }

    protected function removeOnlinePresence(int $userId): void
    {
        if ($userId <= 0) {
            return;
        }

        $snapshot = $this->readOnlinePresence();
        unset($snapshot[(string) $userId]);
        $this->writeOnlinePresence($snapshot, time());
    }

    protected function getOnlineUsersSnapshot(): array
    {
        $snapshot = $this->readOnlinePresence();
        $now = time();
        $threshold = $now - $this->onlineWindowSeconds;
        $online = [];

        foreach ($snapshot as $entry) {
            $lastSeen = isset($entry['last_seen']) ? (int) $entry['last_seen'] : 0;
            if ($lastSeen < $threshold) {
                continue;
            }

            $online[] = [
                'id'        => (int) ($entry['id'] ?? 0),
                'name'      => (string) ($entry['name'] ?? ''),
                'email'     => (string) ($entry['email'] ?? ''),
                'last_seen' => $lastSeen,
            ];
        }

        usort(
            $online,
            static fn (array $a, array $b): int => $b['last_seen'] <=> $a['last_seen']
        );

        return $online;
    }

    protected function getOnlineWindowSeconds(): int
    {
        return $this->onlineWindowSeconds;
    }

    private function getOnlinePresenceFile(): string
    {
        return WRITEPATH . 'online-users.json';
    }

    private function readOnlinePresence(): array
    {
        $file = $this->getOnlinePresenceFile();
        if (!is_file($file)) {
            return [];
        }

        $json = @file_get_contents($file);
        if ($json === false || $json === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function writeOnlinePresence(array $snapshot, int $now): void
    {
        $threshold = $now - $this->onlineWindowSeconds;

        foreach ($snapshot as $key => $entry) {
            $lastSeen = isset($entry['last_seen']) ? (int) $entry['last_seen'] : 0;
            if ($lastSeen < $threshold) {
                unset($snapshot[$key]);
            }
        }

        $file = $this->getOnlinePresenceFile();
        $dir = dirname($file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $fp = @fopen($file, 'c+');
        if ($fp === false) {
            return;
        }

        if (@flock($fp, LOCK_EX)) {
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($snapshot, JSON_UNESCAPED_UNICODE));
            fflush($fp);
            flock($fp, LOCK_UN);
        }

        fclose($fp);
    }
}
