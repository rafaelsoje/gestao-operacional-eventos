<?php

namespace App\Services;

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
}
