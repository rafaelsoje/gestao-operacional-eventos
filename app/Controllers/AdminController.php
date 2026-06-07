<?php

namespace App\Controllers;

use App\Models\Logs;
use App\Models\Settings;
use App\Models\Users;
use App\Services\AvatarService;
use App\Services\TelegramService;
use App\Validation\FormRequest\UserFormRequest;
use CodeIgniter\HTTP\ResponseInterface;
use stdClass;
use Throwable;

class AdminController extends Home
{
    /**
     * Lista usuários (exceto superusuário) com status de atividade.
     */
    public function usuarios(): string
    {
        $list = new Users();
        $superEmail = env('SUPERUSER_EMAIL', env('TELEGRAM_ADMIN_EMAIL', 'rafaelsoje@gmail.com'));
        $users = $list->select()->where('email !=', $superEmail)->findAll();
        $onlineSnapshot = $this->getOnlineUsersSnapshot();
        $onlineMap = [];

        foreach ($onlineSnapshot as $online) {
            $onlineMap[(int) ($online['id'] ?? 0)] = (int) ($online['last_seen'] ?? 0);
        }

        foreach ($users as $user) {
            $id = (int) ($user->id ?? 0);
            $onlineTs = $onlineMap[$id] ?? 0;

            $lastLoginTs = !empty($user->lastLogin) ? strtotime((string) $user->lastLogin) : 0;
            if ($lastLoginTs === false) {
                $lastLoginTs = 0;
            }

            $updatedTs = !empty($user->updated_at) ? strtotime((string) $user->updated_at) : 0;
            if ($updatedTs === false) {
                $updatedTs = 0;
            }

            $activityTs = max($onlineTs, $lastLoginTs, $updatedTs);
            $user->last_activity_ts = $activityTs;
            $user->is_online_now = $onlineTs > 0;
        }

        return view('usuarios', ['users' => $users, 'botanicUser' => true]);
    }

    /**
     * Exibe formulário de cadastro de usuário.
     */
    public function cadastroUsuario(): string
    {
        return view('cadastroUsuario');
    }

    /**
     * Persiste novo usuário com papel, senha inicial e log de auditoria.
     */
    public function usuarioStore(): ResponseInterface
    {
        if (!$this->validateData($this->request->getPost(), UserFormRequest::storeRules(), UserFormRequest::messages())) {
            return redirect()->route('usuario')->with('error', UserFormRequest::firstError($this->validator));
        }

        $data = $this->request->getPost();
        $data['email'] = mb_strtolower(trim((string)($data['email'] ?? '')));
        $data['name'] = trim((string)($data['name'] ?? ''));

        if ((new Users())->where('email', $data['email'])->first()) {
            return redirect()->route('usuario')->with('error', 'Este e-mail já está cadastrado.');
        }

        $defaultPassword = trim((string)env('DEFAULT_USER_PASSWORD', ''));
        if ($defaultPassword === '') {
            $defaultPassword = bin2hex(random_bytes(8));
        }
        $pass = password_hash($defaultPassword, PASSWORD_DEFAULT);
        $data['avatar'] = "avatar.svg";
        $role = $data['role'] ?? 'user';
        if (!in_array($role, ['admin', 'user'], true)) {
            $role = 'user';
        }
        $data['role'] = $role;
        $data['admin'] = ($role === 'admin') ? 1 : 0;
        $data['password'] = $pass;
        if ($this->hasUsersMustChangePasswordColumn()) {
            $data['must_change_password'] = 1;
        }

        $add = new Users();
        try {
            $result = $add->insert($data);
        } catch (Throwable $e) {
            if ($this->isDuplicateEmailException($e)) {
                return redirect()->route('usuario')->with('error', 'Este e-mail já está cadastrado.');
            }
            log_message('error', 'Falha ao cadastrar usuário: {message}', ['message' => $e->getMessage()]);
            return redirect()->route('usuario')->with('error', 'Não foi possível cadastrar usuário.');
        }

        if($result){
            $user = session()->get('user');
            $this->notifyTelegram($user->name . " cadastrou um usuário " . $data['name'], TelegramService::EVENT_USERS);
            return redirect()->route('usuarios')->with('success', 'Usuário cadastrado. Senha padrão: ' . $defaultPassword);
        }

        return redirect()->route('usuario')->with('error', 'Não foi possível cadastrar usuário.');

    }

    /**
     * Carrega um usuário específico para edição.
     */
    public function updateUsuario($id): string|ResponseInterface
    {
        if(is_int(intval($id)))
        {
            $temp = new Users();
            $user = $temp->select()->where('id', $id)->first();
            if (!$user) {
                return redirect()->route('usuarios')->with('error', 'Usuário não encontrado.');
            }
            unset($user->password);

            $superEmail = env('SUPERUSER_EMAIL', env('TELEGRAM_ADMIN_EMAIL', 'rafaelsoje@gmail.com'));
            if ($user && $user->email === $superEmail) {
                return redirect()->route('usuarios')->with('error', 'Superusuário não pode ser editado.');
            }
            return view('updateUsuario', ['user' => $user]);
        }

        return redirect()->route('usuarios')->with('error', 'Parâmetro inválido.');
    }

    /**
     * Atualiza perfil/permissões e sincroniza sessão quando necessário.
     */
    public function updateUsuarioStore(): ResponseInterface
    {
        // if (!session()->has('auth')) {
        //     return redirect()->route('login')->with('error', 'Sessão expirada!!!');
        // }

        if (!$this->validateData($this->request->getPost(), UserFormRequest::updateRules(), UserFormRequest::messages())) {
            return redirect()->route('usuarios')->with('error', UserFormRequest::firstError($this->validator));
        }

        $data = $this->request->getPost();
        $data['name'] = trim((string)($data['name'] ?? ''));
        $superEmail = env('SUPERUSER_EMAIL', env('TELEGRAM_ADMIN_EMAIL', 'rafaelsoje@gmail.com'));
        $target = (new Users())->select('id,email,admin,role,avatar')->where('id', $data['id'])->first();
        if (!$target) {
            return redirect()->route('usuarios')->with('error', 'Usuário não encontrado.');
        }
        if ($target && $target->email === $superEmail) {
            return redirect()->route('usuarios')->with('error', 'Superusuário não pode ser editado.');
        }

        $sessionUser = session()->get('user');
        $isSelf = $sessionUser && (int)$sessionUser->id === (int)$data['id'];

        if ($isSelf) {
            $img = $this->request->getFile('avatar');
            if ($img && $img->isValid() && !$img->hasMoved()) {
                $avatarOk = $this->validate([
                    'avatar' => [
                        'rules' => 'uploaded[avatar]|is_image[avatar]|ext_in[avatar,jpg,png,jpeg]',
                        'errors' => [
                            'uploaded' => 'Você deve selecionar um arquivo para upload.',
                            'is_image' => 'O arquivo enviado não é uma imagem válida.',
                            'ext_in' => 'A imagem deve estar nos formatos: jpg, png, jpeg.',
                        ],
                    ],
                ]);

                if (!$avatarOk) {
                    $errors = $this->validator->getErrors('avatar');
                    return redirect()->route('usuarios')->with('error', $errors['avatar']);
                }

                try {
                    $name = (new AvatarService())->save($img);
                } catch (\Throwable $e) {
                    return redirect()->route('usuarios')->with('error', 'Não foi possível salvar o avatar.');
                }

                $oldAvatar = (string)($target->avatar ?? '');
                $avatarUpdated = (new Users())->update($data['id'], ['avatar' => $name]);
                if (!$avatarUpdated) {
                    return redirect()->route('usuarios')->with('error', 'Não foi possível atualizar o avatar.');
                }

                $this->cleanupUnusedAvatar($oldAvatar, $name);
            }

            if (!empty($data['password'])) {
                if (strlen((string)$data['password']) < 8) {
                    return redirect()->route('usuarios')->with('error', 'A senha deve ter pelo menos 8 caracteres.');
                }
                (new Users())->update($data['id'], [
                    'password' => password_hash($data['password'], PASSWORD_DEFAULT)
                ]);
            }
        }

        $role = $data['role'] ?? 'user';
        if (!in_array($role, ['admin', 'user'], true)) {
            $role = 'user';
        }
        $items = [
            'name' => $data['name'],
            'role' => $role,
            'admin' => ($role === 'admin') ? 1 : 0,
        ];

        $temp = new Users();
        $update = $temp->update($data['id'], $items);

        if (!$update) {
            return redirect()->route('usuarios')->with('error', 'Erro ao Gravar dados.');
        }

        $instance  = new Users();
        $user = $instance->select()->where('id', $data['id'])->first();

        $sessionUser = session()->get('user');
        if ($sessionUser && (int)$sessionUser->id === (int)$data['id']) {
            $userInfo = new stdClass;
            $userInfo->id = $user->id;
            $userInfo->name = $user->name;
            $userInfo->email = $user->email;
            $userInfo->admin = $user->admin;
            $userInfo->avatar = $user->avatar;
            $userInfo->role = $user->role ?? ((int)($user->admin ?? 0) === 1 ? 'admin' : 'user');
            $userInfo->ip = $user->ip ?? null;

            session()->set('user', $userInfo);
            if ($userInfo->role === 'admin' || $userInfo->role === 'superuser') {
                session()->set('admin', true);
            } else {
                session()->remove('admin');
            }
        }

        $user = session()->get('user');
        $data = [
            'title' => 'ATUALIZOU USUÁRIO',
            'description' => "O usuário " . $user->name  . " atualizou seu perfil em " . date('Y-m-d H:i:s')
        ];

        $log = (new Logs())->insert($data);

        return redirect()->route('usuarios')->with('success', 'Dados atualizados com sucesso!');
    }

    /**
     * Remove usuário por id e limpa avatar não utilizado.
     */
    public function removeUsuario($id = null): ResponseInterface
    {
        $id = $id ?? $this->request->getPost('id');

        if(is_int(intval($id))){
            $superEmail = env('SUPERUSER_EMAIL', env('TELEGRAM_ADMIN_EMAIL', 'rafaelsoje@gmail.com'));
            $target = (new Users())->select('id,email,avatar')->where('id', $id)->first();
            if ($target && $target->email === $superEmail) {
                return redirect('usuarios')->with('error', 'Superusuário não pode ser removido.');
            }
            if((new Users())->delete($id)){
                $this->cleanupUnusedAvatar((string)($target->avatar ?? ''), null);
                $user = session()->get('user');
                return redirect('usuarios')->with('success', 'Usuário removido com sucesso!');
            }

            return redirect('usuarios')->with('error', 'Não foi possível remover usuário');
        }

        return redirect()->route('usuarios')->with('error', 'Parametro incorreto.');
    }

    /**
     * Exibe painel de notificações e usuários online.
     */
    public function notifications(): string|ResponseInterface
    {
        if (!$this->canManageNotifications()) {
            return redirect()->route('home')->with('error', 'Acesso negado.');
        }

        $telegram = new TelegramService();
        $enabled = $telegram->isEnabled();
        $onlineUsers = $this->getOnlineUsersSnapshot();

        return view('notifications', [
            'enabled' => $enabled,
            'eventPreferences' => $telegram->eventPreferences(),
            'onlineUsers' => $onlineUsers,
            'onlineWindowSeconds' => $this->getOnlineWindowSeconds(),
        ]);
    }

    /**
     * Atualiza as preferências de envio para Telegram.
     */
    public function notificationsUpdate(): ResponseInterface
    {
        if (!$this->canManageNotifications()) {
            return redirect()->route('home')->with('error', 'Acesso negado.');
        }

        $enabled = $this->request->getPost('telegram_enabled') === '1' ? 'true' : 'false';
        $selectedEvents = (array)($this->request->getPost('telegram_events') ?? []);
        $settings = new Settings();
        $settings->setValue('telegram_enabled', $enabled);

        foreach (TelegramService::EVENT_LABELS as $event => $label) {
            $settings->setValue('telegram_event_' . $event, in_array($event, $selectedEvents, true) ? 'true' : 'false');
        }

        return redirect()->route('admin.notifications')->with('success', 'Configuração atualizada.');
    }

    /**
     * Retorna prévia (JSON) de quantos logs serão removidos.
     */
    public function logsCleanupPreview(): ResponseInterface
    {
        if (!$this->canManageNotifications()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Acesso negado.']);
        }

        $range = $this->getCleanupRange($this->request->getGet('days'));
        if ($range === null) {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'Período inválido.']);
        }

        if (!db_connect()->fieldExists('created_at', 'logs')) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'A tabela de logs não possui coluna created_at.']);
        }

        $count = $range === 'all'
            ? $this->countAllLogs()
            : $this->countLogsOlderThanDays((int) $range);

        return $this->response->setJSON([
            'days' => $range,
            'count' => $count,
        ]);
    }

    /**
     * Executa limpeza de logs por período e registra auditoria.
     */
    public function logsCleanupDelete(): ResponseInterface
    {
        if (!$this->canManageNotifications()) {
            return redirect()->route('home')->with('error', 'Acesso negado.');
        }

        $range = $this->getCleanupRange($this->request->getPost('days'));
        if ($range === null) {
            return redirect()->route('admin.notifications')->with('error', 'Período inválido para remoção de logs.');
        }

        if (!db_connect()->fieldExists('created_at', 'logs')) {
            return redirect()->route('admin.notifications')->with('error', 'A tabela de logs não possui coluna created_at.');
        }

        $count = $range === 'all'
            ? $this->countAllLogs()
            : $this->countLogsOlderThanDays((int) $range);

        if ($count < 1) {
            return redirect()->route('admin.notifications')->with('success', 'Nenhum log encontrado para remoção.');
        }

        if ($range === 'all') {
            $deletedAll = (new Logs())->where('id >', 0)->delete();
            if ($deletedAll === false) {
                return redirect()->route('admin.notifications')->with('error', 'Falha ao remover todos os logs.');
            }

            return redirect()->route('admin.notifications')->with('success', "Foram removidos {$count} registros de logs.");
        }

        $days = (int) $range;
        $limitDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        $logsModel = new Logs();
        $deletedByCreatedAt = $logsModel->where('created_at <', $limitDate)->delete();
        if ($deletedByCreatedAt === false) {
            return redirect()->route('admin.notifications')->with('error', 'Falha ao remover logs por created_at.');
        }

        $legacyIds = $this->getLegacyLogIdsOlderThanDays($days);
        if (!empty($legacyIds)) {
            $deletedLegacy = (new Logs())->whereIn('id', $legacyIds)->delete();
            if ($deletedLegacy === false) {
                return redirect()->route('admin.notifications')->with('error', 'Falha ao remover logs legados.');
            }
        }

        $user = session()->get('user');
        (new Logs())->insert([
            'title' => 'LIMPEZA DE LOGS',
            'description' => "O usuário {$user->name} removeu {$count} logs com mais de {$days} dias em " . date('Y-m-d H:i:s'),
        ]);

        return redirect()->route('admin.notifications')->with('success', "Foram removidos {$count} registros de logs com mais de {$days} dias.");
    }

    /**
     * Exibe os logs recentes com descrição tratada para visualização.
     */
    public function logs(): string
    {
        $data = (new Logs())->select()->orderBy('id', 'desc')->limit(50)->findAll();
        foreach ($data as $log) {
            $description = (string) ($log->description ?? '');
            $log->user_name = $this->extractLogUserName($description);
            $log->clean_description = $this->stripUserFromDescription($description);
        }

        

        return view('logs', ['logs' => $data]);
    }

}
