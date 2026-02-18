<?php

namespace App\Services;

use App\Models\GeradorManutencao;
use App\Models\Operacional;

class OperacionalService
{
    public function buildAggregates(array $filters, ?int $limit = null): array
    {
        $dataQuery = $this->applyFilters((new Operacional())->orderBy('data', 'desc'), $filters);
        if ($limit) {
            $dataQuery->limit($limit);
        }
        $data = $dataQuery->findAll();

        $sum = $this->applyFilters((new Operacional()), $filters)
            ->select('SUM(COALESCE(kwh_saida_g1,0) - COALESCE(kwh_entrada_g1,0)) AS kwh_g1', false)
            ->select('SUM(COALESCE(kwh_saida_g2,0) - COALESCE(kwh_entrada_g2,0)) AS kwh_g2', false)
            ->select('SUM((COALESCE(kwh_saida_g1,0) - COALESCE(kwh_entrada_g1,0)) + (COALESCE(kwh_saida_g2,0) - COALESCE(kwh_entrada_g2,0))) AS kwh_total', false)
            ->selectSum('super_op')
            ->selectSum('diesel_total')
            ->selectSum('tempo_g1')
            ->selectSum('tempo_g2')
            ->selectCount('id', 'event_count')
            ->selectSum('valor_premontagem')
            ->selectSum('diesel_litros')
            ->selectSum('cedae_consumo')
            ->selectSum('cedae_total')
            ->selectSum('light_consumo')
            ->selectSum('light_total')
            ->selectSum('total_consumo')
            ->first();

        $chart = $this->applyFilters((new Operacional()), $filters)
            ->selectSum('total_operacional')
            ->selectSum('diesel_total')
            ->selectSum('cedae_total')
            ->selectSum('light_total')
            ->first();

        $barRows = $this->applyFilters((new Operacional()), $filters)
            ->select('tipo, SUM(total_operacional) AS total_operacional, SUM(diesel_total) AS diesel_total, SUM(cedae_total) AS cedae_total, SUM(light_total) AS light_total, SUM(total_consumo) AS total_consumo')
            ->groupBy('tipo')
            ->findAll();

        $bars = $this->mapBars($barRows);

        return [
            'data' => $data,
            'sum' => $sum,
            'chart' => $chart,
            'bars' => $bars,
        ];
    }

    public function monthlySeries(int $year): array
    {
        $monthTotals = (new Operacional())
            ->select('MONTH(data) AS mes, SUM(total_consumo) AS total_consumo')
            ->where('YEAR(data)', $year)
            ->where('flag', 1)
            ->groupBy('mes')
            ->findAll();

        $lineValues = array_fill(1, 12, 0.0);

        foreach ($monthTotals as $row) {
            $mes = (int)$row->mes;
            if ($mes >= 1 && $mes <= 12) {
                $lineValues[$mes] = (float)$row->total_consumo;
            }
        }

        $line = [
            ['Janeiro', $lineValues[1]],
            ['Fevereiro', $lineValues[2]],
            ['Março', $lineValues[3]],
            ['Abril', $lineValues[4]],
            ['Maio', $lineValues[5]],
            ['Junho', $lineValues[6]],
            ['Julho', $lineValues[7]],
            ['Agosto', $lineValues[8]],
            ['Setembro', $lineValues[9]],
            ['Outubro', $lineValues[10]],
            ['Novembro', $lineValues[11]],
            ['Dezembro', $lineValues[12]],
        ];

        return ['line' => $line];
    }

    public function monthlyComparison(int $year): array
    {
        $prevYear = $year - 1;
        $rows = (new Operacional())
            ->select('YEAR(data) AS ano, MONTH(data) AS mes, SUM(total_consumo) AS total_consumo')
            ->whereIn('YEAR(data)', [$prevYear, $year])
            ->where('flag', 1)
            ->groupBy('ano, mes')
            ->findAll();

        $currValues = array_fill(1, 12, 0.0);
        $prevValues = array_fill(1, 12, 0.0);

        foreach ($rows as $row) {
            $mes = (int)$row->mes;
            if ($mes < 1 || $mes > 12) {
                continue;
            }
            if ((int)$row->ano === $year) {
                $currValues[$mes] = (float)$row->total_consumo;
            } elseif ((int)$row->ano === $prevYear) {
                $prevValues[$mes] = (float)$row->total_consumo;
            }
        }

        return [
            ['Mês', (string)$prevYear, (string)$year],
            ['Janeiro', $prevValues[1], $currValues[1]],
            ['Fevereiro', $prevValues[2], $currValues[2]],
            ['Março', $prevValues[3], $currValues[3]],
            ['Abril', $prevValues[4], $currValues[4]],
            ['Maio', $prevValues[5], $currValues[5]],
            ['Junho', $prevValues[6], $currValues[6]],
            ['Julho', $prevValues[7], $currValues[7]],
            ['Agosto', $prevValues[8], $currValues[8]],
            ['Setembro', $prevValues[9], $currValues[9]],
            ['Outubro', $prevValues[10], $currValues[10]],
            ['Novembro', $prevValues[11], $currValues[11]],
            ['Dezembro', $prevValues[12], $currValues[12]],
        ];
    }

    public function monthlySeriesByFilters(array $filters): array
    {
        $rows = $this->applyFilters((new Operacional()), $filters)
            ->select('YEAR(data) AS ano, MONTH(data) AS mes, SUM(total_consumo) AS total_consumo')
            ->groupBy('ano, mes')
            ->orderBy('ano', 'asc')
            ->orderBy('mes', 'asc')
            ->findAll();

        $chart = [['Período', 'Custo']];
        foreach ($rows as $row) {
            $mes = (int)($row->mes ?? 0);
            $ano = (int)($row->ano ?? 0);
            if ($mes < 1 || $mes > 12 || $ano <= 0) {
                continue;
            }

            $chart[] = [
                $this->monthNamePt($mes) . '/' . $ano,
                (float)($row->total_consumo ?? 0),
            ];
        }

        return $chart;
    }

    public function topEventsByCost(array $filters, int $limit = 10): array
    {
        $rows = $this->applyFilters((new Operacional()), $filters)
            ->select('id, nome, diesel_total, cedae_total, light_total, total_consumo AS total')
            ->orderBy('total', 'desc')
            ->orderBy('total', 'desc')
            ->limit($limit)
            ->findAll();

        $chart = [
            ['Evento', 'Custo total', ['role' => 'annotation'], ['role' => 'tooltip', 'type' => 'string'], ['role' => 'style'], ['role' => 'annotationText']]
        ];

        foreach ($rows as $row) {
            $value = (float)$row->total;
            $tooltip = "Diesel: R$ " . number_format((float)$row->diesel_total, 2, ',', '.') .
                "\\nCedae: R$ " . number_format((float)$row->cedae_total, 2, ',', '.') .
                "\\nLight: R$ " . number_format((float)$row->light_total, 2, ',', '.');
            $chart[] = [
                $row->nome,
                $value,
                '',
                $tooltip,
                '',
                (string)$row->id
            ];
        }

        return $chart;
    }

    public function latestMonthWithFlag(int $year, int $flag): int
    {
        $latest = (new Operacional())
            ->selectMax('data', 'max_date')
            ->where('YEAR(data)', $year)
            ->where('flag', $flag)
            ->first();

        if ($latest && !empty($latest->max_date)) {
            return (int)date('m', strtotime($latest->max_date));
        }

        return (int)date('m');
    }

    public function getGeneratorUsageStatus(int $limitHours = 200): array
    {
        $limitMin = $limitHours * 60;

        $statusG1 = $this->buildGeneratorStatus('G1', 'uso_min_g1', 'leitura_g1', 'leitura_saida_g1', 'tempo_g1', $limitMin);
        $statusG2 = $this->buildGeneratorStatus('G2', 'uso_min_g2', 'leitura_g2', 'leitura_saida_g2', 'tempo_g2', $limitMin);

        return [
            'g1' => $statusG1,
            'g2' => $statusG2,
            'has_alert' => $statusG1['is_limit_reached'] || $statusG2['is_limit_reached'],
            'has_warning' => $statusG1['is_warning'] || $statusG2['is_warning'],
        ];
    }

    private function applyFilters($builder, array $filters)
    {
        if (isset($filters['date_from'])) {
            $builder->where('data >=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $builder->where('data <=', $filters['date_to']);
        }
        if (isset($filters['month'])) {
            $builder->where('MONTH(data)', $filters['month']);
        }
        if (isset($filters['year'])) {
            $builder->where('YEAR(data)', $filters['year']);
        }
        if (isset($filters['flag'])) {
            $builder->where('flag', $filters['flag']);
        }
        if (!empty($filters['tipos'])) {
            $builder->whereIn('tipo', $filters['tipos']);
        }

        return $builder;
    }

    private function mapBars(array $rows): array
    {
        $init = function () {
            return (object)[
                'total_operacional' => 0,
                'diesel_total' => 0,
                'cedae_total' => 0,
                'light_total' => 0,
                'total_consumo' => 0,
            ];
        };

        $bars = [
            'social' => $init(),
            'corporativo' => $init(),
            'entretenimento' => $init(),
        ];

        foreach ($rows as $row) {
            $tipo = strtolower($row->tipo ?? '');
            if (isset($bars[$tipo])) {
                $bars[$tipo] = $row;
            }
        }

        return $bars;
    }

    private function monthNamePt(int $month): string
    {
        $months = [
            1 => 'Jan',
            2 => 'Fev',
            3 => 'Mar',
            4 => 'Abr',
            5 => 'Mai',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Ago',
            9 => 'Set',
            10 => 'Out',
            11 => 'Nov',
            12 => 'Dez',
        ];

        return $months[$month] ?? (string)$month;
    }

    private function buildGeneratorStatus(
        string $gerador,
        string $minColumn,
        string $entradaColumn,
        string $saidaColumn,
        string $tempoColumn,
        int $limitMin
    ): array {
        $ultimaManutencao = null;
        if (db_connect()->tableExists('gerador_manutencoes')) {
            $ultimaManutencao = (new GeradorManutencao())
                ->where('gerador', $gerador)
                ->orderBy('data_manutencao', 'desc')
                ->orderBy('id', 'desc')
                ->first();
        }

        $dataBase = $ultimaManutencao->data_manutencao ?? null;
        $totalMin = $this->sumUsageMinutes($minColumn, $entradaColumn, $saidaColumn, $tempoColumn, $dataBase);
        $percent = $limitMin > 0 ? min(100, ($totalMin / $limitMin) * 100) : 0;
        $remainingMin = max(0, $limitMin - $totalMin);
        $avgDailyRecentMin = $this->recentAverageUsagePerDay($minColumn, $entradaColumn, $saidaColumn, $tempoColumn, 30);
        $daysToMaintenance = ($avgDailyRecentMin > 0)
            ? (int)ceil($remainingMin / $avgDailyRecentMin)
            : null;

        return [
            'gerador' => $gerador,
            'total_min' => $totalMin,
            'total_hhmm' => $this->formatMinutesToHHMM($totalMin),
            'remaining_min' => $remainingMin,
            'remaining_hhmm' => $this->formatMinutesToHHMM($remainingMin),
            'limit_min' => $limitMin,
            'limit_hhmm' => $this->formatMinutesToHHMM($limitMin),
            'progress_percent' => round($percent, 1),
            'is_warning' => $totalMin >= (int)($limitMin * 0.9),
            'is_limit_reached' => $totalMin >= $limitMin,
            'last_maintenance_date' => $dataBase,
            'avg_daily_recent_min' => round($avgDailyRecentMin, 2),
            'days_to_maintenance' => $daysToMaintenance,
        ];
    }

    private function sumUsageMinutes(
        string $minColumn,
        string $entradaColumn,
        string $saidaColumn,
        string $tempoColumn,
        ?string $dateFrom
    ): int {
        $db = db_connect();
        $hasMinColumn = $db->fieldExists($minColumn, 'operacional');
        $hasEntradaColumn = $db->fieldExists($entradaColumn, 'operacional');
        $hasSaidaColumn = $db->fieldExists($saidaColumn, 'operacional');
        $hasTempoColumn = $db->fieldExists($tempoColumn, 'operacional');

        if (!$hasMinColumn && (!$hasEntradaColumn || !$hasSaidaColumn) && !$hasTempoColumn) {
            return 0;
        }

        $select = [];
        if ($hasMinColumn) {
            $select[] = $minColumn;
        }
        if ($hasEntradaColumn) {
            $select[] = $entradaColumn;
        }
        if ($hasSaidaColumn) {
            $select[] = $saidaColumn;
        }
        if ($hasTempoColumn) {
            $select[] = $tempoColumn;
        }

        $builder = (new Operacional())
            ->select(implode(', ', $select))
            ->where('flag', 1);

        if (!empty($dateFrom)) {
            $builder->where('data >=', $dateFrom);
        }

        $rows = $builder->findAll();
        $total = 0;

        foreach ($rows as $row) {
            $stored = $hasMinColumn && isset($row->{$minColumn}) ? (int)$row->{$minColumn} : 0;
            if ($stored > 0) {
                $total += $stored;
                continue;
            }

            $fallback = $this->calculateDiffMinutesFromHourMeter(
                $hasEntradaColumn ? (string)($row->{$entradaColumn} ?? '') : '',
                $hasSaidaColumn ? (string)($row->{$saidaColumn} ?? '') : ''
            );
            if ($fallback > 0) {
                $total += $fallback;
                continue;
            }

            if ($hasTempoColumn) {
                $tempo = $this->parseHourMeterToMinutes((string)($row->{$tempoColumn} ?? '')) ?? 0;
                $total += max(0, $tempo);
            }
        }

        return $total;
    }

    private function calculateDiffMinutesFromHourMeter(string $entrada, string $saida): int
    {
        $inicio = $this->parseHourMeterToMinutes($entrada);
        $fim = $this->parseHourMeterToMinutes($saida);

        if ($inicio === null || $fim === null || $fim < $inicio) {
            return 0;
        }

        return $fim - $inicio;
    }

    private function parseHourMeterToMinutes(string $value): ?int
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

    private function formatMinutesToHHMM(int $minutes): string
    {
        $hours = intdiv(max(0, $minutes), 60);
        $mins = max(0, $minutes) % 60;

        return str_pad((string)$hours, 2, '0', STR_PAD_LEFT) . ':' . str_pad((string)$mins, 2, '0', STR_PAD_LEFT);
    }

    private function recentAverageUsagePerDay(
        string $minColumn,
        string $entradaColumn,
        string $saidaColumn,
        string $tempoColumn,
        int $daysWindow = 30
    ): float {
        $db = db_connect();
        $hasMinColumn = $db->fieldExists($minColumn, 'operacional');
        $hasEntradaColumn = $db->fieldExists($entradaColumn, 'operacional');
        $hasSaidaColumn = $db->fieldExists($saidaColumn, 'operacional');
        $hasTempoColumn = $db->fieldExists($tempoColumn, 'operacional');

        if (!$hasMinColumn && (!$hasEntradaColumn || !$hasSaidaColumn) && !$hasTempoColumn) {
            return 0.0;
        }

        $select = ['data'];
        if ($hasMinColumn) {
            $select[] = $minColumn;
        }
        if ($hasEntradaColumn) {
            $select[] = $entradaColumn;
        }
        if ($hasSaidaColumn) {
            $select[] = $saidaColumn;
        }
        if ($hasTempoColumn) {
            $select[] = $tempoColumn;
        }

        $startDate = date('Y-m-d', strtotime('-' . max(1, $daysWindow - 1) . ' days'));
        $rows = (new Operacional())
            ->select(implode(', ', $select))
            ->where('flag', 1)
            ->where('data >=', $startDate)
            ->findAll();

        if (empty($rows)) {
            return 0.0;
        }

        $totalMin = 0;
        foreach ($rows as $row) {
            $minutes = $hasMinColumn ? (int)($row->{$minColumn} ?? 0) : 0;
            if ($minutes <= 0) {
                $minutes = $this->calculateDiffMinutesFromHourMeter(
                    $hasEntradaColumn ? (string)($row->{$entradaColumn} ?? '') : '',
                    $hasSaidaColumn ? (string)($row->{$saidaColumn} ?? '') : ''
                );
            }
            if ($minutes <= 0 && $hasTempoColumn) {
                $minutes = $this->parseHourMeterToMinutes((string)($row->{$tempoColumn} ?? '')) ?? 0;
            }
            $totalMin += max(0, $minutes);
        }

        return $totalMin > 0 ? ($totalMin / max(1, $daysWindow)) : 0.0;
    }
}
