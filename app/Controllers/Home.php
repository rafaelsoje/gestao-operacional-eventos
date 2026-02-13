<?php

namespace App\Controllers;

use App\Models\Logs;
use App\Models\Operacional;
use App\Models\GeradorManutencao;
use App\Models\Propostas;
use App\Models\Users;
use App\Services\DashboardService;
use App\Services\OperacionalService;
use App\Services\TelegramService;
use App\Services\UploadService;
use App\Services\AvatarService;
use CodeIgniter\Database\Query;
use CodeIgniter\HTTP\ResponseInterface;
use PhpParser\Node\Scalar\Float_;
use Throwable;
use stdClass;

class Home extends BaseController
{
    public function index()
    {
        $service = new DashboardService();
        $payload = $service->getDashboardData();

        return view('dashboard', $payload + ['botanic' => true]);

    }

    public function proposta(): string
    {
        $anoPassado = date('Y')-1;
        $propostas = (new Propostas())->select()->where('YEAR(start_date) >=', date('Y'))->findAll() ;

        return view('proposta', ['propostas' => $propostas, 'botanicProp' => true]);
    }

    public function cadastroProposta(): string
    {
        return view('cadastroProposta',['botanicCad' => true]);
    }

    public function cadastroPropostaStore()
    {
        helper('money');

        $validate = $this->validate([
            'excolab_id' => 'required',
            'type' => 'required',
            'start_date' => 'required',
            'event_name' => 'required',
            'value' => 'required',
            'status' => 'required',
        ]);

        if (!$validate) {
            return redirect()->route('cadastro.proposta')->with('error', 'Preencha o formulário corretamente!');
        }

        $data = $this->request->getPost();

        $uploadService = new UploadService();
        $path = WRITEPATH . 'uploads/';

        $directoryName = $uploadService->sanitizeDir($data['start_date']); // ex: "2024-11-06"
        $fullPath = $path . $directoryName;

        $file = $this->request->getFile('file');
        $fileResult = $uploadService->handle($file, $fullPath);
        if ($fileResult['error']) {
            return redirect()->back()->with('error', $fileResult['error']);
        }
        $newName = $fileResult['name'];

        // $temp = explode('.', $data['value']);
        // $value = implode('',$temp);

        // $temp = explode(',', $value);
        // $value = implode('.',$temp);

        $value = money_to_decimal($data['value']);
        $charge = money_to_decimal($data['charge'] ?? null);
        $rent = money_to_decimal($data['rent'] ?? null);
        $third = money_to_decimal($data['third'] ?? null);
        $saldo = money_to_decimal($data['saldo'] ?? null);


        $items = [
            'excolab_id' => $data['excolab_id'],
            'type' => $data['type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'event_name' => $data['event_name'],
            'client_name' => $data['client_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'value' => $value,
            'charge' => $charge,
            'rent' => $rent,
            'third'=> $third,
            'saldo'=> $saldo,
            'status' => $data['status'],
            'description' => $data['description'],
            'file' => $newName
        ];

        $prop = new Propostas();
        $result = $prop->insert($items);

        $rider = $this->request->getFile('rider');
        $riderResult = $uploadService->handle($rider, $fullPath);
        if ($riderResult['error']) {
            return redirect()->back()->with('error', $riderResult['error']);
        }
        if ($riderResult['name']) {
            $item = [
                'rider' => $riderResult['name']
            ];
            $prop->update($result, $item);
        }


        if($result){

            $user = session()->get('user');
            $data = [
                'title' => 'CADASTROU PROPOSTA',
                'description' => "O usuário " . $user->name  . " cadastrou " . $data['event_name'] . " " .date('Y-m-d H:i:s')
            ];

            $log = (new Logs())->insert($data);
            $this->notifyTelegram($user->name . " cadastrou " . $items['event_name']);
            return redirect()->route('proposta')->with('success', "Proposta cadastrada com sucesso!");
        }

        return redirect()->route('proposta')->with('error', 'Não foi possível gravar os dados.');
    }

    public function UpdateProposta($id){

        if(is_int(intval($id))){

            if($proposta = (new Propostas())->select()->where('id', $id)->first()){

                return view('updateProposta',['proposta' => $proposta]);
            }

            return redirect('proposta')->with('error', 'Proposta não encontrada.');
        }

        return redirect('proposta')->with('error', 'ID da proposta inválido.');

    }

    public function UpdatePropostaStore()
    {
        helper('money');

        $validate = $this->validate([
            'id' => 'required',
            'excolab_id' => 'required',
            'type' => 'required',
            'start_date' => 'required',
            'event_name' => 'required',
            'value' => 'required',
            'status' => 'required',
        ]);

        if (!$validate) {
            return redirect()->route('proposta')->with('error', 'Preencha o formulário corretamente!');
        }

        $data = $this->request->getPost();

        $uploadService = new UploadService();
        $path = WRITEPATH . 'uploads/';

        $directoryName = $uploadService->sanitizeDir($data['start_date']); // ex: "2024-11-06"
        $fullPath = $path . $directoryName;

        $file = $this->request->getFile('file');
        $rider = $this->request->getFile('rider');


        $safeOldDate = $uploadService->sanitizeDir($data['start_date_old']);
        $safeOldFile = basename($data['file_old']);
        $fileOld = $path . $safeOldDate . "/" . $safeOldFile;
        $fileNew = $fullPath . "/" . $safeOldFile;

        if($file){
            $fileResult = $uploadService->handle($file, $fullPath);
            if ($fileResult['error']) {
                return redirect()->back()->with('error', $fileResult['error']);
            }

            if ($fileResult['name']) {
                $item = [
                    'file' => $fileResult['name']
                ];

                $prop = new Propostas();
                $prop->update($data['id'], $item);

                if (is_file($fileOld)) {
                    unlink($fileOld);
                }
            } else {
                if($data['start_date'] != $data['start_date_old']){
                    if (!is_dir($fullPath)) {
                        mkdir($fullPath, 0755, true);
                    }
                    if (is_file($fileOld)) {
                        rename($fileOld, $fileNew);
                    }
                }
            }
        }


        if($rider){
            $riderResult = $uploadService->handle($rider, $fullPath);
            if ($riderResult['error']) {
                return redirect()->back()->with('error', $riderResult['error']);
            }

            if ($riderResult['name']) {
                $item = [
                    'rider' => $riderResult['name']
                ];

                $prop = new Propostas();
                $prop->update($data['id'], $item);
            }
        }

        if(isset($data['removeRider']) && $data['removeRider'] == "on"){
            $item = [
                'rider' => ''
            ];

            $prop = new Propostas();
            $temp = $prop->update($data['id'], $item);
        }



        // $temp = explode('.', $data['value']);
        // $value = implode('',$temp);

        // $temp = explode(',', $value);
        // $value = implode('.',$temp);

        $value = money_to_decimal($data['value']);
        $charge = money_to_decimal($data['charge'] ?? null);
        $rent = money_to_decimal($data['rent'] ?? null);
        $third = money_to_decimal($data['third'] ?? null);
        $saldo = money_to_decimal($data['saldo'] ?? null);

        $items = [
            'excolab_id' => $data['excolab_id'],
            'type' => $data['type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'event_name' => $data['event_name'],
            'client_name' => $data['client_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'value' => $value,
            'charge' => $charge,
            'rent' => $rent,
            'third'=> $third,
            'saldo'=> $saldo,
            'status' => $data['status'],
            'description' => $data['description'],
        ];




        $prop = new Propostas();
        $result = $prop->update($data['id'], $items);

        if($result){

            $user = session()->get('user');
            $data = [
                'title' => 'ATUALIZOU PROPOSTA',
                'description' => "O usuário " . $user->name  . " atualizou proposta " . $data['event_name'] . " ". date('Y-m-d H:i:s')
            ];

            $log = (new Logs())->insert($data);
            $this->notifyTelegram($user->name . " atualizou a proposta " . $items["event_name"]);
            return redirect()->route('proposta')->with('success', "Proposta atualizada com sucesso!");
        }

        return redirect()->route('proposta')->with('error', 'Não foi possível gravar os dados.');

    }

    public function removeProposta()
    {
        $id = $this->request->getPost('id');
        $file = $this->request->getPost('file');
        $date = $this->request->getPost('date');

        $safeDate = preg_replace('/[^0-9\-]/', '', $date);
        $safeFile = basename($file);
        $path = WRITEPATH . 'uploads/' . $safeDate . "/" . $safeFile;

        if(is_int(intval($id))){

            if($prop = (new Propostas())->where('id',$id)->first()){
                $user = session()->get('user');
                $data = [
                    'title' => 'REMOVEU PROPOSTA',
                    'description' => "O usuário " . $user->name  . " removeu " . $prop->event_name . " " . date('Y-m-d H:i:s')
                ];

                $log = (new Logs())->insert($data);
                $this->notifyTelegram($user->name . " removeu " . $prop->event_name);
            }

            if($user = (new Propostas())->delete($id)){
                if (file_exists($path) && $file != "") {
                    unlink($path);
                }

                return redirect('proposta')->with('success', 'Proposta removida com sucesso!');
            }

            return redirect('proposta')->with('error', 'Não foi possível remover proposta');
        }

        return redirect()->route('proposta')->with('error', 'Parametro incorreto.');
    }


    ##############################################
    #               Operacional                  #
    ###############################################

    public function operacional(){
        $service = new OperacionalService();
        $year = (int)date('Y');

        $get = '';

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

        if($this->request->getPost()){

           # dd('post');

            $post = $this->request->getPost();
            $filters = [];

            if($post['data_inicial'] && $post['data_final'])
            {

                if(isset($post['social']) || isset($post['corporativo']) || isset($post['entretenimento']))
                {

                    if(isset($post['social'])){
                        $array['social'] = $post['social'];
                    }
                    if(isset($post['corporativo'])){
                        $array['corporativo'] = $post['corporativo'];
                    }
                    if(isset($post['entretenimento'])){
                        $array['entretenimento'] = $post['entretenimento'];
                    }

                    $filters = [
                        'date_from' => $post['data_inicial'],
                        'date_to' => $post['data_final'],
                        'flag' => 1,
                        'tipos' => $array,
                    ];

                    $result = $service->buildAggregates($filters);
                    $data = $result['data'];
                    $sum = $result['sum'];
                    $chart = $result['chart'];
                    $barSocial = $result['bars']['social'];
                    $barCorporativo = $result['bars']['corporativo'];
                    $barEntretenimento = $result['bars']['entretenimento'];
                }else{
                    return redirect()->back()->with('error', 'Nenhum tipo de evento selecionado!');
                }
            }else{
                return redirect()->back()->with('error', 'Data da pesquisa incorreta');
            }
        }else if($get == 0){

           

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
        
        $donutChart = [
            ['Categorria', 'valor'],
            ['Equipe Operacional', (float)$chart->total_operacional],
            ['Diesel', (float)$chart->diesel_total],
            ['Cedae', (float)$chart->cedae_total],
            ['light', (float)$chart->light_total]
        ];

        $bar = [
            ['Custo', 'Equipe Operacional','Gerador', 'Cedae', 'Light'],
            ['Entretenimento',
                (float)$barEntretenimento->total_operacional,
                (float)$barEntretenimento->diesel_total,
                (float)$barEntretenimento->cedae_total,
                (float)$barEntretenimento->light_total
            ],
            ['Social',
                (float)$barSocial->total_operacional,
                (float)$barSocial->diesel_total,
                (float)$barSocial->cedae_total,
                (float)$barSocial->light_total
            ],
            ['Corporativo',
                (float)$barCorporativo->total_operacional,
                (float)$barCorporativo->diesel_total,
                (float)$barCorporativo->cedae_total,
                (float)$barCorporativo->light_total
            ],
        ];

        $barchart = [
            ['tipo', 'valor Total'],
            ['Entretenimento', (float)$barEntretenimento->total_consumo],
            ['Social', (float)$barSocial->total_consumo],
            ['Corporativo', (float)$barCorporativo->total_consumo],
        ];


        $line = $service->monthlyComparison($year);
        $generatorAccumLine = $this->buildGeneratorAccumulatedLine($data ?? []);

        

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

        $payload = [
            'sum' => $sum,
            'avg_event_cost' => $avgEventCost,
            'trend_cards' => $trendCards,
            'data' => $data,
            'generator_usage' => $generatorUsage,
            'donut' => json_encode($donutChart),
            'bar' => json_encode($bar),
            'barChart' => json_encode($barchart),
            'line' => json_encode($line),
            'lineGenerator' => json_encode($generatorAccumLine),
        ];

        if(isset($post)){
            $topEvents = $service->topEventsByCost($filters ?? []);
            return view('operacional', $payload + [
                'operacional' => true,
                'topEvents' => json_encode($topEvents),
                'search' => $post,
                'tipo' => $array ?? [],
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

    public function downloadUpload(string $date, string $file)
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

    public function cadastroOperacional(){
        return view('cadastroOperacional',['cadastroOp' => true]);
    }

    public function operacionalLista(){
        $data = (new Operacional())->where('YEAR(data)', date('Y'))->where('flag', 1)->findAll();

        return view('operacionalLista', ['data'=> $data, 'listaOp' => true]);
    }

    public function cadastroOperacionalStore(){

        $post = $this->request->getPost();

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
            'monitor_sonoro' => isset($post['monitor_sonoro']) ? $post['monitor_sonoro'] : null,
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

    public function registrarManutencaoGerador()
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

    public function geradoresManutencao()
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

            $historicoUso = (new Operacional())
                ->select(implode(', ', $select))
                ->where('flag', 1)
                ->orderBy('data', 'desc')
                ->orderBy('id', 'desc')
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
            'usage_columns_ready' => ($hasUsoMinG1 || $hasUsoMinG2 || $canReadG1FromLeitura || $canReadG2FromLeitura),
            'maintenance_table_ready' => $tableReady,
        ]);
    }

    public function geradoresManutencaoStore()
    {
        $post = $this->request->getPost();
        $gerador = strtoupper(trim((string)($post['gerador'] ?? '')));
        $dataManutencao = trim((string)($post['data_manutencao'] ?? ''));
        $observacao = trim((string)($post['observacao'] ?? ''));

        if (!$this->isValidGerador($gerador) || !$this->isValidDateYmd($dataManutencao)) {
            return redirect()->route('geradores.index')->with('error', 'Dados inválidos para registrar manutenção.');
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

    public function geradoresManutencaoUpdate()
    {
        $post = $this->request->getPost();
        $id = (int)($post['id'] ?? 0);
        $gerador = strtoupper(trim((string)($post['gerador'] ?? '')));
        $dataManutencao = trim((string)($post['data_manutencao'] ?? ''));
        $observacao = trim((string)($post['observacao'] ?? ''));

        if ($id <= 0 || !$this->isValidGerador($gerador) || !$this->isValidDateYmd($dataManutencao)) {
            return redirect()->route('geradores.index')->with('error', 'Dados inválidos para atualizar manutenção.');
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

    public function geradoresManutencaoDelete()
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

    public function updateOperacional($id)
    {
        if(is_int(intval($id)))
        {
            $temp = new Operacional();
            $item = $temp->select()->where('id', $id)->first();

            if($item->flag == 0){
                return view('cadastroPlanejamento', ['item' => $item]);
            }

            return view('cadastroOperacional', ['item' => $item]);
        }

        return redirect()->route('operacional')->with('error', "Parametro incorreto");

    }

    public function deleteOperacional()
    {
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

    public function cadastroPlanejamento()
    {
        return view('cadastroPlanejamento', ['planejado' => true]);
    }

    public function cadastroPlanejamentoStore()
    {
        return $this->cadastroOperacionalStore();
    }

    public function planejamentoLista()
    {
        $data = (new Operacional())->where('YEAR(data)', date('Y'))->where('flag', 0)->findAll();
        return view('operacionalLista', ['data' => $data, 'flag' => 0, 'planejado' => true]);
    }

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

    public function cadastroUsuario(): string
    {
        return view('cadastroUsuario');
    }

    public function usuarioStore()
    {
        $validate = $this->validate([
            'name' => 'required|min_length[3]|max_length[120]',
            'email' => 'required|valid_email|max_length[190]',
            'role' => 'required|in_list[user,admin]',
        ]);

        if (!$validate) {
            return redirect()->route('usuario')->with('error', 'Preencha o formulário corretamente.');
        }

        $data = $this->request->getPost();
        $data['email'] = mb_strtolower(trim((string)($data['email'] ?? '')));
        $data['name'] = trim((string)($data['name'] ?? ''));

        if ((new Users())->where('email', $data['email'])->first()) {
            return redirect()->route('usuario')->with('error', 'Este e-mail já está cadastrado.');
        }

        $defaultPassword = env('DEFAULT_USER_PASSWORD', 'Bemvindo@2026');
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
            $this->notifyTelegram($user->name . " cadastrou um usuário " . $data['name']);
            return redirect()->route('usuarios')->with('success', 'Usuário cadastrado. Senha padrão: ' . $defaultPassword);
        }

        return redirect()->route('usuario')->with('error', 'Não foi possível cadastrar usuário.');

    }

    public function updateUsuario($id)
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

    public function updateUsuarioStore()
    {
        // if (!session()->has('auth')) {
        //     return redirect()->route('login')->with('error', 'Sessão expirada!!!');
        // }

        $validate = $this->validate([
            'id' => 'required|is_natural_no_zero',
            'name' => 'required|min_length[3]|max_length[120]',
            'role' => 'required|in_list[user,admin]',
        ]);
        if (!$validate) {
            return redirect()->route('usuarios')->with('error', 'Preencha o formulário corretamente!');
        }

        $data = $this->request->getPost();
        $data['name'] = trim((string)($data['name'] ?? ''));
        $superEmail = env('SUPERUSER_EMAIL', env('TELEGRAM_ADMIN_EMAIL', 'rafaelsoje@gmail.com'));
        $target = (new Users())->select('id,email,admin,role')->where('id', $data['id'])->first();
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

                (new Users())->update($data['id'], ['avatar' => $name]);
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
            $userInfo->role = $user->role ?? (($user->admin == '1') ? 'admin' : 'user');
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

    public function perfil()
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

        $role = $dbUser->role ?? (($dbUser->admin == '1') ? 'admin' : 'user');

        return view('perfil', [
            'user' => $dbUser,
            'role' => $role,
        ]);
    }

    public function perfilUpdate()
    {
        if (!session()->has('auth')) {
            return redirect()->route('login')->with('error', 'Sessão expirada ou não autenticada.');
        }

        $user = session()->get('user');
        if (!$user) {
            return redirect()->route('login')->with('error', 'Sessão expirada ou não autenticada.');
        }

        $data = $this->request->getPost();
        $items = [];

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
            $dbUser = (new Users())->find($user->id);
            if (!$dbUser || !password_verify($currentPassword, $dbUser->password)) {
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
        }

        return redirect()->route('perfil')->with('success', 'Perfil atualizado com sucesso!');
    }

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

    public function removeUsuario($id = null)
    {
        $id = $id ?? $this->request->getPost('id');

        if(is_int(intval($id))){
            $superEmail = env('SUPERUSER_EMAIL', env('TELEGRAM_ADMIN_EMAIL', 'rafaelsoje@gmail.com'));
            $target = (new Users())->select('id,email')->where('id', $id)->first();
            if ($target && $target->email === $superEmail) {
                return redirect('usuarios')->with('error', 'Superusuário não pode ser removido.');
            }
            if((new Users())->delete($id)){
                $user = session()->get('user');
                #$this->telegran($user->name . " removeu usuario");
                return redirect('usuarios')->with('success', 'Usuário removido com sucesso!');
            }

            return redirect('usuarios')->with('error', 'Não foi possível remover usuário');
        }

        return redirect()->route('usuarios')->with('error', 'Parametro incorreto.');
    }

    public function notifications()
    {
        if (!$this->canManageNotifications()) {
            return redirect()->route('home')->with('error', 'Acesso negado.');
        }

        $enabled = (new TelegramService())->isEnabled();
        $onlineUsers = $this->getOnlineUsersSnapshot();

        return view('notifications', [
            'enabled' => $enabled,
            'onlineUsers' => $onlineUsers,
            'onlineWindowSeconds' => $this->getOnlineWindowSeconds(),
        ]);
    }

    public function notificationsUpdate()
    {
        if (!$this->canManageNotifications()) {
            return redirect()->route('home')->with('error', 'Acesso negado.');
        }

        $enabled = $this->request->getPost('telegram_enabled') === '1' ? 'true' : 'false';
        (new \App\Models\Settings())->setValue('telegram_enabled', $enabled);

        return redirect()->route('admin.notifications')->with('success', 'Configuração atualizada.');
    }

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

    public function logsCleanupDelete()
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

    private function canManageNotifications(): bool
    {
        $user = session()->get('user');
        $superEmail = env('SUPERUSER_EMAIL', env('TELEGRAM_ADMIN_EMAIL', 'rafaelsoje@gmail.com'));
        return $user && $user->email === $superEmail;
    }

    private function getCleanupRange($rawValue): ?string
    {
        $value = trim((string) $rawValue);
        if ($value === 'all') {
            return 'all';
        }

        return in_array($value, ['30', '60', '90'], true) ? $value : null;
    }

    private function countLogsOlderThanDays(int $days): int
    {
        $limitDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        $countByCreatedAt = (int) (new Logs())
            ->where('created_at <', $limitDate)
            ->countAllResults();

        $legacyIds = $this->getLegacyLogIdsOlderThanDays($days);

        return $countByCreatedAt + count($legacyIds);
    }

    private function getLegacyLogIdsOlderThanDays(int $days): array
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

    private function countAllLogs(): int
    {
        return (int) (new Logs())->countAllResults();
    }

    private function extractTimestampFromLogDescription(string $description): ?int
    {
        if (preg_match('/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $description, $matches) !== 1) {
            return null;
        }

        $parsed = strtotime((string) ($matches[1] ?? ''));
        return $parsed !== false ? (int) $parsed : null;
    }

    private function notifyTelegram(string $message): void
    {
        (new TelegramService())->send($message);
    }

    public function calendario()
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


    public function mes($num)
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

    public function calendarioOperacional()
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
                ],
                'custos' => [
                    'premontagem'      => $e->valor_premontagem,
                    'operacional'      => $e->valor_operacional,
                    'total_operacional'=> $e->total_operacional,
                    'seguranca'        => $e->seguranca,
                    'bombeiro'         => $e->bombeiro,
                    'limpeza'          => $e->limpeza,
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

    public function logs()
    {
        $data = (new Logs())->select()->orderBy('id', 'desc')->limit(50)->findAll();
        foreach ($data as $log) {
            $description = (string) ($log->description ?? '');
            $log->user_name = $this->extractLogUserName($description);
            $log->clean_description = $this->stripUserFromDescription($description);
        }

        

        return view('logs', ['logs' => $data]);
    }

    private function extractLogUserName(string $description): string
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

    private function stripUserFromDescription(string $description): string
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

    public function telegran($texto)
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

    private function hasUsersMustChangePasswordColumn(): bool
    {
        static $hasColumn = null;
        if ($hasColumn !== null) {
            return $hasColumn;
        }

        $hasColumn = db_connect()->fieldExists('must_change_password', 'users');
        return $hasColumn;
    }

    private function isDuplicateEmailException(Throwable $e): bool
    {
        $message = mb_strtolower($e->getMessage());
        return str_contains($message, 'duplicate') && str_contains($message, 'email');
    }

    private function isValidGerador(string $gerador): bool
    {
        return in_array($gerador, ['G1', 'G2'], true);
    }

    private function isValidDateYmd(string $date): bool
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1;
    }

    private function calculateGeneratorUsageMinutesFromPost(array $post, string $entradaKey, string $saidaKey): int
    {
        return $this->calculateGeneratorUsageMinutesFromHourValues(
            (string)($post[$entradaKey] ?? ''),
            (string)($post[$saidaKey] ?? '')
        );
    }

    private function buildGeneratorAccumulatedLine(array $events): array
    {
        $line = [
            ['Data', 'G1 acumulado (h)', 'G2 acumulado (h)']
        ];

        if (empty($events)) {
            return $line;
        }

        usort($events, static function ($a, $b) {
            $da = (string)($a->data ?? '');
            $db = (string)($b->data ?? '');
            if ($da === $db) {
                return (int)($a->id ?? 0) <=> (int)($b->id ?? 0);
            }
            return $da <=> $db;
        });

        $accG1 = 0;
        $accG2 = 0;

        foreach ($events as $event) {
            $accG1 += $this->resolveEventGeneratorUsageMinutes($event, 'g1');
            $accG2 += $this->resolveEventGeneratorUsageMinutes($event, 'g2');

            $label = !empty($event->data)
                ? date('d/m', strtotime((string)$event->data))
                : ('#' . (int)($event->id ?? 0));

            $line[] = [
                $label,
                round($accG1 / 60, 2),
                round($accG2 / 60, 2),
            ];
        }

        return $line;
    }

    private function resolveEventGeneratorUsageMinutes(object $event, string $generator): int
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

    private function calculateGeneratorUsageMinutesFromHourValues(string $entradaValue, string $saidaValue): int
    {
        $entrada = $this->parseHourMeterToMinutes($entradaValue);
        $saida = $this->parseHourMeterToMinutes($saidaValue);

        if ($entrada === null || $saida === null || $saida < $entrada) {
            return 0;
        }

        return $saida - $entrada;
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

    private function normalizeEquipeNames($input): ?string
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

    private function buildPreviousPeriodFilters(array $filters): ?array
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

    private function buildTrendCards(object $sum, ?object $prevSum, float $avgEventCost, ?float $prevAvgEventCost): array
    {
        $cards = [
            'diesel_total' => $this->makeTrendValue((float)($sum->diesel_total ?? 0), (float)($prevSum->diesel_total ?? 0)),
            'kwh_total' => $this->makeTrendValue((float)($sum->kwh_total ?? 0), (float)($prevSum->kwh_total ?? 0)),
            'cedae_total' => $this->makeTrendValue((float)($sum->cedae_total ?? 0), (float)($prevSum->cedae_total ?? 0)),
            'light_total' => $this->makeTrendValue((float)($sum->light_total ?? 0), (float)($prevSum->light_total ?? 0)),
            'valor_premontagem' => $this->makeTrendValue((float)($sum->valor_premontagem ?? 0), (float)($prevSum->valor_premontagem ?? 0)),
            'super_op' => $this->makeTrendValue((float)($sum->super_op ?? 0), (float)($prevSum->super_op ?? 0)),
            'avg_event_cost' => $this->makeTrendValue($avgEventCost, (float)($prevAvgEventCost ?? 0)),
            'total_consumo' => $this->makeTrendValue((float)($sum->total_consumo ?? 0), (float)($prevSum->total_consumo ?? 0)),
        ];

        return $cards;
    }

    private function makeTrendValue(float $current, float $previous): array
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

}
