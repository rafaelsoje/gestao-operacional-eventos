<?php

namespace App\Services;

use App\Models\Settings;

class TelegramService
{
    public const EVENT_GENERAL = 'general';
    public const EVENT_LOGIN = 'login';
    public const EVENT_USERS = 'users';
    public const EVENT_OPERATIONAL = 'operational';
    public const EVENT_SUBSCRIPTION_CHARGE_CREATED = 'subscription_charge_created';
    public const EVENT_SUBSCRIPTION_PAYMENT_RECEIVED = 'subscription_payment_received';
    public const EVENT_SUBSCRIPTION_OVERDUE = 'subscription_overdue';
    public const EVENT_SUBSCRIPTION_CANCELLED = 'subscription_cancelled';

    public const EVENT_LABELS = [
        self::EVENT_GENERAL => 'Avisos gerais',
        self::EVENT_LOGIN => 'Login de usuários',
        self::EVENT_USERS => 'Cadastro e usuários',
        self::EVENT_OPERATIONAL => 'Cadastros operacionais',
        self::EVENT_SUBSCRIPTION_CHARGE_CREATED => 'Assinatura: cobrança gerada',
        self::EVENT_SUBSCRIPTION_PAYMENT_RECEIVED => 'Assinatura: pagamento recebido',
        self::EVENT_SUBSCRIPTION_OVERDUE => 'Assinatura: vencimento/inadimplência',
        self::EVENT_SUBSCRIPTION_CANCELLED => 'Assinatura: cancelamento/estorno',
    ];

    public function isEnabled(): bool
    {
        $enabled = env('TELEGRAM_ENABLED', true);
        $dbValue = (new Settings())->getValue('telegram_enabled', null);
        if ($dbValue !== null) {
            $enabled = filter_var($dbValue, FILTER_VALIDATE_BOOL);
        }
        return (bool)$enabled;
    }

    public function isEventEnabled(string $event): bool
    {
        $event = array_key_exists($event, self::EVENT_LABELS) ? $event : self::EVENT_GENERAL;
        $dbValue = (new Settings())->getValue('telegram_event_' . $event, null);

        if ($dbValue === null) {
            return true;
        }

        return filter_var($dbValue, FILTER_VALIDATE_BOOL);
    }

    public function eventPreferences(): array
    {
        $preferences = [];

        foreach (self::EVENT_LABELS as $event => $label) {
            $preferences[$event] = [
                'label' => $label,
                'enabled' => $this->isEventEnabled($event),
            ];
        }

        return $preferences;
    }

    public function send(string $message, string $event = self::EVENT_GENERAL): void
    {
        if ($this->isCurrentUserMuted()) {
            return;
        }

        if (!$this->isEnabled() || !$this->isEventEnabled($event)) {
            return;
        }

        $botToken = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID');
        if (empty($botToken) || empty($chatId)) {
            return;
        }

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

        $data = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }

    /**
     * Não envia ao Telegram ações feitas por usuários configurados como silenciosos.
     * Eventos sem um usuário autenticado (webhooks, tarefas agendadas etc.) continuam ativos.
     */
    private function isCurrentUserMuted(): bool
    {
        $user = session()->get('user');
        $email = mb_strtolower(trim((string) ($user->email ?? '')));

        if ($email === '') {
            return false;
        }

        $configuredEmails = (string) env('TELEGRAM_MUTED_EMAILS', '');
        $mutedEmails = array_filter(array_map(
            static fn (string $item): string => mb_strtolower(trim($item)),
            explode(',', $configuredEmails)
        ));

        return in_array($email, $mutedEmails, true);
    }
}
