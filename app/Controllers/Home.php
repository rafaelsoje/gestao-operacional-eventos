<?php

namespace App\Controllers;

use App\Models\GeradorManutencao;
use App\Models\Logs;
use App\Models\Operacional;
use App\Models\Propostas;
use App\Models\Users;
use App\Services\AvatarService;
use App\Services\TelegramService;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Home extends BaseController
{
    /**
     * Exibe os dados do perfil do usuário autenticado.
     */
    public function perfil(): string|ResponseInterface
    {
        if (!session()->has('auth')) {
            return redirect()->route('login')->with('error', 'Sessão expirada ou não autenticada.');
        }

        $user = session()->get('user');
        if (!$user) {
            return redirect()->route('login')->with('error', 'Sessão expirada ou não autenticada.');
        }

        $dbUser = (new Users())->find($user->id);
        if (!$dbUser) {
            return redirect()->route('home')->with('error', 'Usuário não encontrado.');
        }

        $role = $dbUser->role ?? ((int)($dbUser->admin ?? 0) === 1 ? 'admin' : 'user');

        return view('perfil', [
            'user' => $dbUser,
            'role' => $role,
        ]);
    }

    /**
     * Atualiza dados de perfil, avatar e senha do usuário atual.
     */
    public function perfilUpdate(): ResponseInterface
    {
        if (!session()->has('auth')) {
            return redirect()->route('login')->with('error', 'Sessão expirada ou não autenticada.');
        }

        $user = session()->get('user');
        if (!$user) {
            return redirect()->route('login')->with('error', 'Sessão expirada ou não autenticada.');
        }

        $dbUser = (new Users())->find($user->id);
        if (!$dbUser) {
            return redirect()->route('perfil')->with('error', 'Usuário não encontrado.');
        }

        $data = $this->request->getPost();
        $items = [];
        $oldAvatar = (string)($dbUser->avatar ?? '');

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
                return redirect()->route('perfil')->with('error', $errors['avatar']);
            }

            try {
                $name = (new AvatarService())->save($img);
            } catch (\Throwable $e) {
                return redirect()->route('perfil')->with('error', 'Não foi possível salvar o avatar.');
            }

            $items['avatar'] = $name;
        }

        $newPassword = trim((string)($data['new_password'] ?? ''));
        if ($newPassword !== '') {
            $confirm = trim((string)($data['new_password_confirm'] ?? ''));
            if ($newPassword !== $confirm) {
                return redirect()->route('perfil')->with('error', 'As senhas não conferem.');
            }
            $currentPassword = (string)($data['current_password'] ?? '');
            $storedPasswordHash = (string)($dbUser->password ?? '');
            if ($storedPasswordHash === '' || !password_verify($currentPassword, $storedPasswordHash)) {
                return redirect()->route('perfil')->with('error', 'Senha atual incorreta.');
            }

            if (strlen($newPassword) < 8) {
                return redirect()->route('perfil')->with('error', 'Nova senha deve ter pelo menos 8 caracteres.');
            }

            $items['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
            if ($this->hasUsersMustChangePasswordColumn()) {
                $items['must_change_password'] = 0;
            }
        }

        if (empty($items)) {
            return redirect()->route('perfil')->with('success', 'Nenhuma alteração realizada.');
        }

        $updated = (new Users())->update($user->id, $items);
        if (!$updated) {
            return redirect()->route('perfil')->with('error', 'Erro ao atualizar perfil.');
        }

        if (isset($items['avatar'])) {
            $userInfo = session()->get('user');
            $userInfo->avatar = $items['avatar'];
            session()->set('user', $userInfo);
            $this->cleanupUnusedAvatar($oldAvatar, $items['avatar']);
        }

        return redirect()->route('perfil')->with('success', 'Perfil atualizado com sucesso!');
    }

    /**
     * Remove avatar antigo quando não está mais em uso por nenhum usuário.
     */
    protected function cleanupUnusedAvatar(?string $oldAvatar, ?string $newAvatar = null): void
    {
        $old = trim((string)$oldAvatar);
        $new = trim((string)$newAvatar);

        if ($old === '' || $old === 'avatar.svg' || $old === $new) {
            return;
        }

        $safe = basename($old);
        if ($safe === '' || $safe !== $old) {
            return;
        }

        $stillUsed = (new Users())->where('avatar', $old)->countAllResults();
        if ($stillUsed > 0) {
            return;
        }

        $path = WRITEPATH . 'uploads/avatars' . DIRECTORY_SEPARATOR . $safe;
        if (is_file($path)) {
            @unlink($path);
        }
    }

    /**
     * Faz entrega segura de arquivos de avatar armazenados em disco.
     */
    public function avatar(string $file): ResponseInterface
    {
        if (!session()->get('auth')) {
            return redirect()->route('login')->with('error', 'Sessão expirada. Faça login novamente.');
        }

        $path = (new AvatarService())->resolvePath($file);
        if (!is_file($path)) {
            return $this->response->setStatusCode(404);
        }

        $mime = mime_content_type($path) ?: 'application/octet-stream';

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Cache-Control', 'private, max-age=86400')
            ->setBody((string)file_get_contents($path));
    }

    /**
     * Define se o usuário atual pode gerenciar notificações administrativas.
     */
    protected function canManageNotifications(): bool
    {
        $user = session()->get('user');
        $superEmail = env('SUPERUSER_EMAIL', env('TELEGRAM_ADMIN_EMAIL', 'rafaelsoje@gmail.com'));
        return $user && $user->email === $superEmail;
    }

    /**
     * Normaliza intervalo de limpeza de logs para opções permitidas.
     */
    protected function getCleanupRange($rawValue): ?string
    {
        $value = trim((string) $rawValue);
        if ($value === 'all') {
            return 'all';
        }

        return in_array($value, ['30', '60', '90'], true) ? $value : null;
    }

    /**
     * Conta logs mais antigos que a janela informada em dias.
     */
    protected function countLogsOlderThanDays(int $days): int
    {
        $limitDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        $countByCreatedAt = (int) (new Logs())
            ->where('created_at <', $limitDate)
            ->countAllResults();

        $legacyIds = $this->getLegacyLogIdsOlderThanDays($days);

        return $countByCreatedAt + count($legacyIds);
    }

    /**
     * Recupera ids de logs legados sem created_at usando descrição.
     */
    protected function getLegacyLogIdsOlderThanDays(int $days): array
    {
        $limitTimestamp = strtotime("-{$days} days");
        $legacyLogs = (new Logs())
            ->select('id, description')
            ->where('created_at', null)
            ->findAll();

        $ids = [];
        foreach ($legacyLogs as $log) {
            $description = (string) ($log->description ?? '');
            $descriptionTimestamp = $this->extractTimestampFromLogDescription($description);
            if ($descriptionTimestamp !== null && $descriptionTimestamp < $limitTimestamp) {
                $ids[] = (int) $log->id;
            }
        }

        return $ids;
    }

    /**
     * Conta todos os registros da tabela de logs.
     */
    protected function countAllLogs(): int
    {
        return (int) (new Logs())->countAllResults();
    }

    /**
     * Extrai timestamp textual da descrição de logs legados.
     */
    protected function extractTimestampFromLogDescription(string $description): ?int
    {
        if (preg_match('/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $description, $matches) !== 1) {
            return null;
        }

        $parsed = strtotime((string) ($matches[1] ?? ''));
        return $parsed !== false ? (int) $parsed : null;
    }

    /**
     * Envia notificação para Telegram respeitando configuração ativa.
     */
    protected function notifyTelegram(string $message): void
    {
        (new TelegramService())->send($message);
    }

    /**
     * Monta estrutura de calendário mensal para o dashboard principal.
     */
    public function calendario(): string
    {
        $model = new Propostas();

        $ano = $this->request->getGet('ano') ?? date('Y');

        $eventos = $model->where('YEAR(start_date)', $ano)->findAll();

        $data = array_map(function ($e) {
            // cor por status
            switch ($e->status) {
                case '1': $cor = '#28a745'; break; // aceita
                case '2': $cor = '#e0a800'; break; // em análise (melhor que #ffc107)
                case '3': $cor = '#dc3545'; break; // rejeitada
                default:  $cor = '#3788d8';
            }

            return [
                'id'    => $e->id,
                'title' => $e->event_name,
                'start' => $e->start_date,
                'end'   => $e->end_date,
                'color' => $cor,
                // extendedProps:
                'status'    => $e->status,
                'type'      => $e->type ?? '',
                'descricao' => $e->description ?? '',
            ];
        }, $eventos);

        return view('calendario', [
            'ano'      => $ano,
            'fcEvents' => json_encode($data), // string JSON
            'calendario' => true,
        ]);
    }


    /**
     * Converte número do mês para nome textual em português.
     */
    public function mes($num): string
    {
        switch($num){
            case '01':
            case '1':
                return "Janeiro";
                break;
            case '02':
            case '2':
                return "Fevereiro";
                break;
            case '03':
            case '3':
                return "Março";
                break;
            case '04':
            case '4':
                return "Abril";
                break;
            case '05':
            case '5':
                return "Maio";
                break;
            case '06':
            case '6':
                return "Junho";
                break;
            case '07':
            case '7':
                return "Julho";
                break;
            case '08':
            case '8':
                return "Agosto";
                break;
            case '09':
            case '9':
                return "Setembro";
                break;
            case '10':
                return "Outubro";
                break;
            case '11':
                return "Novembro";
                break;
            case '12':
                return "Dezembro";
                break;
            default:
                return "Não encontrado";
                break;
        }
    }

    /**
     * Extrai nome de usuário presente no início da descrição do log.
     */
    protected function extractLogUserName(string $description): string
    {
        $description = trim($description);
        if ($description === '') {
            return '---';
        }

        $pattern = '/(?:^|\\b)(?:o\\s+)?usu[aá]rio\\s+(.+?)\\s+(?:cadastrou|efetuou|apagou|removeu|atualizou|editou|entrou)\\b/iu';
        if (preg_match($pattern, $description, $matches) === 1) {
            $name = trim((string) ($matches[1] ?? ''));
            return $name !== '' ? $name : '---';
        }

        return '---';
    }

    /**
     * Remove prefixo de usuário da descrição para exibição limpa.
     */
    protected function stripUserFromDescription(string $description): string
    {
        $description = trim($description);
        if ($description === '') {
            return '';
        }

        $pattern = '/^(?:o\\s+)?usu[aá]rio\\s+.+?\\s+(?=(?:cadastrou|efetuou|apagou|removeu|atualizou|editou|entrou)\\b)/iu';
        $clean = preg_replace($pattern, '', $description);
        $clean = trim((string) $clean);

        return $clean !== '' ? $clean : $description;
    }

    /**
     * Verifica presença da coluna de troca obrigatória de senha.
     */
    protected function hasUsersMustChangePasswordColumn(): bool
    {
        static $hasColumn = null;
        if ($hasColumn !== null) {
            return $hasColumn;
        }

        $hasColumn = db_connect()->fieldExists('must_change_password', 'users');
        return $hasColumn;
    }

    /**
     * Detecta exceção de e-mail duplicado em operações no banco.
     */
    protected function isDuplicateEmailException(Throwable $e): bool
    {
        $message = mb_strtolower($e->getMessage());
        return str_contains($message, 'duplicate') && str_contains($message, 'email');
    }

    /**
     * Valida identificador de gerador permitido pelo sistema.
     */
    protected function isValidGerador(string $gerador): bool
    {
        return in_array($gerador, ['G1', 'G2'], true);
    }

    /**
     * Valida data no formato estrito AAAA-MM-DD.
     */
    protected function isValidDateYmd(string $date): bool
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) !== 1) {
            return false;
        }

        [$year, $month, $day] = array_map('intval', explode('-', $date));
        return checkdate($month, $day, $year);
    }

    /**
     * Normaliza tipos de evento de busca e remove valores inválidos.
     */
    protected function sanitizeSearchTipos($rawTipos): ?array
    {
        if ($rawTipos === null || $rawTipos === '') {
            return [];
        }

        if (is_string($rawTipos)) {
            $rawTipos = [$rawTipos];
        }

        if (!is_array($rawTipos)) {
            return null;
        }

        $allowed = ['social', 'corporativo', 'entretenimento'];
        $tipos = [];
        foreach ($rawTipos as $tipo) {
            if (!is_string($tipo)) {
                return null;
            }

            $tipo = mb_strtolower(trim($tipo));
            if ($tipo === '') {
                continue;
            }

            if (!in_array($tipo, $allowed, true)) {
                continue;
            }

            $tipos[] = $tipo;
        }

        return array_values(array_unique($tipos));
    }

    /**
     * Calcula tempo de uso de gerador a partir dos campos do formulário.
     */
    protected function calculateGeneratorUsageMinutesFromPost(array $post, string $entradaKey, string $saidaKey): int
    {
        return $this->calculateGeneratorUsageMinutesFromHourValues(
            (string)($post[$entradaKey] ?? ''),
            (string)($post[$saidaKey] ?? '')
        );
    }

    /**
     * Gera série acumulada de horas de uso para gráficos de geradores.
     */
    protected function buildGeneratorAccumulatedLine(
        array $events,
        array $maintenances = [],
        array $historyEvents = []
    ): array
    {
        $line = [
            ['Data', 'G1 acumulado (h)', 'G2 acumulado (h)', 'Manutenção G1', 'Manutenção G2']
        ];

        if (empty($events)) {
            return $line;
        }

        $dailyVisibleUsage = $this->aggregateGeneratorDailyUsage($events);
        if (empty($dailyVisibleUsage)) {
            return $line;
        }

        $dailyHistoryUsage = !empty($historyEvents)
            ? $this->aggregateGeneratorDailyUsage($historyEvents)
            : $dailyVisibleUsage;
        if (empty($dailyHistoryUsage)) {
            return $line;
        }

        ksort($dailyHistoryUsage);
        $visibleDateSet = array_fill_keys(array_keys($dailyVisibleUsage), true);

        $g1Maint = $maintenances['g1'] ?? [];
        $g2Maint = $maintenances['g2'] ?? [];
        $g1MaintSet = array_fill_keys($g1Maint, true);
        $g2MaintSet = array_fill_keys($g2Maint, true);
        sort($g1Maint);
        sort($g2Maint);
        $g1MaintIdx = 0;
        $g2MaintIdx = 0;

        $accG1 = 0;
        $accG2 = 0;
        foreach ($dailyHistoryUsage as $date => $usage) {
            while ($g1MaintIdx < count($g1Maint) && $g1Maint[$g1MaintIdx] <= $date) {
                $accG1 = 0;
                $g1MaintIdx++;
            }
            while ($g2MaintIdx < count($g2Maint) && $g2Maint[$g2MaintIdx] <= $date) {
                $accG2 = 0;
                $g2MaintIdx++;
            }

            $accG1 += (int)($usage['g1'] ?? 0);
            $accG2 += (int)($usage['g2'] ?? 0);

            if (!isset($visibleDateSet[$date])) {
                continue;
            }

            $line[] = [
                date('d/m', strtotime($date)),
                round($accG1 / 60, 2),
                round($accG2 / 60, 2),
                isset($g1MaintSet[$date]) ? round($accG1 / 60, 2) : null,
                isset($g2MaintSet[$date]) ? round($accG2 / 60, 2) : null,
            ];
        }

        return $line;
    }

    /**
     * Consolida uso diário por gerador a partir dos eventos operacionais.
     */
    protected function aggregateGeneratorDailyUsage(array $events): array
    {
        if (empty($events)) {
            return [];
        }

        usort($events, static function ($a, $b) {
            $da = (string)($a->data ?? '');
            $db = (string)($b->data ?? '');
            if ($da === $db) {
                return (int)($a->id ?? 0) <=> (int)($b->id ?? 0);
            }
            return $da <=> $db;
        });

        $dailyUsage = [];
        foreach ($events as $event) {
            $date = !empty($event->data) ? date('Y-m-d', strtotime((string)$event->data)) : null;
            if ($date === null) {
                continue;
            }
            if (!isset($dailyUsage[$date])) {
                $dailyUsage[$date] = ['g1' => 0, 'g2' => 0];
            }
            $dailyUsage[$date]['g1'] += $this->resolveEventGeneratorUsageMinutes($event, 'g1');
            $dailyUsage[$date]['g2'] += $this->resolveEventGeneratorUsageMinutes($event, 'g2');
        }

        return $dailyUsage;
    }

    /**
     * Obtém a maior data disponível no conjunto de eventos.
     */
    protected function maxEventDate(array $events): ?string
    {
        $maxDate = null;
        foreach ($events as $event) {
            $date = !empty($event->data) ? date('Y-m-d', strtotime((string)$event->data)) : null;
            if ($date === null) {
                continue;
            }
            if ($maxDate === null || $date > $maxDate) {
                $maxDate = $date;
            }
        }

        return $maxDate;
    }

    /**
     * Carrega e indexa manutenção para reinício da contagem de uso.
     */
    protected function loadGeneratorMaintenanceTimeline(array $events): array
    {
        $timeline = ['g1' => [], 'g2' => []];
        if (empty($events) || !db_connect()->tableExists('gerador_manutencoes')) {
            return $timeline;
        }

        $maxDate = null;
        foreach ($events as $event) {
            $date = !empty($event->data) ? date('Y-m-d', strtotime((string)$event->data)) : null;
            if ($date === null) {
                continue;
            }
            if ($maxDate === null || $date > $maxDate) {
                $maxDate = $date;
            }
        }

        if ($maxDate === null) {
            return $timeline;
        }

        $rows = (new GeradorManutencao())
            ->select('gerador, data_manutencao')
            ->where('data_manutencao <=', $maxDate)
            ->whereIn('gerador', ['G1', 'G2'])
            ->orderBy('data_manutencao', 'asc')
            ->findAll();

        foreach ($rows as $row) {
            $date = !empty($row->data_manutencao) ? date('Y-m-d', strtotime((string)$row->data_manutencao)) : null;
            if ($date === null) {
                continue;
            }
            if (strtoupper((string)$row->gerador) === 'G2') {
                $timeline['g2'][] = $date;
            } else {
                $timeline['g1'][] = $date;
            }
        }

        return $timeline;
    }

    /**
     * Resolve minutos de uso por evento com fallback de colunas legadas.
     */
    protected function resolveEventGeneratorUsageMinutes(object $event, string $generator): int
    {
        $suffix = strtolower($generator) === 'g2' ? 'g2' : 'g1';
        $minField = 'uso_min_' . $suffix;
        $entradaField = 'leitura_' . $suffix;
        $saidaField = 'leitura_saida_' . $suffix;
        $tempoField = 'tempo_' . $suffix;

        $minutes = isset($event->{$minField}) ? (int)$event->{$minField} : 0;
        if ($minutes > 0) {
            return $minutes;
        }

        $minutes = $this->calculateGeneratorUsageMinutesFromHourValues(
            (string)($event->{$entradaField} ?? ''),
            (string)($event->{$saidaField} ?? '')
        );
        if ($minutes > 0) {
            return $minutes;
        }

        return $this->parseHourMeterToMinutes((string)($event->{$tempoField} ?? '')) ?? 0;
    }

    /**
     * Calcula diferença em minutos entre duas leituras de horímetro.
     */
    protected function calculateGeneratorUsageMinutesFromHourValues(string $entradaValue, string $saidaValue): int
    {
        $entrada = $this->parseHourMeterToMinutes($entradaValue);
        $saida = $this->parseHourMeterToMinutes($saidaValue);

        if ($entrada === null || $saida === null || $saida < $entrada) {
            return 0;
        }

        return $saida - $entrada;
    }

    /**
     * Converte valor textual de horímetro para minutos inteiros.
     */
    protected function parseHourMeterToMinutes(string $value): ?int
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^(\d{1,4}):(\d{2})$/', $value, $matches)) {
            $hours = (int)$matches[1];
            $minutes = (int)$matches[2];
            if ($minutes > 59) {
                return null;
            }
            return ($hours * 60) + $minutes;
        }

        if (preg_match('/^(\d{1,2}):(\d{2})(:\d{2})?$/', $value, $matches)) {
            $hours = (int)$matches[1];
            $minutes = (int)$matches[2];
            if ($minutes > 59) {
                return null;
            }
            return ($hours * 60) + $minutes;
        }

        return null;
    }

    /**
     * Normaliza lista de nomes de equipe para persistência textual.
     */
    protected function normalizeEquipeNames($input): ?string
    {
        $allowed = ['Helder', 'Nathan', 'Anderson'];
        $values = is_array($input) ? $input : [$input];
        $values = array_values(array_unique(array_filter(array_map('trim', $values), static function ($v) {
            return $v !== '';
        })));
        $valid = array_values(array_filter($values, static function ($v) use ($allowed) {
            return in_array($v, $allowed, true);
        }));

        return empty($valid) ? null : implode(',', $valid);
    }

    /**
     * Gera filtros do período anterior para cálculo de tendências.
     */
    protected function buildPreviousPeriodFilters(array $filters): ?array
    {
        if (isset($filters['date_from'], $filters['date_to'])) {
            try {
                $start = new \DateTimeImmutable((string)$filters['date_from']);
                $end = new \DateTimeImmutable((string)$filters['date_to']);
                if ($end < $start) {
                    return null;
                }
                $days = (int)$start->diff($end)->days + 1;
                $prevEnd = $start->modify('-1 day');
                $prevStart = $prevEnd->modify('-' . max(0, $days - 1) . ' days');

                $prev = $filters;
                $prev['date_from'] = $prevStart->format('Y-m-d');
                $prev['date_to'] = $prevEnd->format('Y-m-d');
                unset($prev['month'], $prev['year']);
                return $prev;
            } catch (\Throwable $e) {
                return null;
            }
        }

        if (isset($filters['month'], $filters['year'])) {
            $month = (int)$filters['month'];
            $year = (int)$filters['year'];
            $month--;
            if ($month < 1) {
                $month = 12;
                $year--;
            }

            $prev = $filters;
            $prev['month'] = $month;
            $prev['year'] = $year;
            unset($prev['date_from'], $prev['date_to']);
            return $prev;
        }

        return null;
    }

    /**
     * Monta indicadores comparativos do período atual versus anterior.
     */
    protected function buildTrendCards(object $sum, ?object $prevSum, float $avgEventCost, ?float $prevAvgEventCost): array
    {
        $cards = [
            'diesel_total' => $this->makeTrendValue((float)($sum->diesel_total ?? 0), (float)($prevSum->diesel_total ?? 0)),
            'kwh_total' => $this->makeTrendValue((float)($sum->kwh_total ?? 0), (float)($prevSum->kwh_total ?? 0)),
            'cedae_total' => $this->makeTrendValue((float)($sum->cedae_total ?? 0), (float)($prevSum->cedae_total ?? 0)),
            'light_total' => $this->makeTrendValue((float)($sum->light_total ?? 0), (float)($prevSum->light_total ?? 0)),
            'poco_consumo' => $this->makeTrendValue((float)($sum->poco_consumo ?? 0), (float)($prevSum->poco_consumo ?? 0)),
            'cedae_consumo' => $this->makeTrendValue((float)($sum->cedae_consumo ?? 0), (float)($prevSum->cedae_consumo ?? 0)),
            'valor_premontagem' => $this->makeTrendValue((float)($sum->valor_premontagem ?? 0), (float)($prevSum->valor_premontagem ?? 0)),
            'super_op' => $this->makeTrendValue((float)($sum->super_op ?? 0), (float)($prevSum->super_op ?? 0)),
            'avg_event_cost' => $this->makeTrendValue($avgEventCost, (float)($prevAvgEventCost ?? 0)),
            'total_consumo' => $this->makeTrendValue((float)($sum->total_consumo ?? 0), (float)($prevSum->total_consumo ?? 0)),
        ];

        return $cards;
    }

    /**
     * Anexa medições sonoras normalizadas ao payload operacional.
     */
    protected function appendNoiseMonitoringData(array &$data, array $post): void
    {
        $db = db_connect();
        // Ambientes sem migration de ruído não devem quebrar o cadastro.
        if (!$db->fieldExists('ruido_tribuna_c', 'operacional')) {
            return;
        }

        $tribuna = $this->parseNoiseReading($post['ruido_tribuna_c'] ?? null);
        $pista = $this->parseNoiseReading($post['ruido_pista_cavalo'] ?? null);
        $corredor = $this->parseNoiseReading($post['ruido_corredor_jb'] ?? null);
        $media = $this->parseNoiseReading($post['ruido_media'] ?? null);

        if ($media === null) {
            // Calcula média automática quando usuário informar só as três leituras fixas.
            $values = array_values(array_filter([$tribuna, $pista, $corredor], static fn ($v) => $v !== null));
            $media = !empty($values) ? round(array_sum($values) / count($values), 2) : null;
        }

        $data['ruido_tribuna_c'] = $tribuna;
        $data['ruido_pista_cavalo'] = $pista;
        $data['ruido_corredor_jb'] = $corredor;
        $data['ruido_media'] = $media;
        // Campo legado usado em telas antigas e exportações.
        $data['monitor_sonoro'] = $media;
    }

    /**
     * Converte leitura de ruído para float com tratamento de formato local.
     */
    protected function parseNoiseReading($value): ?float
    {
        if ($value === null) {
            return null;
        }
        $normalized = str_replace(',', '.', trim((string)$value));
        if ($normalized === '') {
            return null;
        }
        if (!is_numeric($normalized)) {
            return null;
        }
        return round((float)$normalized, 2);
    }

    /**
     * Avalia consumo CEDAE e retorna alerta quando houver anomalia.
     */
    protected function buildCedaeConsumptionAlert(array $events): array
    {
        $limit = 6.0;
        $overLimitEvents = [];

        // O alerta é por evento no período corrente filtrado.
        foreach ($events as $event) {
            $consumo = (float)($event->cedae_consumo ?? 0);
            if ($consumo <= $limit) {
                continue;
            }

            $eventName = trim((string)($event->nome ?? ''));
            $eventId = (int)($event->id ?? 0);
            if ($eventName === '') {
                $eventName = $eventId > 0 ? ('Evento #' . $eventId) : 'Evento sem nome';
            }

            $overLimitEvents[] = [
                'id' => $eventId > 0 ? $eventId : null,
                'name' => $eventName,
                'consumo' => $consumo,
            ];
        }

        $dedup = [];
        foreach ($overLimitEvents as $row) {
            // Evita repetição visual quando a consulta retorna entradas duplicadas do mesmo evento/consumo.
            $key = mb_strtolower(trim((string)$row['name'])) . '|' . number_format((float)$row['consumo'], 3, '.', '');
            $dedup[$key] = $row;
        }
        $overLimitEvents = array_values($dedup);

        return [
            'limit' => $limit,
            'events' => $overLimitEvents,
            'count' => count($overLimitEvents),
            'is_alert' => !empty($overLimitEvents),
        ];
    }

    /**
     * Avalia consumo do poço e retorna alerta quando houver anomalia.
     */
    protected function buildPocoConsumptionAlert(array $events): array
    {
        $limit = 6.0;
        $overLimitEvents = [];

        // Regra espelhada da CEDAE: alerta ao exceder 6 m³ por evento.
        foreach ($events as $event) {
            $consumo = (float)($event->poco_consumo ?? 0);
            if ($consumo <= $limit) {
                continue;
            }

            $eventName = trim((string)($event->nome ?? ''));
            $eventId = (int)($event->id ?? 0);
            if ($eventName === '') {
                $eventName = $eventId > 0 ? ('Evento #' . $eventId) : 'Evento sem nome';
            }

            $overLimitEvents[] = [
                'id' => $eventId > 0 ? $eventId : null,
                'name' => $eventName,
                'consumo' => $consumo,
            ];
        }

        $dedup = [];
        foreach ($overLimitEvents as $row) {
            $key = mb_strtolower(trim((string)$row['name'])) . '|' . number_format((float)$row['consumo'], 3, '.', '');
            $dedup[$key] = $row;
        }
        $overLimitEvents = array_values($dedup);

        return [
            'limit' => $limit,
            'events' => $overLimitEvents,
            'count' => count($overLimitEvents),
            'is_alert' => !empty($overLimitEvents),
        ];
    }

    /**
     * Calcula variação percentual e direção para cards de tendência.
     */
    protected function makeTrendValue(float $current, float $previous): array
    {
        if ($previous <= 0) {
            if ($current <= 0) {
                return ['text' => '0%', 'class' => 'text-muted'];
            }
            return ['text' => 'novo', 'class' => 'text-muted'];
        }

        $pct = (($current - $previous) / $previous) * 100;
        $sign = $pct > 0 ? '+' : '';
        $class = $pct > 0 ? 'text-danger' : ($pct < 0 ? 'text-success' : 'text-muted');

        return [
            'text' => $sign . number_format($pct, 1, ',', '.') . '%',
            'class' => $class,
        ];
    }

    /**
     * Indica se o usuário da sessão possui papel administrativo.
     */
    protected function isAdminUser(): bool
    {
        $user = session()->get('user');
        if (!$user) {
            return false;
        }

        $role = $user->role ?? ((int)($user->admin ?? 0) === 1 ? 'admin' : 'user');
        return in_array((string)$role, ['admin', 'superuser'], true);
    }
}
