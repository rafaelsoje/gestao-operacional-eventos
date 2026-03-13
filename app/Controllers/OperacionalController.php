<?php

namespace App\Controllers;

use App\Models\GeradorManutencao;
use App\Models\Logs;
use App\Models\Operacional;
use App\Services\OperacionalService;
use App\Validation\FormRequest\GeradorManutencaoFormRequest;
use CodeIgniter\HTTP\ResponseInterface;

class OperacionalController extends Home
{
    public function operacional(): string|ResponseInterface {
        $service = new OperacionalService();
        $year = (int)date('Y');

        $get = '';
        $search = null;
        $tipo = [];

        # caso receba ?id=0 no GET direciona para lançamentos futuros
        if($this->request->getGet()){
            $get = $this->request->getGet('id');
        }

        $chart = (object)[
            'total_operacional' => 0,
            'diesel_total' => 0,
            'cedae_total' => 0,
            'light_total' => 0,
            'total_consumo' => 0,
        ];
        $barEntretenimento = clone $chart;
        $barSocial = clone $chart;
        $barCorporativo = clone $chart;

        if ($this->request->getMethod() === 'post') {
            $post = $this->request->getPost();
            $dataInicialRaw = $post['data_inicial'] ?? null;
            $dataFinalRaw = $post['data_final'] ?? null;
            if (!is_string($dataInicialRaw) || !is_string($dataFinalRaw)) {
                return redirect()->back()->with('error', 'Formato de dados inválido para pesquisa.');
            }

            $dataInicial = trim($dataInicialRaw);
            $dataFinal = trim($dataFinalRaw);

            if ($dataInicial === '' || $dataFinal === '') {
                return redirect()->back()->with('error', 'Data da pesquisa incorreta');
            }

            if (!$this->isValidDateYmd($dataInicial) || !$this->isValidDateYmd($dataFinal)) {
                return redirect()->back()->with('error', 'Formato de data inválido. Use AAAA-MM-DD.');
            }

            if ($dataInicial > $dataFinal) {
                return redirect()->back()->with('error', 'A data inicial deve ser anterior à data final.');
            }

            $selectedTipos = $this->sanitizeSearchTipos($post['tipos'] ?? null);
            if ($selectedTipos === null) {
                return redirect()->back()->with('error', 'Tipo de dado inválido em tipos de evento.');
            }

            if (empty($selectedTipos)) {
                return redirect()->back()->with('error', 'Nenhum tipo de evento selecionado!');
            }

            return redirect()->to(url_to('search.operacional.get') . '?' . http_build_query([
                'data_inicial' => $dataInicial,
                'data_final' => $dataFinal,
                'tipos' => $selectedTipos,
            ]), 303);
        }

        $dataInicialGetRaw = $this->request->getGet('data_inicial');
        $dataFinalGetRaw = $this->request->getGet('data_final');
        if (($dataInicialGetRaw !== null && !is_string($dataInicialGetRaw)) || ($dataFinalGetRaw !== null && !is_string($dataFinalGetRaw))) {
            return redirect()->route('home')->with('error', 'Formato de dados inválido para pesquisa.');
        }

        $dataInicialGet = trim((string)($dataInicialGetRaw ?? ''));
        $dataFinalGet = trim((string)($dataFinalGetRaw ?? ''));
        if ($dataInicialGet !== '' && $dataFinalGet !== '') {
            if (!$this->isValidDateYmd($dataInicialGet) || !$this->isValidDateYmd($dataFinalGet)) {
                return redirect()->route('home')->with('error', 'Formato de data inválido. Use AAAA-MM-DD.');
            }

            if ($dataInicialGet > $dataFinalGet) {
                return redirect()->route('home')->with('error', 'A data inicial deve ser anterior à data final.');
            }

            $tipos = $this->sanitizeSearchTipos($this->request->getGet('tipos'));
            if ($tipos === null) {
                return redirect()->route('home')->with('error', 'Tipo de dado inválido em tipos de evento.');
            }
            if (empty($tipos)) {
                $tipos = ['social', 'corporativo', 'entretenimento'];
            }

            $tipo = $tipos;
            $search = [
                'data_inicial' => $dataInicialGet,
                'data_final' => $dataFinalGet,
            ];
            $filters = [
                'date_from' => $dataInicialGet,
                'date_to' => $dataFinalGet,
                'flag' => 1,
                'tipos' => $tipos,
            ];

            $result = $service->buildAggregates($filters);
            $data = $result['data'];
            $sum = $result['sum'];
            $chart = $result['chart'];
            $barSocial = $result['bars']['social'];
            $barCorporativo = $result['bars']['corporativo'];
            $barEntretenimento = $result['bars']['entretenimento'];
        } else if($get == 0){

           

            $month = (int)date('m') + 1;
            $filters = [
                'month' => $month,
                'year' => $year,
                'flag' => 0,
            ];

            $result = $service->buildAggregates($filters, 4);
            $data = $result['data'];
            $sum = $result['sum'];
            $chart = $result['chart'];
            $barSocial = $result['bars']['social'];
            $barCorporativo = $result['bars']['corporativo'];
            $barEntretenimento = $result['bars']['entretenimento'];

        }else{

          

            # obtem o mês atual;
            $mesSearch = $service->latestMonthWithFlag($year, 1);

            $filters = [
                'month' => $mesSearch,
                'year' => $year,
                'flag' => 1,
            ];

            $result = $service->buildAggregates($filters);
            $data = $result['data'];
            $sum = $result['sum'];
            $chart = $result['chart'];
            $barSocial = $result['bars']['social'];
            $barCorporativo = $result['bars']['corporativo'];
            $barEntretenimento = $result['bars']['entretenimento'];


        }
        
        $lineTitle = 'Custo anual';
        if (isset($search)) {
            $line = $service->monthlySeriesByFilters($filters ?? []);
            $lineTitle = 'Custo no período pesquisado';
        } else {
            $line = $service->monthlyComparison($year);
        }

        $donutChart = [
            ['Categorria', 'valor'],
            ['Equipe Operacional', (float)$chart->total_operacional],
            ['Diesel', (float)$chart->diesel_total],
            ['Cedae', (float)$chart->cedae_total],
            ['light', (float)$chart->light_total],
        ];

        $bar = [
            ['Custo', 'Equipe Operacional','Gerador', 'Cedae', 'Light'],
            ['Entretenimento',
                (float)$barEntretenimento->total_operacional,
                (float)$barEntretenimento->diesel_total,
                (float)$barEntretenimento->cedae_total,
                (float)$barEntretenimento->light_total,
            ],
            ['Social',
                (float)$barSocial->total_operacional,
                (float)$barSocial->diesel_total,
                (float)$barSocial->cedae_total,
                (float)$barSocial->light_total,
            ],
            ['Corporativo',
                (float)$barCorporativo->total_operacional,
                (float)$barCorporativo->diesel_total,
                (float)$barCorporativo->cedae_total,
                (float)$barCorporativo->light_total,
            ],
        ];

        $barchart = [
            ['tipo', 'Custo médio por evento'],
            [
                'Entretenimento',
                ((int)($barEntretenimento->event_count ?? 0) > 0)
                    ? ((float)$barEntretenimento->total_consumo / (int)$barEntretenimento->event_count)
                    : 0
            ],
            [
                'Social',
                ((int)($barSocial->event_count ?? 0) > 0)
                    ? ((float)$barSocial->total_consumo / (int)$barSocial->event_count)
                    : 0
            ],
            [
                'Corporativo',
                ((int)($barCorporativo->event_count ?? 0) > 0)
                    ? ((float)$barCorporativo->total_consumo / (int)$barCorporativo->event_count)
                    : 0
            ],
        ];
        $publicByCategory = [
            ['Categoria', 'Público total'],
            ['Entretenimento', (float)($barEntretenimento->publico_total ?? 0)],
            ['Social', (float)($barSocial->publico_total ?? 0)],
            ['Corporativo', (float)($barCorporativo->publico_total ?? 0)],
        ];
        $generatorSourceData = $data ?? [];
        $generatorHistoryData = $generatorSourceData;
        if (!isset($search)) {
            $generatorMaxDate = $this->maxEventDate($generatorSourceData);
            if ($generatorMaxDate !== null) {
                $generatorHistoryFilters = [
                    'date_to' => $generatorMaxDate,
                    'flag' => (int)($filters['flag'] ?? 1),
                ];
                if (!empty($filters['tipos'])) {
                    $generatorHistoryFilters['tipos'] = $filters['tipos'];
                }
                $generatorHistoryData = $service->buildAggregates($generatorHistoryFilters)['data'] ?? $generatorSourceData;
            }
        }

        $generatorMaintenances = $this->loadGeneratorMaintenanceTimeline($generatorHistoryData);
        $generatorAccumLine = $this->buildGeneratorAccumulatedLine(
            $generatorSourceData,
            $generatorMaintenances,
            $generatorHistoryData
        );

        

        $eventCount = (int)($sum->event_count ?? 0);
        $avgEventCost = $eventCount > 0 ? ((float)$sum->total_consumo / $eventCount) : 0;
        $generatorUsage = $service->getGeneratorUsageStatus();
        $previousFilters = $this->buildPreviousPeriodFilters($filters ?? []);
        $prevSum = null;
        $prevAvgEventCost = null;
        if (!empty($previousFilters)) {
            $prevResult = $service->buildAggregates($previousFilters);
            $prevSum = $prevResult['sum'] ?? null;
            $prevEventCount = (int)($prevSum->event_count ?? 0);
            $prevAvgEventCost = $prevEventCount > 0 ? ((float)$prevSum->total_consumo / $prevEventCount) : 0;
        }
        $trendCards = $this->buildTrendCards($sum, $prevSum, $avgEventCost, $prevAvgEventCost);
        $cedaeAlert = $this->buildCedaeConsumptionAlert($data);
        $pocoAlert = $this->buildPocoConsumptionAlert($data);

        $payload = [
            'sum' => $sum,
            'avg_event_cost' => $avgEventCost,
            'trend_cards' => $trendCards,
            'data' => $data,
            'generator_usage' => $generatorUsage,
            'donut' => json_encode($donutChart),
            'bar' => json_encode($bar),
            'barChart' => json_encode($barchart),
            'publicByCategory' => json_encode($publicByCategory),
            'line' => json_encode($line),
            'lineTitle' => $lineTitle,
            'lineGenerator' => json_encode($generatorAccumLine),
            'cedae_alert' => $cedaeAlert,
            'poco_alert' => $pocoAlert,
        ];

        if(isset($search)){
            $topEvents = $service->topEventsByCost($filters ?? []);
            return view('operacional', $payload + [
                'operacional' => true,
                'topEvents' => json_encode($topEvents),
                'search' => $search,
                'tipo' => $tipo,
            ]);
        }else{
            if($get == 0){
                $filters = [
                    'month' => (int)date('m') + 1,
                    'year' => $year,
                    'flag' => 0,
                ];
                $topEvents = $service->topEventsByCost($filters);
                return view('operacional', $payload + [
                    'planejado' => true,
                    'topEvents' => json_encode($topEvents),
                    'get' => $get,
                    'mes' => $this->mes(date('m') + 1),
                ]);
            }
            $filters = [
                'month' => $mesSearch,
                'year' => $year,
                'flag' => 1,
            ];
            $topEvents = $service->topEventsByCost($filters);
            return view('operacional', $payload + [
                'operacional' => true,
                'topEvents' => json_encode($topEvents),
                'mes' => $this->mes($mesSearch),
            ]);
        }
    }

    public function downloadUpload(string $date, string $file): ResponseInterface
    {
        if (!session()->get('auth')) {
            return redirect()->route('login')->with('error', 'Sessão expirada. Faça login novamente.');
        }

        $safeDate = preg_replace('/[^0-9\-]/', '', $date);
        $safeFile = basename($file);

        if ($safeDate !== $date || $safeFile !== $file) {
            return $this->response->setStatusCode(404);
        }

        $path = WRITEPATH . 'uploads/' . $safeDate . '/' . $safeFile;
        $legacyPath = FCPATH . 'uploads/' . $safeDate . '/' . $safeFile;

        if (!is_file($path) && is_file($legacyPath)) {
            if (!is_dir(WRITEPATH . 'uploads/' . $safeDate)) {
                mkdir(WRITEPATH . 'uploads/' . $safeDate, 0755, true);
            }
            @rename($legacyPath, $path);
        }

        if (!is_file($path)) {
            return $this->response->setStatusCode(404);
        }

        return $this->response->download($path, null);
    }

    public function cadastroOperacional(): string {
        return view('cadastroOperacional',['cadastroOp' => true]);
    }

    public function operacionalLista(): string {
        $data = (new Operacional())->where('YEAR(data)', date('Y'))->where('flag', 1)->findAll();

        return view('operacionalLista', ['data'=> $data, 'listaOp' => true]);
    }

    public function cadastroOperacionalStore(): ResponseInterface {

        $post = $this->request->getPost();

        if (isset($post['id']) && !$this->isAdminUser()) {
            return redirect()->route('operacional.lista')->with('error', 'Apenas administradores podem atualizar cadastro operacional.');
        }

        helper('money');

        $valor_diesel = parse_money_br($post['valor_diesel'] ?? null);
        $valor_cedae = parse_money_br($post['cedae_valor'] ?? null);
        $valor_light = parse_money_br($post['light_valor'] ?? null);
        $cache_premontagem = parse_money_br($post['cache_premontagem'] ?? null);
        $valor_operacional = parse_money_br($post['valor_operacional'] ?? null);

        $cedae_total = money_to_decimal($post['cedae_total'] ?? null);
        $light_total = money_to_decimal($post['light_total'] ?? null);
        $diesel_total = money_to_decimal($post['diesel_total'] ?? null);
        $total_consumo = money_to_decimal($post['total_consumo'] ?? null);
        $valor_premontagem = money_to_decimal($post['valor_premontagem'] ?? null);
        $super_op = money_to_decimal($post['super_op'] ?? null);
        $total_operacional = money_to_decimal($post['total_operacional'] ?? null);
        $usoMinG1 = $this->calculateGeneratorUsageMinutesFromPost($post, 'leitura_g1', 'leitura_saida_g1');
        $usoMinG2 = $this->calculateGeneratorUsageMinutesFromPost($post, 'leitura_g2', 'leitura_saida_g2');
        $equipeManha = $this->normalizeEquipeNames($post['equipe_manha'] ?? []);
        $equipeNoite = $this->normalizeEquipeNames($post['equipe_noite'] ?? []);
        // Mantém compatibilidade: se o formulário antigo enviar apenas `monitor_sonoro`, ainda persistimos corretamente.
        $noiseAverage = $this->parseNoiseReading($post['ruido_media'] ?? ($post['monitor_sonoro'] ?? null));


        $data = [
            'nome' => $post['nome'],
            'data' => $post['data'],
            'tipo' => $post['tipo'],
            'hora_inicio' => isset($post['hora_inicio']) ? $post['hora_inicio'].':00' : null,
            'hora_fim' => isset($post['hora_fim'])? $post['hora_fim'].':00' : null,
            'hora_extra' => intval(isset($post['hora_extra'])? $post['hora_extra'] : null),
            'temp_inicio' => isset($post['temp_inicio']) ? $post['temp_inicio'] : null,
            'temp_fim' => isset($post['temp_fim'])? $post['temp_fim'] : null,
            'qtd_fornecedor' =>  intval(isset($post['qtd_fornecedor'])? $post['qtd_fornecedor']: null),
            'monitor_sonoro' => $noiseAverage,
            'liga_g1' => isset($post['liga_g1']) ? $post['liga_g1'] : null,
            'desliga_g1' => isset($post['desliga_g1']) ? $post['desliga_g1'] : null,
            'tempo_g1' => isset($post['tempo_g1']) ? $post['tempo_g1'] : null,
            'leitura_g1' => isset($post['leitura_g1']) ? $post['leitura_g1'] : null,
            'leitura_saida_g1' => isset($post['leitura_saida_g1']) ? $post['leitura_saida_g1'] : null,
            'uso_min_g1' => $usoMinG1,
            'kwh_entrada_g1' => isset($post['kwh_entrada_g1']) ? $post['kwh_entrada_g1'] : null,
            'kwh_saida_g1' => isset($post['kwh_saida_g1']) ? $post['kwh_saida_g1'] : null,
            'liga_g2' => isset($post['liga_g2']) ? $post['liga_g2'] : null,
            'desliga_g2' => isset($post['desliga_g2']) ? $post['desliga_g2'] : null,
            'tempo_g2' => isset($post['tempo_g2']) ? $post['tempo_g2'] : null,
            'leitura_g2' => isset($post['leitura_g2']) ? $post['leitura_g2'] : null,
            'leitura_saida_g2' => isset($post['leitura_saida_g2']) ? $post['leitura_saida_g2'] : null,
            'uso_min_g2' => $usoMinG2,
            'kwh_entrada_g2' => isset($post['kwh_entrada_g2']) ? $post['kwh_entrada_g2'] : null,
            'kwh_saida_g2' => isset($post['kwh_saida_g2']) ? $post['kwh_saida_g2'] : null,
            'premontagem' => intval($post['premontagem']),
            'cache_premontagem' => $cache_premontagem,
            'valor_premontagem' => $valor_premontagem,
            'operacional' => intval($post['operacional']),
            'valor_operacional' => $valor_operacional,
            'super_op'=> $super_op,
            'total_operacional' => $total_operacional,
            'seguranca' => intval(isset($post['seguranca']) ? $post['seguranca'] : null),
            'bombeiro' => intval(isset($post['bombeiro']) ? $post['bombeiro'] : null),
            'limpeza' => intval(isset($post['limpeza']) ? $post['limpeza'] : null),
            'equipe_manha' => $equipeManha,
            'equipe_noite' => $equipeNoite,
            'publico_final' => intval(isset($post['publico_final']) ? $post['publico_final'] : null),
            'publico_tribuna' => intval(isset($post['publico_tribuna'])? $post['publico_tribuna'] : null),
            'publico_saida' => intval(isset($post['publico_saida']) ? $post['publico_saida'] : null),
            'valor_diesel' => $valor_diesel,
            'diesel_inicio' => intval($post['diesel_inicio']),
            'diesel_fim' => intval($post['diesel_fim']),
            'diesel_litros' => intval($post['diesel_litros']),
            'diesel_total' => $diesel_total,
            'cedae_valor' => $valor_cedae,
            'cedae_inicio' => intval($post['cedae_inicio']),
            'cedae_fim' => intval($post['cedae_fim']),
            'cedae_consumo' => intval($post['cedae_consumo']),
            'cedae_total' => $cedae_total,
            'light_valor' => $valor_light,
            'light_inicio' => intval($post['light_inicio']),
            'light_fim' => intval($post['light_fim']),
            'light_consumo' => intval($post['light_consumo']),
            'light_total' => $light_total,
            'poco_inicio' => intval($post['poco_inicio'] ?? 0),
            'poco_fim' => intval($post['poco_fim'] ?? 0),
            'poco_consumo' => intval($post['poco_consumo'] ?? 0),
            'total_consumo' => $total_consumo,
            'descricao' => $post['descricao'],
            'flag' => intval($post['flag']),
        ];
        $this->appendNoiseMonitoringData($data, $post);

        $user = session()->get('user');

        if(isset($post['id'])){

            if((new Operacional())->update($post['id'], $data)){

                $log = [
                    'title' => 'ATUALIZAÇÃO OPERACIONAL',
                    'description' => "$user->name atualizou um cadastro operacional. " . $data['nome'] . " em " . date('Y-m-d H:i:s'),
                ];

                (new Logs())->insert($log);
                $this->notifyTelegram($user->name . " atualizou um cadastro operacional " . $data['nome']);
                if($post['flag'] == 0){
                    return redirect()->route('planejamento.lista')->with('success', "Atualização realizada com sucesso!");
                }

                return redirect()->route('operacional.lista')->with('success', "Atualização realizada com sucesso!");
            }

        }else{

            if($result = (new Operacional())->insert($data)){

                $log = [
                    'title' => 'CADASTRO OPERACIONAL',
                    'description' => "$user->name realizou um novo cadastro operacional. " . $data['nome'] . " em " . date('Y-m-d H:i:s'),
                ];

                (new Logs())->insert($log);
                $this->notifyTelegram($user->name . " novo cadastro operacional " . $data['nome']);
                if($post['flag'] == 0){
                    return redirect()->route('planejamento.lista')->with('success', "Cadastro realizado com sucesso!");
                }

                return redirect()->route('operacional.lista')->with('success', "Cadastro realizado com sucesso!");
            }
        }

        return redirect()->route('operacional.lista')->with('error', "Erro ao fazer cadastro!");
    }

    public function registrarManutencaoGerador(): ResponseInterface
    {
        $post = $this->request->getPost();
        $gerador = strtoupper(trim((string)($post['gerador'] ?? '')));
        $dataManutencao = trim((string)($post['data_manutencao'] ?? ''));
        $observacao = trim((string)($post['observacao'] ?? ''));

        if (!in_array($gerador, ['G1', 'G2'], true)) {
            return redirect()->back()->with('error', 'Gerador inválido para manutenção.');
        }

        if ($dataManutencao === '' || preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataManutencao) !== 1) {
            return redirect()->back()->with('error', 'Data de manutenção inválida.');
        }

        $user = session()->get('user');
        $saved = (new GeradorManutencao())->insert([
            'gerador' => $gerador,
            'data_manutencao' => $dataManutencao,
            'observacao' => $observacao !== '' ? $observacao : null,
            'created_by' => isset($user->id) ? (int)$user->id : null,
        ]);

        if (!$saved) {
            return redirect()->back()->with('error', 'Não foi possível registrar a manutenção do gerador.');
        }

        (new Logs())->insert([
            'title' => 'MANUTENÇÃO GERADOR',
            'description' => ($user->name ?? 'Usuário') . " registrou manutenção do {$gerador} em {$dataManutencao}.",
        ]);

        return redirect()->back()->with('success', "Manutenção do {$gerador} registrada. Contagem reiniciada.");
    }

    public function geradoresManutencao(): string
    {
        $service = new OperacionalService();
        $usage = $service->getGeneratorUsageStatus();
        $db = db_connect();
        $tableReady = $db->tableExists('gerador_manutencoes');
        $hasUsoMinG1 = $db->fieldExists('uso_min_g1', 'operacional');
        $hasUsoMinG2 = $db->fieldExists('uso_min_g2', 'operacional');
        $hasLeituraG1 = $db->fieldExists('leitura_g1', 'operacional');
        $hasLeituraSaidaG1 = $db->fieldExists('leitura_saida_g1', 'operacional');
        $hasLeituraG2 = $db->fieldExists('leitura_g2', 'operacional');
        $hasLeituraSaidaG2 = $db->fieldExists('leitura_saida_g2', 'operacional');
        $hasTempoG1 = $db->fieldExists('tempo_g1', 'operacional');
        $hasTempoG2 = $db->fieldExists('tempo_g2', 'operacional');
        $manutencoes = [];
        $historicoUso = [];
        $usageLimit = 100;
        $usageTotalRows = 0;

        if ($tableReady) {
            $manutencoes = (new GeradorManutencao())
                ->orderBy('data_manutencao', 'desc')
                ->orderBy('id', 'desc')
                ->findAll();
        }

        $canReadG1FromLeitura = $hasLeituraG1 && $hasLeituraSaidaG1;
        $canReadG2FromLeitura = $hasLeituraG2 && $hasLeituraSaidaG2;

        if ($hasUsoMinG1 || $hasUsoMinG2 || $canReadG1FromLeitura || $canReadG2FromLeitura) {
            $select = ['id', 'data', 'nome'];
            if ($hasUsoMinG1) {
                $select[] = 'uso_min_g1';
            }
            if ($hasUsoMinG2) {
                $select[] = 'uso_min_g2';
            }
            if ($canReadG1FromLeitura) {
                $select[] = 'leitura_g1';
                $select[] = 'leitura_saida_g1';
            }
            if ($canReadG2FromLeitura) {
                $select[] = 'leitura_g2';
                $select[] = 'leitura_saida_g2';
            }
            if ($hasTempoG1) {
                $select[] = 'tempo_g1';
            }
            if ($hasTempoG2) {
                $select[] = 'tempo_g2';
            }

            $usageTotalRows = (new Operacional())
                ->where('flag', 1)
                ->countAllResults();

            // O histórico é propositalmente limitado aos 100 eventos mais recentes para manter a página responsiva.
            $historicoUso = (new Operacional())
                ->select(implode(', ', $select))
                ->where('flag', 1)
                ->orderBy('data', 'desc')
                ->orderBy('id', 'desc')
                ->limit($usageLimit)
                ->findAll();

            foreach ($historicoUso as $row) {
                $usoG1 = $hasUsoMinG1 ? (int)($row->uso_min_g1 ?? 0) : 0;
                $usoG2 = $hasUsoMinG2 ? (int)($row->uso_min_g2 ?? 0) : 0;

                if ($usoG1 <= 0 && $canReadG1FromLeitura) {
                    $usoG1 = $this->calculateGeneratorUsageMinutesFromHourValues(
                        (string)($row->leitura_g1 ?? ''),
                        (string)($row->leitura_saida_g1 ?? '')
                    );
                }
                if ($usoG1 <= 0 && $hasTempoG1) {
                    $usoG1 = $this->parseHourMeterToMinutes((string)($row->tempo_g1 ?? '')) ?? 0;
                }
                if ($usoG2 <= 0 && $canReadG2FromLeitura) {
                    $usoG2 = $this->calculateGeneratorUsageMinutesFromHourValues(
                        (string)($row->leitura_g2 ?? ''),
                        (string)($row->leitura_saida_g2 ?? '')
                    );
                }
                if ($usoG2 <= 0 && $hasTempoG2) {
                    $usoG2 = $this->parseHourMeterToMinutes((string)($row->tempo_g2 ?? '')) ?? 0;
                }

                $row->uso_min_g1_calc = $usoG1;
                $row->uso_min_g2_calc = $usoG2;
            }
        }

        return view('geradoresManutencao', [
            'geradoresPage' => true,
            'generator_usage' => $usage,
            'manutencoes' => $manutencoes,
            'historico_uso' => $historicoUso,
            'usage_limit' => $usageLimit,
            'usage_total_rows' => $usageTotalRows,
            'usage_columns_ready' => ($hasUsoMinG1 || $hasUsoMinG2 || $canReadG1FromLeitura || $canReadG2FromLeitura),
            'maintenance_table_ready' => $tableReady,
        ]);
    }

    public function geradoresManutencaoStore(): ResponseInterface
    {
        $post = $this->request->getPost();
        $gerador = strtoupper(trim((string)($post['gerador'] ?? '')));
        $dataManutencao = trim((string)($post['data_manutencao'] ?? ''));
        $observacao = trim((string)($post['observacao'] ?? ''));

        $payloadInput = [
            'gerador' => $gerador,
            'data_manutencao' => $dataManutencao,
            'observacao' => $observacao,
        ];
        if (!$this->validateData($payloadInput, GeradorManutencaoFormRequest::storeRules(), GeradorManutencaoFormRequest::messages())) {
            return redirect()->route('geradores.index')->with('error', GeradorManutencaoFormRequest::firstError($this->validator));
        }

        $user = session()->get('user');
        $saved = (new GeradorManutencao())->insert([
            'gerador' => $gerador,
            'data_manutencao' => $dataManutencao,
            'observacao' => $observacao !== '' ? $observacao : null,
            'created_by' => isset($user->id) ? (int)$user->id : null,
        ]);

        if (!$saved) {
            return redirect()->route('geradores.index')->with('error', 'Não foi possível registrar a manutenção.');
        }

        (new Logs())->insert([
            'title' => 'MANUTENÇÃO GERADOR',
            'description' => ($user->name ?? 'Usuário') . " registrou manutenção do {$gerador} em {$dataManutencao}.",
        ]);

        return redirect()->route('geradores.index')->with('success', "Manutenção do {$gerador} registrada com sucesso.");
    }

    public function geradoresManutencaoUpdate(): ResponseInterface
    {
        $post = $this->request->getPost();
        $id = (int)($post['id'] ?? 0);
        $gerador = strtoupper(trim((string)($post['gerador'] ?? '')));
        $dataManutencao = trim((string)($post['data_manutencao'] ?? ''));
        $observacao = trim((string)($post['observacao'] ?? ''));

        $payloadInput = [
            'id' => $id,
            'gerador' => $gerador,
            'data_manutencao' => $dataManutencao,
            'observacao' => $observacao,
        ];
        if (!$this->validateData($payloadInput, GeradorManutencaoFormRequest::updateRules(), GeradorManutencaoFormRequest::messages())) {
            return redirect()->route('geradores.index')->with('error', GeradorManutencaoFormRequest::firstError($this->validator));
        }

        $model = new GeradorManutencao();
        $item = $model->find($id);
        if (!$item) {
            return redirect()->route('geradores.index')->with('error', 'Registro de manutenção não encontrado.');
        }

        $updated = $model->update($id, [
            'gerador' => $gerador,
            'data_manutencao' => $dataManutencao,
            'observacao' => $observacao !== '' ? $observacao : null,
        ]);

        if (!$updated) {
            return redirect()->route('geradores.index')->with('error', 'Não foi possível atualizar a manutenção.');
        }

        $user = session()->get('user');
        (new Logs())->insert([
            'title' => 'ATUALIZAÇÃO MANUTENÇÃO GERADOR',
            'description' => ($user->name ?? 'Usuário') . " atualizou manutenção #{$id} ({$gerador}) em {$dataManutencao}.",
        ]);

        return redirect()->route('geradores.index')->with('success', 'Manutenção atualizada com sucesso.');
    }

    public function geradoresManutencaoDelete(): ResponseInterface
    {
        $id = (int)($this->request->getPost('id') ?? 0);
        if ($id <= 0) {
            return redirect()->route('geradores.index')->with('error', 'Registro inválido para exclusão.');
        }

        $model = new GeradorManutencao();
        $item = $model->find($id);
        if (!$item) {
            return redirect()->route('geradores.index')->with('error', 'Registro de manutenção não encontrado.');
        }

        if (!$model->delete($id)) {
            return redirect()->route('geradores.index')->with('error', 'Não foi possível excluir a manutenção.');
        }

        $user = session()->get('user');
        (new Logs())->insert([
            'title' => 'EXCLUSÃO MANUTENÇÃO GERADOR',
            'description' => ($user->name ?? 'Usuário') . " excluiu manutenção #{$id} do {$item->gerador}.",
        ]);

        return redirect()->route('geradores.index')->with('success', 'Manutenção excluída com sucesso.');
    }

    public function updateOperacional($id): string|ResponseInterface
    {
        if (!$this->isAdminUser()) {
            return redirect()->route('operacional.lista')->with('error', 'Apenas administradores podem editar cadastro operacional.');
        }

        if(is_int(intval($id)))
        {
            $temp = new Operacional();
            $item = $temp->select()->where('id', $id)->first();

            if($item->flag == 0){
                return view('cadastroPlanejamento', ['item' => $item]);
            }

            return view('cadastroOperacional', ['item' => $item]);
        }

        return redirect()->route('operacional.lista')->with('error', 'Parâmetro incorreto.');

    }

    public function deleteOperacional(): ResponseInterface
    {
        if (!$this->isAdminUser()) {
            return redirect()->route('operacional.lista')->with('error', 'Apenas administradores podem excluir cadastro operacional.');
        }

        $data = $this->request->getPost();

        $user = session()->get('user');

        $log=[
            'title' => "INFORMAÇÂO DELETADA",
            'description' => "$user->name apagou um cadastro operacional. " . $data['nome'] . " em " . date('Y-m-d H:i:s'),
        ];

        if($temp = (new Operacional())->delete($data['id'])){

            $info = new Logs();
            $info->insert($log);
            $this->notifyTelegram($user->name . " apagou " . $data['nome']);


            return redirect()->route('operacional.lista')->with('success', "Informação deletada com sucesso.");
        }

        return redirect()->route('operacional.lista')->with('error', "Informação não pode ser removida.");

    }

    public function cadastroPlanejamento(): string
    {
        return view('cadastroPlanejamento', ['planejado' => true]);
    }

    public function cadastroPlanejamentoStore(): ResponseInterface
    {
        return $this->cadastroOperacionalStore();
    }

    public function planejamentoLista(): string
    {
        $data = (new Operacional())->where('YEAR(data)', date('Y'))->where('flag', 0)->findAll();
        return view('operacionalLista', ['data' => $data, 'flag' => 0, 'planejado' => true]);
    }

    public function calendarioOperacional(): string
    {

        $model = new Operacional(); // ajuste o nome do Model correto

        $ano = $this->request->getGet('ano') ?? date('Y');

        // busca pelo ano na coluna 'data'
        $eventos = $model->where('YEAR(data)', $ano)->findAll();

        $data = array_map(function ($e) {
            // cor pode variar pelo tipo ou flag, ajuste como preferir
            switch ($e->tipo) {
                case 'show':      $cor = '#28a745'; break;
                case 'evento':    $cor = '#e0a800'; break;
                case 'serviço':   $cor = '#dc3545'; break;
                default:          $cor = '#3788d8';
            }

            // monta start/end combinando data + hora
            $start = $e->data . ' ' . ($e->hora_inicio ?? '00:00:00');
            $end   = $e->data . ' ' . ($e->hora_fim ?? '23:59:59');

            return [
                'id'    => $e->id,
                'title' => $e->nome,
                'start' => $start,
                'end'   => $end,
                'allDay' => true,
                'color' => $cor,
                // extendedProps
                'tipo'          => $e->tipo,
                'descricao'     => $e->descricao ?? '',
                'hora_extra'    => $e->hora_extra,
                'qtd_fornecedor'=> $e->qtd_fornecedor,
                'flag'          => $e->flag,
                'consumos'      => [
                    'diesel' => [
                        'inicio'   => $e->diesel_inicio,
                        'fim'      => $e->diesel_fim,
                        'litros'   => $e->diesel_litros,
                        'total'    => $e->diesel_total,
                        'valor'    => $e->valor_diesel,
                    ],
                    'gerador_kwh' => [
                        'entrada_g1' => $e->kwh_entrada_g1,
                        'saida_g1'   => $e->kwh_saida_g1,
                        'entrada_g2' => $e->kwh_entrada_g2,
                        'saida_g2'   => $e->kwh_saida_g2,
                    ],
                    'cedae' => [
                        'inicio'   => $e->cedae_inicio,
                        'fim'      => $e->cedae_fim,
                        'consumo'  => $e->cedae_consumo,
                        'total'    => $e->cedae_total,
                        'valor'    => $e->cedae_valor,
                    ],
                    'light' => [
                        'inicio'   => $e->light_inicio,
                        'fim'      => $e->light_fim,
                        'consumo'  => $e->light_consumo,
                        'total'    => $e->light_total,
                        'valor'    => $e->light_valor,
                    ],
                    'poco' => [
                        'inicio'   => $e->poco_inicio,
                        'fim'      => $e->poco_fim,
                        'consumo'  => $e->poco_consumo,
                    ],
                ],
                'custos' => [
                    'premontagem'      => $e->valor_premontagem,
                    'operacional'      => $e->valor_operacional,
                    'total_operacional'=> $e->total_operacional,
                    'seguranca'        => $e->seguranca,
                    'bombeiro'         => $e->bombeiro,
                    'limpeza'          => $e->limpeza,
                ],
                'ruido' => [
                    'tribuna_c' => $e->ruido_tribuna_c ?? null,
                    'pista_cavalo' => $e->ruido_pista_cavalo ?? null,
                    'corredor_jb' => $e->ruido_corredor_jb ?? null,
                    'media' => $e->ruido_media ?? ($e->monitor_sonoro ?? null),
                ],
                'publico' => [
                    'final'   => $e->publico_final,
                    'tribuna' => $e->publico_tribuna,
                    'saida'   => $e->publico_saida,
                ],
            ];
        }, $eventos);

        return view('calendarioOperacional', [
            'ano'        => $ano,
            'fcEvents'   => json_encode($data),
            'calendarioOp' => true,
        ]);

    }

}
