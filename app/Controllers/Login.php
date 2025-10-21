<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Logs;
use App\Models\Operacional;
use App\Models\Users;
use CodeIgniter\HTTP\ResponseInterface;
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

            $users = (new Users())->findAll();

            foreach($users as $user){
                if($recovery == hash('sha256', $user->email))
                {
                    return view('newPasswd', ['recovery' => $recovery, 'id' => $user->id]);
                }
            }
            return redirect()->route('login')->with('error', 'Usuario não encontrado.');
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

        $user = (new Users())->where('email', $this->request->getPost('email'))->first();          
        
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
                'description' => "Tentativa de login com falha " . $email . " " . date('d-m-Y H:i:s')
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

            session()->set('auth', true);
            session()->set('user', $userInfo);            

            if ($userInfo->admin == '1') {
                session()->set('admin', true);
            }

            $data = [
                'title' => 'LOGIN',
                'description' => "O usuário " . $user->name  . " efetuou login em " . date('d-m-Y H:i:s')
            ];           

            $item = [
                'lastLogin' => date('Y-m-d H:i:s'),
                'loginAttempts' => 0,               
                'ip' => $real_ip
            ];            

            (new Users())->update($user->id, $item);

            $log = (new Logs())->insert($data);

            $this->telegran($user->name . " entrou no sistema!");
            return redirect()->route('home')->with('success', 'Login efetuado com sucesso.'); 

        }else{

            $email = $this->request->getPost('email');

            $data = [
                'title' => 'FALHA LOGIN',
                'description' => "Tentativa de login com falha " . $email . " " . date('d-m-Y H:i:s')
            ];

            $loginAttempt = $user->loginAttempts + 1 ;

            $item = [                
                'loginAttempts' => $loginAttempt,
                'lastAttemptTime' => date('Y-m-d H:i:s'),
            ];

            (new Users())->update($user->id, $item);

            $log = (new Logs())->insert($data);    
            
            $this->telegran("Tetantivade login com falha. \n". $email);
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
        $email = $this->request->getPost();

        $user = (new Users())->where('email', $email)->first();

        if($user){

            $link = base_url() . "login?recovery=" .hash('sha256', $user->email);

            $to = $user->email;
            $subject = "Redefinição de senha";
            $headers  = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
            $headers .= "From: contato@rafaelsoje.com" . "\r\n";

            $html = file_get_contents("template.html");
            $html = str_replace("{{nome_cliente}}", $user->name, $html);
            $html = str_replace("{{nome_da_empresa}}", "rafaelsoje.com", $html);
            $html = str_replace("{{link_do_site}}", $link, $html);
            $html = str_replace("{{ano_atual}}", date("Y"), $html);

            mail($to, $subject, $html, $headers);

            return redirect('login');
        }

        return redirect()->back()->with('error', 'Endereço de email não encontrado.');
        
    }

    public function recoveryStore()
    {
        $post = $this->request->getPost();       

        $users = (new Users())->findAll();

        foreach($users as $user)
        {
            if($post['recovery'] == hash('sha256', $user->email))
            {
                $data = [
                    'password' => password_hash($post['password'], PASSWORD_DEFAULT)
                ];

                if((new Users())->update($post['id'], $data))
                {
                    return redirect()->route('login')->with('success', 'Senha atualizada con sucesso.');
                }
            }

            return redirect()->route('login')->with('error', 'Erro ao redefinir senha.');
        }

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
