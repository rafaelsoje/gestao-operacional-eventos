<?php

namespace App\Services;

use App\Models\Settings;

class TelegramService
{
    public function isEnabled(): bool
    {
        $enabled = env('TELEGRAM_ENABLED', true);
        $dbValue = (new Settings())->getValue('telegram_enabled', null);
        if ($dbValue !== null) {
            $enabled = filter_var($dbValue, FILTER_VALIDATE_BOOL);
        }
        return (bool)$enabled;
    }

    public function send(string $message): void
    {
        if (!$this->isEnabled()) {
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
}
