<?php

namespace App\Services;

use App\Models\Propostas;

class DashboardService
{
    public function getDashboardData(): array
    {
        $anoAtual = (int)date('Y');
        $anoPassado = $anoAtual - 1;

        $items = new Propostas();
        $data = $items->select("YEAR(start_date) AS ano, QUARTER(start_date) AS trimestre, COUNT(*) AS total")
            ->where("YEAR(start_date) = $anoAtual OR YEAR(start_date) = $anoPassado")
            ->groupBy("ano, trimestre")
            ->orderBy("ano, trimestre")
            ->findAll();

        $totaisPassado = [0, 0, 0, 0];
        $totaisAtual = [0, 0, 0, 0];

        foreach ($data as $row) {
            $ano = $row->ano;
            $trimestre = $row->trimestre - 1;
            $total = $row->total;

            if ($ano == $anoPassado) {
                $totaisPassado[$trimestre] = (int)$total;
            } elseif ($ano == $anoAtual) {
                $totaisAtual[$trimestre] = (int)$total;
            }
        }

        $recent = $items->select()
            ->where('YEAR(start_date) >=', $anoAtual)
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->findAll();

        $columnChart = [
            ['Trimestre', (string)$anoPassado, (string)$anoAtual],
            ['1º Trimestre', $totaisPassado[0], $totaisAtual[0]],
            ['2º Trimestre', $totaisPassado[1], $totaisAtual[1]],
            ['3º Trimestre', $totaisPassado[2], $totaisAtual[2]],
            ['4º Trimestre', $totaisPassado[3], $totaisAtual[3]],
        ];

        $accepted = (new Propostas())
            ->where('status', '1')
            ->where('YEAR(start_date)', $anoAtual)
            ->selectSum('value')
            ->selectSum('charge')
            ->selectSum('rent')
            ->selectSum('third')
            ->first();

        $acceptedValue = (float)($accepted->value ?? 0);
        $acceptedCharge = (float)($accepted->charge ?? 0);
        $acceptedRent = (float)($accepted->rent ?? 0);
        $acceptedThird = (float)($accepted->third ?? 0);
        $saldo = $acceptedValue - $acceptedCharge - $acceptedRent - $acceptedThird;

        $pieChart = [
            ['Categoria', 'Valor'],
            ['Equipe', (int)$acceptedCharge],
            ['Locação', (int)$acceptedRent],
            ['Terceiros', (int)$acceptedThird],
            ['Saldo', (int)$saldo],
        ];

        $aceita = [
            'value' => $acceptedValue,
            'charge' => $acceptedCharge,
            'rent' => $acceptedRent,
            'third' => $acceptedThird,
        ];

        $analiseData = (new Propostas())
            ->where('status', '2')
            ->where('YEAR(start_date) >=', $anoAtual)
            ->where('start_date >=', date('Y-m-d'))
            ->selectSum('value')
            ->first();

        $analise = [
            'value' => (float)($analiseData->value ?? 0),
        ];

        $hasChartData = array_sum($totaisPassado) + array_sum($totaisAtual) > 0;
        $hasFinancialData = $aceita['value'] > 0 || $analise['value'] > 0;
        $hasRecentData = !empty($recent);

        return [
            'aceita' => $aceita,
            'analise' => $analise,
            'saldo' => $saldo,
            'columnChart' => json_encode($columnChart),
            'pieChart' => json_encode($pieChart),
            'recent' => $recent,
            'hasDashboardData' => $hasChartData || $hasFinancialData || $hasRecentData,
            'hasChartData' => $hasChartData,
            'hasRecentData' => $hasRecentData,
        ];
    }
}
