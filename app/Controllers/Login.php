<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Logs;
use App\Models\Operacional;
use App\Models\PasswordResets;
use App\Models\Users;
use App\Services\TelegramService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;
use Config\Services;
use DateTime;
use stdClass;

class Login extends BaseController
{
    protected $session;    

    public function index(): String|ResponseInterface
    {

        if($this->request->getGet('recovery'))
        {
            $recovery = $this->request->getGet('recovery');
            $tokenHash = hash('sha256', $recovery);

            $reset = (new PasswordResets())
                ->where('token_hash', $tokenHash)
                ->where('expires_at >=', Time::now()->toDateTimeString())
                ->first();

            if ($reset) {
                return view('newPasswd', ['recovery' => $recovery]);
            }

            return redirect()->route('login')->with('error', 'Link de recuperação inválido ou expirado.');
        }

        return view('login');
    }

    public function store(): ResponseInterface
    {      
        $this->session = session();  
        
        $validate = $this->validate([
            'email' => 'required|valid_email',
            'password' => 'required',
        ]);

        if (!$validate) {            
            return redirect()->back()->with('error', 'Preencha os campos corretamente!!!');
        }

        $email = $this->request->getPost('email');
        if (!$this->allowLoginAttempt($email, $this->request->getIPAddress())) {
            return redirect()->back()->with('error', 'Usuário ou Senha inválidos.');
        }

        $user = (new Users())->where('email', $email)->first();          
        
        if(!$user){
            return redirect()->back()->with('error', "Usuário ou Senha inválidos.");
        }
        
        $now = new DateTime();
        $lastAttempt = new DateTime($user->lastAttemptTime);
        $interval = $now->getTimestamp() - $lastAttempt->getTimestamp(); // Tempo em segundos   

    
        if($user->loginAttempts >= 2 && $interval < 30){ 

            $email = $this->request->getPost('email');

            $data = [
                'title' => 'FALHA LOGIN',
                'description' => "Tentativa de login com falha " . $email . " " . date('Y-m-d H:i:s')
            ];

            $loginAttempt = $user->loginAttempts + 1 ;

            $item = [                
                'loginAttempts' => $loginAttempt,
                'lastAttemptTime' => date('Y-m-d H:i:s'),
            ];

            (new Users())->update($user->id, $item);

            $log = (new Logs())->insert($data);            
            return redirect()->back()->with('error', 'Varias tentativas de Login com falha. Aguarde 30 segundos e tente novamente!');
        }
   
        // Função para verificar se um IP pertence a um intervalo CIDR
        function ip_in_range($ip, $range) 
        {
            list($subnet, $bits) = explode('/', $range);
            $ip_dec = ip2long($ip);
            $subnet_dec = ip2long($subnet);
            $mask = -1 << (32 - $bits);
            return ($ip_dec & $mask) == ($subnet_dec & $mask);
        }
        
        // Lista de IPs da Cloudflare
        $cloudflare_ips = [
            "173.245.48.0/20",
            "103.21.244.0/22",
            "103.22.200.0/22",
            "103.31.4.0/22",
            "141.101.64.0/18",
            "108.162.192.0/18",
            "190.93.240.0/20",
            "188.114.96.0/20",
            "197.234.240.0/22",
            "198.41.128.0/17",
            "162.158.0.0/15",
            "104.16.0.0/13",
            "104.24.0.0/14",
            "172.64.0.0/13",
            "131.0.72.0/22"
        ];
        
        // Obtém o IP real do visitante
        $remote_ip = $_SERVER['REMOTE_ADDR'];
        $real_ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $remote_ip;
        $valid_cloudflare = false;
        
        // Verifica se o IP remoto está nos IPs da Cloudflare
        foreach ($cloudflare_ips as $range) {
            if (ip_in_range($remote_ip, $range)) {
                $valid_cloudflare = true;
                break;
            }
        }        
        // Se não for um IP da Cloudflare, usar o REMOTE_ADDR
        if (!$valid_cloudflare) {
            $real_ip = $remote_ip;
        }

        if ($user && password_verify((string)$this->request->getPost('password'), $user->password)) {

            if(password_needs_rehash($user->password, PASSWORD_DEFAULT))
            {
                $hash = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);

                $data = [
                    'password' => $hash
                ];

                (new Users())->update($user->id, $data);
            }
         
            $this->session->remove('login_attempts');
            $this->session->remove('last_attempt_time');

            $userInfo = new stdClass;
            $userInfo->id = $user->id;
            $userInfo->name = $user->name;
            $userInfo->email = $user->email;
            $userInfo->admin = $user->admin;
            $userInfo->avatar = $user->avatar;
            $userInfo->ip = $user->ip;
            $userInfo->role = $this->resolveUserRole($user);

            session()->set('auth', true);
            session()->set('user', $userInfo);            

            if ($userInfo->role === 'admin' || $userInfo->role === 'superuser') {
                session()->set('admin', true);
            }

            $data = [
                'title' => 'LOGIN',
                'description' => "O usuário " . $user->name  . " efetuou login em " . date('Y-m-d H:i:s')
            ];           

            $item = [
                'lastLogin' => date('Y-m-d H:i:s'),
                'loginAttempts' => 0,               
                'ip' => $real_ip
            ];            

            (new Users())->update($user->id, $item);

            $log = (new Logs())->insert($data);

            (new TelegramService())->send($user->name . " entrou no sistema");

            if (!empty($user->must_change_password)) {
                return redirect()->route('password.force')->with('warning', 'Defina uma nova senha para continuar.');
            }
           
            return redirect()->route('home')->with('success', 'Login efetuado com sucesso.'); 

        }else{

            $email = $this->request->getPost('email');

            $data = [
                'title' => 'FALHA LOGIN',
                'description' => "Tentativa de login com falha " . $email . " " . date('Y-m-d H:i:s')
            ];

            $loginAttempt = $user->loginAttempts + 1 ;

            $item = [                
                'loginAttempts' => $loginAttempt,
                'lastAttemptTime' => date('Y-m-d H:i:s'),
            ];

            (new Users())->update($user->id, $item);

            $log = (new Logs())->insert($data);    
            
            #$this->telegran("Tentativa de login com falha. \n". $email);
            return redirect()->back()->with('error', "Usuário ou Senha inválidos.");            
        }  
    }

    public function logOut(): ResponseInterface
    {
        session()->destroy();

        return redirect()->route('login');
    }

    public function recovery(): String
    {
        return view('recovery');
    }

    public function recoverySend(): ResponseInterface
    {
        $email = mb_strtolower(trim((string)$this->request->getPost('email')));
        $ip = (string)$this->request->getIPAddress();

        $this->logRecoveryEvent('SOLICITACAO', [
            'email' => $this->maskEmail($email),
            'ip' => $ip,
        ]);

        if (!$this->allowRecoveryAttempt($email, $ip)) {
            $this->logRecoveryEvent('RATE_LIMIT', [
                'email' => $this->maskEmail($email),
                'ip' => $ip,
            ]);
            return redirect('login')->with('success', 'Se o e-mail existir, enviaremos um link de redefinição.');
        }

        $resets = new PasswordResets();
        $resets->where('expires_at <', Time::now()->toDateTimeString())->delete();

        $user = (new Users())->where('email', $email)->first();

        if($user){

            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expiresAt = Time::now()->addHours(1)->toDateTimeString();

            $resets->where('user_id', $user->id)->delete();
            $resets->insert([
                'user_id' => $user->id,
                'token_hash' => $tokenHash,
                'expires_at' => $expiresAt,
                'created_at' => Time::now()->toDateTimeString(),
            ]);

            $link = base_url() . "login?recovery=" . $token;

            $to = $user->email;
            $subject = "Redefinição de senha";
            $headers  = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
            $headers .= "From: contato@rafaelsoje.com" . "\r\n";

            $templatePath = FCPATH . 'template.html';
            $html = is_file($templatePath) ? file_get_contents($templatePath) : false;
            if ($html === false) {
                $html = '<p>Olá, {{nome_cliente}}.</p>'
                    . '<p>Para redefinir sua senha, acesse: <a href="{{link_do_site}}">{{link_do_site}}</a></p>'
                    . '<p>&copy; {{ano_atual}} {{nome_da_empresa}}</p>';
            }

            $html = str_replace("{{nome_cliente}}", $user->name, $html);
            $html = str_replace("{{nome_da_empresa}}", "rafaelsoje.com", $html);
            $html = str_replace("{{link_do_site}}", $link, $html);
            $html = str_replace("{{ano_atual}}", date("Y"), $html);

            if (!mail($to, $subject, $html, $headers)) {
                log_message('warning', 'Falha ao enviar e-mail de recuperação para: {email}', ['email' => $to]);
                $this->logRecoveryEvent('EMAIL_FALHA', [
                    'user_id' => (int)$user->id,
                    'email' => $this->maskEmail($to),
                    'ip' => $ip,
                    'expires_at' => $expiresAt,
                ]);
            } else {
                $this->logRecoveryEvent('EMAIL_ENVIADO', [
                    'user_id' => (int)$user->id,
                    'email' => $this->maskEmail($to),
                    'ip' => $ip,
                    'expires_at' => $expiresAt,
                ]);
            }
        } else {
            $this->logRecoveryEvent('EMAIL_NAO_ENCONTRADO', [
                'email' => $this->maskEmail($email),
                'ip' => $ip,
            ]);
        }

        return redirect('login')->with('success', 'Se o e-mail existir, enviaremos um link de redefinição.');
        
    }

    public function forcePasswordForm(): string|ResponseInterface
    {
        if (!session()->has('auth')) {
            return redirect()->route('login')->with('error', 'Sessão expirada ou não autenticada.');
        }

        $user = session()->get('user');
        if (!$user) {
            return redirect()->route('login')->with('error', 'Sessão expirada ou não autenticada.');
        }

        $dbUser = (new Users())->find($user->id);
        if (!$dbUser || empty($dbUser->must_change_password)) {
            return redirect()->route('home');
        }

        return view('newPasswd', ['forceChange' => true]);
    }

    public function forcePasswordStore(): ResponseInterface
    {
        if (!session()->has('auth')) {
            return redirect()->route('login')->with('error', 'Sessão expirada ou não autenticada.');
        }

        $validate = $this->validate([
            'password' => 'required|min_length[8]|max_length[72]|matches[password_confirmation]|regex_match[/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).+$/]',
            'password_confirmation' => 'required',
        ]);

        if (!$validate) {
            return redirect()->back()->with('error', 'Use ao menos 8 caracteres, com maiúscula, minúscula, número, símbolo e confirmação igual.');
        }

        $user = session()->get('user');
        if (!$user) {
            return redirect()->route('login')->with('error', 'Sessão expirada ou não autenticada.');
        }

        $password = (string)$this->request->getPost('password');
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $updated = (new Users())->update($user->id, [
            'password' => $hash,
            'must_change_password' => 0,
        ]);

        if (!$updated) {
            return redirect()->back()->with('error', 'Erro ao atualizar a senha.');
        }

        return redirect()->route('home')->with('success', 'Senha atualizada com sucesso.');
    }

    private function resolveUserRole($user): string
    {
        $superEmail = env('SUPERUSER_EMAIL', env('TELEGRAM_ADMIN_EMAIL', 'rafaelsoje@gmail.com'));
        if (!empty($user->email) && $user->email === $superEmail) {
            return 'superuser';
        }
        if (!empty($user->role)) {
            return (string)$user->role;
        }
        return ($user->admin == '1') ? 'admin' : 'user';
    }

    private function allowRecoveryAttempt(?string $email, string $ip): bool
    {
        $cache = Services::cache();
        $ttl = 3600;
        $maxPerIp = 10;
        $maxPerEmail = 5;

        $ipKey = 'recovery_ip_' . sha1($ip);
        $ipCount = (int)($cache->get($ipKey) ?? 0);
        if ($ipCount >= $maxPerIp) {
            return false;
        }

        $emailKey = null;
        if (!empty($email)) {
            $normEmail = mb_strtolower(trim($email));
            $emailKey = 'recovery_email_' . sha1($normEmail);
            $emailCount = (int)($cache->get($emailKey) ?? 0);
            if ($emailCount >= $maxPerEmail) {
                return false;
            }
        }

        $cache->save($ipKey, $ipCount + 1, $ttl);
        if ($emailKey) {
            $emailCount = (int)($cache->get($emailKey) ?? 0);
            $cache->save($emailKey, $emailCount + 1, $ttl);
        }

        return true;
    }

    private function allowLoginAttempt(?string $email, string $ip): bool
    {
        $cache = Services::cache();
        $ttl = 900; // 15 min
        $maxPerIp = 20;
        $maxPerEmail = 8;

        $ipKey = 'login_ip_' . sha1($ip);
        $ipCount = (int)($cache->get($ipKey) ?? 0);
        if ($ipCount >= $maxPerIp) {
            return false;
        }

        $emailKey = null;
        if (!empty($email)) {
            $normEmail = mb_strtolower(trim($email));
            $emailKey = 'login_email_' . sha1($normEmail);
            $emailCount = (int)($cache->get($emailKey) ?? 0);
            if ($emailCount >= $maxPerEmail) {
                return false;
            }
        }

        $cache->save($ipKey, $ipCount + 1, $ttl);
        if ($emailKey) {
            $emailCount = (int)($cache->get($emailKey) ?? 0);
            $cache->save($emailKey, $emailCount + 1, $ttl);
        }

        return true;
    }

    public function recoveryStore()
    {
        $ip = (string)$this->request->getIPAddress();
        $validate = $this->validate([
            'recovery' => 'required|exact_length[64]|alpha_numeric',
            'password' => 'required|min_length[8]|max_length[72]|matches[password_confirmation]|regex_match[/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).+$/]',
            'password_confirmation' => 'required',
        ]);

        if (!$validate) {
            $this->logRecoveryEvent('FALHA_VALIDACAO', [
                'ip' => $ip,
            ]);
            return redirect()->back()->with('error', 'Dados inválidos para redefinição de senha.');
        }

        $post = $this->request->getPost();

        (new PasswordResets())->where('expires_at <', Time::now()->toDateTimeString())->delete();

        $tokenHash = hash('sha256', $post['recovery']);

        $reset = (new PasswordResets())
            ->where('token_hash', $tokenHash)
            ->where('expires_at >=', Time::now()->toDateTimeString())
            ->first();

        if (!$reset) {
            $this->logRecoveryEvent('TOKEN_INVALIDO_OU_EXPIRADO', [
                'ip' => $ip,
            ]);
            return redirect()->route('login')->with('error', 'Link de recuperação inválido ou expirado.');
        }

        $data = [
            'password' => password_hash((string)$post['password'], PASSWORD_DEFAULT),
            'must_change_password' => 0,
        ];

        if((new Users())->update($reset->user_id, $data))
        {
            (new PasswordResets())->delete($reset->id);
            $this->logRecoveryEvent('SENHA_REDEFINIDA', [
                'user_id' => (int)$reset->user_id,
                'ip' => $ip,
            ]);
            return redirect()->route('login')->with('success', 'Senha atualizada com sucesso.');
        }

        $this->logRecoveryEvent('FALHA_ATUALIZAR_SENHA', [
            'user_id' => (int)$reset->user_id,
            'ip' => $ip,
        ]);
        return redirect()->route('login')->with('error', 'Erro ao redefinir senha.');

    }

    private function logRecoveryEvent(string $status, array $context = []): void
    {
        $parts = ['status=' . $status];
        foreach ($context as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $parts[] = $key . '=' . $value;
        }
        $parts[] = 'ts=' . date('Y-m-d H:i:s');

        (new Logs())->insert([
            'title' => 'RECOVERY',
            'description' => implode(' | ', $parts),
        ]);
    }

    private function maskEmail(string $email): string
    {
        if ($email === '' || strpos($email, '@') === false) {
            return 'indefinido';
        }

        [$user, $domain] = explode('@', $email, 2);
        if ($user === '') {
            return '***@' . $domain;
        }

        return substr($user, 0, 1) . '***@' . $domain;
    }

    public function telegran($texto): void
    {
        // Telegram
        // Monta a mensagem para o Telegram
        // $texto = "📝 Novo Login efetuado:\n\n";
        // $texto .= "👤 Nome: " . htmlspecialchars($nome) . "\n";
        // $texto .= "📧 E-mail: " . htmlspecialchars($email) . "\n";
        // $texto .= "Assunto: " . htmlspecialchars($assunto) . "\n";
        // $texto .= "💬 Mensagem: " . htmlspecialchars($mensagem);

        // Dados do bot
        $bot_token = env('TELEGRAM_BOT_TOKEN'); 
        $chat_id = env('TELEGRAM_CHAT_ID');

        // Prepara a chamada cURL
        $url = "https://api.telegram.org/bot$bot_token/sendMessage";

        $dados = [
            'chat_id' => $chat_id,
            'text' => $texto,
            'parse_mode' => 'HTML'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($dados));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $resposta = curl_exec($ch);
        $erro = curl_error($ch);
        curl_close($ch);

    }
}
